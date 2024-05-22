<?php

namespace Serato\SwsApp\ClientApplication;

use Exception;
use DateTime;
use Aws\Sdk as AwsSdk;
use Psr\Cache\CacheItemPoolInterface;
use Serato\SwsApp\ClientApplication\Exception\InvalidEnvironmentNameException;
use Serato\SwsApp\ClientApplication\Exception\InvalidFileContentsException;
use Serato\SwsApp\ClientApplication\Exception\MissingApplicationIdException;
use Serato\SwsApp\ClientApplication\Exception\MissingApplicationPasswordHash;
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

    /** @var array */
    private $loadEnv = [];

    /** @var AwsSdk */
    private $awsSdk;

    /** @var CacheItemPoolInterface */
    private $psrCache;

    /** @var string */
    private $localDirPath;

    /**
     * Constructs the object
     *
     * @param string                    $env            Application environment
     * @param AwsSdk                    $awsSdk         AWS SDK
     * @param CacheItemPoolInterface    $psrCache       PSR-6 cache item pool
     * @param string                    $localDirPath   Path to a directory where configuration files can be found.
     *                                                  Overrides `$awsSdk` and `$psrCache` parameters.
     */
    public function __construct(
        string $env,
        AwsSdk $awsSdk,
        CacheItemPoolInterface $psrCache,
        string $localDirPath = null
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

        if ($localDirPath !== null) {
            $this->localDirPath = realpath($localDirPath);
            if ($this->localDirPath === false) {
                throw new Exception("Invalid directory path '" . $this->localDirPath . "'. Path does not exist.");
            }
            if (!is_dir($this->localDirPath)) {
                throw new Exception("Invalid directory path '" . $this->localDirPath . "'. Path is not a directory.");
            }
        }

        // Load all environment data in `dev` environment
        if ($this->env === 'dev') {
            $this->loadEnv = self::ENVIRONMENTS;
        } else {
            $this->loadEnv = [$this->env];
        }
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
            $this->getItem(self::COMMON_APP_DATA_NAME, $useCache)
        );
    }

    /**
     * Returns a data config item. Will look in the cache for the item if `$useCache = true`.
     *
     * @return array
     */
    public function getItem(string $name, bool $useCache = true): array
    {
        if ($this->localDirPath !== null) {
            return $this->loadFromLocalDirectory($name);
        } else {
            return $this->loadFromCache($name, $useCache);
        }
    }

    /**
     * Load application data from a file in a local directory.
     *
     * @return array
     */
    private function loadFromLocalDirectory(string $name): array
    {
        $filePath = rtrim($this->localDirPath, '/') . '/' . $name;
        if (file_exists($filePath)) {
            $data = json_decode((string)file_get_contents($filePath), true);
            if ($data === null) {
                throw new Exception("Invalid file path '$filePath'. File does not contain valid JSON.");
            } else {
                return $data;
            }
        } else {
            throw new Exception("Invalid file path '$filePath'. File does not exist.");
        }
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
    private function getSecrets(?string $appPath = null): array
    {
        $secretsManagerClient = $this->awsSdk->createSecretsManager(['version' => '2017-10-17']);
        if ($appPath !== null) {
            $appPath = ltrim($appPath, '/');
            if ($appPath === '') {
                $appPath = null;
            }
        }

        $secrets = [];
        $next = '';
        while ($next !== null) {
            $listPayload = [
                'Filters' => [
                    [
                        'Key' => 'name',
                        'Values' => array_map(
                            function ($env) use ($appPath) {
                                return $env . '/' . self::CLIENT_APPS_SECRET_PREFIX . '/' .
                                ($appPath === null ? '' : $appPath);
                            },
                            self::ENVIRONMENTS
                        ),
                    ],
                ],
            ];

            if ($next !== '') {
                $listPayload['NextToken'] = $next;
            }

            $result = $secretsManagerClient->batchGetSecretValue($listPayload);

            if (isset($result['SecretValues']) && is_array($result['SecretValues'])) {
                foreach ($result['SecretValues'] as $item) {
                    $secrets[(string) $item['Name']] = json_decode($item['SecretString']);
                }
            }

            if (isset($result['NextToken']) && $result['NextToken'] !== '') {
                $next = $result['NextToken'];
            } else {
                $next = null;
            }
        };

        return $secrets;
    }

    /**
     * Merges environment specific credentials
     *
     * @param array $clientAppsData data from client-applications.json
     * @param array $credentialsData
     * @return array
     *
     * @throws MissingApplicationIdException
     * @throws MissingApplicationPasswordHash
     */
    private function parseClientAppData(array $clientAppsData): array
    {
        $data = [];
        foreach ($clientAppsData as $appData) {
            $credentialsData = $this->getSecrets($appData['path']);
            $appName = $appData['path'];
            $appSecretName = $this->getSecretName($appData['path'], $this->env);
            if (isset($credentialsData[$appSecretName])) {
                // All apps MUST have `appId`, `appSecret` and `kmsKeyId` keys defined
                if (!isset($credentialsData[$appSecretName]['appId'])) {
                    throw new MissingApplicationIdException(
                        'Invalid configuration for application `' . $appSecretName . '` in Secrets Manager `' .
                        $$appSecretName . '`. Missing required key `appId`.'
                    );
                }
                if (!isset($credentialsData[$appSecretName]['appSecret'])) {
                    throw new MissingApplicationPasswordHash(
                        'Invalid configuration for application `' . $appSecretName . '` in credentials file `' .
                        $$appSecretName . '`. Missing required key `appSecret`.'
                    );
                }
                if (!isset($credentialsData[$appSecretName]['kmsKeyId'])) {
                    throw new MissingKmsKeyIdException(
                        'Invalid configuration for application `' . $appSecretName . '` in credentials file `' .
                        $$appSecretName . '`. Missing required key `kmsKeyId`.'
                    );
                }

                // Add common and required data
                $data[$appName]['name'] = $appData['name'];
                $data[$appName]['description'] = $appData['description'];
                $data[$appName]['id'] = $credentialsData[$appSecretName]['appId'];
                $data[$appName]['password_hash'] = password_hash(
                    $credentialsData[$appSecretName]['appSecret'],
                    PASSWORD_DEFAULT
                );
                $data[$appName]['forcePasswordReEntryOnLogout'] = $appData['forcePasswordReEntryOnLogout'];
                $data[$appName]['seasAfterSignIn'] = $appData['seasAfterSignIn'];

                // Add scopes if present
                if (isset($appData['scopes'])) {
                    $data[$appName]['scopes'] = $this->formatScopes($appData['basic_auth_scopes']);
                }

                if (isset($appData['jwt'])) {
                    $data[$appName]['jwt'] =  $this->formatJwt($appData['jwt']);
                    $data[$appName]['jwt']['kms_key_id'] = $credentialsData[$appSecretName]['kmsKeyId'];
                }

                // Add optional `custom_template_path` item
                if (isset($appData['custom_template_path'])) {
                    $data[$appName]['custom_template_path'] = $this->formatCustomTemplatePath(
                        $appData['custom_template_path']
                    );
                }
                // Add optional `restricted_to` item
                if (isset($credentialsData[$appSecretName]['restricted_to'])) {
                    if (!isset($data[$appName]['jwt'])) {
                        $data[$appName]['jwt'] = [];
                    }
                    if (!isset($data[$appName]['jwt']['access'])) {
                        $data[$appName]['jwt']['access'] = [];
                    }
                    $data[$appName]['jwt']['access']['restricted_to'] = $appData['restricted_to'];
                }
            }
        }
        return $data;
    }

    private function getSecretName(string $secretPath, string $env): string
    {
        return $env . '/' . self::CLIENT_APPS_SECRET_PREFIX . '/' . $secretPath;
    }

    private function formatScopes(array $scopes): array
    {
        $formattedScopes = [];
        foreach ($scopes as $scope) {
            $formattedScopes[$scope['service']] = $scope['scopes'];
        }
        return $formattedScopes;
    }

    private function formatCustomTemplatePath(array $customTemplatePath): array
    {
        $paths = [];
        foreach ($customTemplatePath['errors'] as $errorPath) {
            $paths['errors'][$errorPath['http_status_code'] = $errorPath['template_path']];
        }
        return $paths;
    }

    private function formatJwt(array $jwt): array
    {
        $formattedJwt = $jwt;
        $formattedJwt['access']['default_audience'] = $jwt['services'];
        unset($formattedJwt['access']['services']);
        $formattedJwt['access']['default_scopes'] = $this->formatScopes($jwt['access']['default_scopes']);
        $formattedJwt['access']['permissioned_scopes'] = $this->formatScopes($jwt['access']['permissioned_scopes']);

        return $formattedJwt;
    }
}
