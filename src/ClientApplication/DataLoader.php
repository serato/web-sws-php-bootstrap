<?php

namespace Serato\SwsApp\ClientApplication;

use Exception;
use DateTime;
use Aws\Sdk as AwsSdk;
use Psr\Cache\CacheItemPoolInterface;
use Serato\SwsApp\ClientApplication\Exception\InvalidEnvironmentNameException;
use Serato\SwsApp\ClientApplication\Exception\InvalidFileContentsException;
use Serato\SwsApp\ClientApplication\Exception\MissingApplicationIdException;
use Serato\SwsApp\ClientApplication\Exception\MissingApplicationPassword;
use Serato\SwsApp\ClientApplication\Exception\MissingKmsKeyIdException;

/**
 * Client Application Data Loader
 *
 * Loads client application data from S3
 */

class DataLoader
{
    private const CACHE_EXPIRY_TIME = 3600; // seconds
    private const ENVIRONMENTS = ['dev', 'test', 'production'];
    private const S3_BUCKET_NAME = 'sws.clientapps';
    private const S3_BASE_PATH = 'v3';
    private const CLIENT_APPS_SECRET_PREFIX = 'sws-client-application';
    private const COMMON_APP_DATA_NAME = 'client-applications.json';

    /** @var string */
    private $env;

    /** @var AwsSdk */
    private $awsSdk;

    /** @var CacheItemPoolInterface */
    private $psrCache;

    /**
     * Constructs the object
     *
     * @param string                    $env            Application environment
     * @param AwsSdk                    $awsSdk         AWS SDK
     * @param CacheItemPoolInterface    $psrCache       PSR-6 cache item pool
     */
    public function __construct(
        string $env,
        AwsSdk $awsSdk,
        CacheItemPoolInterface $psrCache
    ) {
        if (!in_array($env, self::ENVIRONMENTS)) {
            throw new InvalidEnvironmentNameException(
                'Invalid environment name `' . $env . '`. Must be one of `' .
                implode('`, `', self::ENVIRONMENTS) . '`.'
            );
        }

        $this->env = $env;
        $this->awsSdk = $awsSdk;
        $this->psrCache = $psrCache;
    }

    /**
     * Returns an array of all valid client applications for an environment.
     *
     * @param string $env           Application environment. Defaults to value passed to constructor
     * @param boolean $useCache     Determines whether or not to look in the cache.
     * @return array
     */
    public function getApp(string $env = null, bool $useCache = true): array
    {
        if ($env === null) {
            $env = $this->env;
        }

        return $this->parseClientAppData(
            $this->loadFromCache(self::COMMON_APP_DATA_NAME, $useCache)
        );
    }

    /**
     * Load application data from cache if available.
     * If not, fetch from S3 and save to cache.
     *
     * @return array
     */
    private function loadFromCache(string $name, bool $useCache = true): array
    {
        $s3ObjectName = self::S3_BASE_PATH . '/' . $name;

        $cacheKey = str_replace(['\\', '/'], '_', __CLASS__ . '--' . self::S3_BUCKET_NAME . '--' . $s3ObjectName);

        // Read from cache, if specified
        if ($useCache) {
            $item = $this->psrCache->getItem($cacheKey);
            if ($item->isHit()) {
                return $item->get();
            }
        }

        // Fetch from S3
        $s3Data = $this->loadFromS3($s3ObjectName);

        // Write to cache regardless of `$useCache` setting
        $item = $this->psrCache->getItem($cacheKey);
        $expiryTime = new DateTime();
        $expiryTime->setTimestamp(time() + self::CACHE_EXPIRY_TIME);
        $item->set($s3Data);
        $item->expiresAt($expiryTime);
        $this->psrCache->save($item);

        return $s3Data;
    }

    /**
     * Load application data from S3
     *
     * @return array
     * @throws InvalidFileContentsException
     */
    private function loadFromS3(string $s3ObjectName): array
    {
        $result = $this->awsSdk->createS3(['version' => '2006-03-01'])->getObject(
            ['Bucket' => self::S3_BUCKET_NAME, 'Key' => $s3ObjectName]
        );
        $data = json_decode((string)$result['Body'], true);
        if ($data === null || !is_array($data)) {
            throw new InvalidFileContentsException(
                'Invalid file contents for S3 object `s3://' .
                self::S3_BUCKET_NAME . '/' . $s3ObjectName . '`. ' .
                'File does not contain valid JSON string.'
            );
        }
        return $data;
    }

    /**
     * Retrieve application secrets from secrets manager
     *
     * @return array
     * @throws InvalidFileContentsException
     */
    private function getSecret(string $appPath): array
    {
        $secretsManagerClient = $this->awsSdk->createSecretsManager(['version' => '2017-10-17']);

        if ($appPath !== null) {
            $appPath = ltrim($appPath, '/');
            if ($appPath === '') {
                $appPath = null;
            }
        }

        $result = $secretsManagerClient->getSecretValue([
            'SecretId' => $this->env . '/' . self::CLIENT_APPS_SECRET_PREFIX . '/' . ($appPath === null ? '' : $appPath)
        ]);

        if (isset($result['SecretString'])) {
            $secret = json_decode($result['SecretString'], true);
        }

        return $secret;
    }

