<?php

namespace Serato\SwsApp\ClientApplication;

use DateTime;
use Aws\Sdk as AwsSdk;
use Aws\SecretsManager\SecretsManagerClient;
use Psr\Cache\CacheItemPoolInterface;
use Serato\SwsApp\ClientApplication\Exception\InvalidEnvironmentNameException;
use Serato\SwsApp\ClientApplication\Exception\InvalidFileContentsException;

/**
 * Client Application Data Loader
 *
 * Loads client application data from S3
 */

class DataLoader
{
    private const CACHE_EXPIRY_TIME = 10; // seconds
    private const CLIENT_APPS_DATA_CACHE_KEY = 'SWS-Client-Applications-Data';
    private const ENVIRONMENTS = ['dev', 'test', 'production'];
    private const S3_BUCKET_NAME = 'sws.clientapps';
    private const S3_BASE_PATH = 'v3';

    /** @var string */
    private $env;

    /** @var AwsSdk */
    private $awsSdk;

    /** @var CacheItemPoolInterface */
    private $psrCache;

    /** @var SecretsManagerClient */
    private $secretsManagerClient;

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
        $this->secretsManagerClient = $this->awsSdk->createSecretsManager(['version' => '2017-10-17']);
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

        $cacheKey = str_replace(['\\', '/'], '_', __CLASS__ . '--' . self::CLIENT_APPS_DATA_CACHE_KEY. '-v2');
        var_dump($cacheKey);

        // Read from cache, if specified
        if ($useCache) {
            $item = $this->psrCache->getItem($cacheKey);
            if ($item->isHit()) {
//                var_dump($item);
                var_dump('cache hit');
                return $item->get();
            }
        }

        // Fetch client-applications-{$env}.json from S3
        $clientAppsRawData = $this->loadFromS3(self::S3_BASE_PATH . "/client-applications-{$env}.json");

        // Generate output array
        $clientAppsData = $this->parseClientAppData($clientAppsRawData);

        // Write to cache regardless of `$useCache` setting
        $this->saveToCache($cacheKey, $clientAppsData);
        return $clientAppsData;
    }

    /**
     * Save data to cache if available.
     *
     * @param string $cacheKey The cache key under which to store the data.
     * @param array $data The client apps data to be stored in the cache.
     * @return void
     */
    private function saveToCache(string $cacheKey, array $data): void
    {
        $item = $this->psrCache->getItem($cacheKey);
        $expiryTime = new DateTime();
        $expiryTime->setTimestamp(time() + self::CACHE_EXPIRY_TIME);
        var_dump('cache expiry time: ' . $expiryTime->getTimestamp());
        var_dump($expiryTime->getTimezone()->getName());
        $item->set($data);
//        $item->expiresAfter(self::CACHE_EXPIRY_TIME);
        $item->expiresAt($expiryTime);
        $this->psrCache->save($item);
        var_dump('saved');
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
     * Merges environment specific credentials with the provided client app
     * data.
     *
     * @param array $clientAppsData data from client-applications.json
     * @return array
     */
    private function parseClientAppData(array $clientAppsData): array
    {
        $data = [];
        foreach ($clientAppsData as $appData) {
            // Add all data
            $parsedData = $appData;
            // Exclude certain keys: 'path' is new property, 'basic_auth_scopes' is renamed to 'scopes' and
            //'restricted_to' is nested in the 'jwt' objectin the output array
            unset($parsedData['path'], $parsedData['basic_auth_scopes'], $parsedData['restricted_to']);

            // Format scopes if present
            if (isset($appData['basic_auth_scopes'])) {
                $parsedData['scopes'] = $this->parseScopes($appData['basic_auth_scopes']);
            }

            // Format the optional `custom_template_path` item
            if (isset($appData['custom_template_path'])) {
                $parsedData['custom_template_path'] = $this->parseCustomTemplatePath($appData['custom_template_path']);
            }

            $parsedData['jwt'] = isset($appData['jwt']) ? $this->parseJwt($appData['jwt']) : [];

            // Always add `kms_key_id` inside jwt
            $parsedData['jwt']['kms_key_id'] = $appData['kms_key_id'];
            unset($parsedData['kms_key_id']);

            // Add optional `restricted_to` item
            if (isset($appData['restricted_to'])) {
                if (!isset($parsedData['jwt']['access'])) {
                    $parsedData['jwt']['access'] = [];
                }
                $parsedData['jwt']['access']['restricted_to'] = $appData['restricted_to'];
            }

            // Transform some keys from snake case to camel case
            if (isset($appData['seas_after_sign_in'])) {
                $parsedData['seasAfterSignIn'] = $appData['seas_after_sign_in'];
                unset($parsedData['seas_after_sign_in']);
            }
            if (isset($appData['force_password_re_entry_on_logout'])) {
                $parsedData['forcePasswordReEntryOnLogout'] = $appData['force_password_re_entry_on_logout'];
                unset($parsedData['force_password_re_entry_on_logout']);
            }
            if (isset($appData['requires_password_re_entry'])) {
                $parsedData['requiresPasswordReEntry'] = $appData['requires_password_re_entry'];
                unset($parsedData['requires_password_re_entry']);
            }
            if (isset($appData['refresh_token_group'])) {
                $parsedData['refreshTokenGroup'] = $appData['refresh_token_group'];
                unset($parsedData['refresh_token_group']);
            }

            array_push($data, $parsedData);
        }

        return $data;
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