    /**
     * Merges environment specific credentials with the provided client app
     * data.
     *
     * @param array $clientAppsData data from client-applications.json
     * @return array
     *
     * @throws MissingApplicationIdException
     * @throws MissingApplicationPassword
     * @throws MissingKmsKeyIdException
     */
    private function parseClientAppData(array $clientAppsData): array
    {
        $data = [];
        foreach ($clientAppsData as $appData) {
            $parsedData = [];
            $credentialsData = $this->getSecret($appData['path']);
            $appSecretName = $this->getSecretName($appData['path'], $this->env);
            // All apps MUST have `appId`, `appSecret` and `kmsKeyId` keys defined
            if (!isset($credentialsData['appId'])) {
                throw new MissingApplicationIdException(
                    'Invalid configuration for application `' . $appSecretName . '` in Secrets Manager `' .
                    $$appSecretName . '`. Missing required key `appId`.'
                );
            }
            if (!isset($credentialsData['appSecret'])) {
                throw new MissingApplicationPassword(
                    'Invalid configuration for application `' . $appSecretName . '` in credentials file `' .
                    $$appSecretName . '`. Missing required key `appSecret`.'
                );
            }
            if (!isset($credentialsData['kmsKeyId'])) {
                throw new MissingKmsKeyIdException(
                    'Invalid configuration for application `' . $appSecretName . '` in credentials file `' .
                    $$appSecretName . '`. Missing required key `kmsKeyId`.'
                );
            }

            // Add all data, excluding certain keys
            $parsedData = $appData;
            unset($parsedData['path'], $parsedData['basic_auth_scopes'], $parsedData['restricted_to']);
            $parsedData['id'] = $credentialsData['appId'];
            $parsedData['password_hash'] = password_hash($credentialsData['appSecret'], PASSWORD_DEFAULT);

            // Format scopes if present
            if (isset($appData['basic_auth_scopes'])) {
                $parsedData['scopes'] = $this->parseScopes($appData['basic_auth_scopes']);
            }

            if (isset($appData['jwt'])) {
                $parsedData['jwt'] =  $this->parseJwt($appData['jwt']);
            }
            $parsedData['jwt']['kms_key_id'] = $credentialsData['kmsKeyId'];

            // Format the optional `custom_template_path` item
            if (isset($appData['custom_template_path'])) {
                $parsedData['custom_template_path'] = $this->parseCustomTemplatePath($appData['custom_template_path']);
            }

            // Add optional `restricted_to` item
            if (isset($appData['restricted_to'])) {
                if (!isset($parsedData['jwt'])) {
                    $parsedData['jwt'] = [];
                }
                if (!isset($parsedData['jwt']['access'])) {
                    $parsedData['jwt']['access'] = [];
                }
                $parsedData['jwt']['access']['restricted_to'] = $appData['restricted_to'];
            }

            array_push($data, $parsedData);
        }

        return $data;
    }

    private function getSecretName(string $secretPath, string $env): string
    {
        return $env . '/' . self::CLIENT_APPS_SECRET_PREFIX . '/' . $secretPath;
    }

    /**
     * Return scopes to be in format
     * 'service' => ['scope']
     */
    private function parseScopes(array $scopes): array
    {
        $parsedScopes = [];
        foreach ($scopes as $scope) {
            $parsedScopes[$scope['service']] = $scope['scopes'];
        }
        return $parsedScopes;
    }

    /**
     * Return permissioned scopes to be in format
     * 'service' => [
     *     'scope' => ['group_membership']
     * ],
     */
    private function parsePermissionedScopes(array $permissionedScopes): array
    {
        $parsedPermissionedScopes = [];
        // $scopes = [ 'service' => 'webservice', 'scopes' => [ list of scopes ]]
        foreach ($permissionedScopes as $serviceScopes) {
            $userGroups = [];

            // Parse the inner user group scopes
            // $scope = [ 'scope' => 'scope name', 'group_membership' => [user group names]]
            foreach ($serviceScopes['scopes'] as $serviceScope) {
                $userGroups[$serviceScope['scope']] = $serviceScope['group_membership'];
            }
            $parsedPermissionedScopes[$serviceScopes['service']] = $userGroups;
        }
        return $parsedPermissionedScopes;
    }
    /**
     * Return custom template paths to be in format
     * 'errors' => [
     *      'errorCode' => 'path'
     * ]
     */
    private function parseCustomTemplatePath(array $customTemplatePath): array
    {
        foreach ($customTemplatePath['errors'] as $errorPath) {
            $parsedPaths['errors'][$errorPath['http_status_code']] = $errorPath['template_path'];
        }
        return $parsedPaths;
    }

    /**
     * Return jwt object to be in format
     * ```
     * 'access' => [
     *      'default_audience' => [],
     *      'default_scopes' => [
     *          'service' => ['scope']
     *       ],
     *      'permissioned_scopes' => [
     *          'service' => [
     *            'scope' => ['group_membership']
     *          ],
     *       ],
     *      <other properties>
     * ]
     * ```
     */
    private function parseJwt(array $jwt): array
    {
        $parsedJwt = $jwt;
        $parsedJwt['access']['default_audience'] = $jwt['access']['services'];
        unset($parsedJwt['access']['services']);
        $parsedJwt['access']['default_scopes'] = $this->parseScopes($jwt['access']['default_scopes']);
        if (isset($parsedJwt['access']['permissioned_scopes'])) {
            $parsedJwt['access']['permissioned_scopes'] = $this->parsePermissionedScopes(
                $jwt['access']['permissioned_scopes']
            );
        }
        return $parsedJwt;
    }
}
