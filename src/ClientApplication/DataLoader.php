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
    private const ENV_CREDENTIALS_NAME_PATTERN = 'credentials.__env__.json';
    private const CREDENTIALS_ENV_PLACEHOLDER = '__env__';

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

        $credentialsObject = $this->getCredentialsObjectName($env);

        return $this->mergeCredentials(
            $this->getItem(self::COMMON_APP_DATA_NAME, $useCache),
            $this->getItem($credentialsObject, $useCache),
            $credentialsObject
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
     * Returns the name of an environment-specific credentials object
     *
     * @param string $env
     * @return string
     */
    public function getCredentialsObjectName(string $env): string
    {
        return str_replace(
            self::CREDENTIALS_ENV_PLACEHOLDER,
            $env,
            self::ENV_CREDENTIALS_NAME_PATTERN
        );
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
    private function getSecrets(): array
    {
        $secretsManagerClient = $this->awsSdk->createSecretsManager(['version' => '2017-10-17']);
        
        $secrets = [];
        $next = '';
        while ($next !== null) {
            $listPayload = [
                'Filters' => [
                    [
                        'Key' => 'name',
                        'Values' => array_map(
                            function ($env) {
                                return $env . '/' . self::CLIENT_APPS_SECRET_PREFIX . '/';
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
                    $secrets[(string) $item['Name']] = [
                        'arn' => (string) $item['ARN'],
                        'secret' => (string) $item['SecretString'],
                    ];
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
     * @param array $commonData
     * @param array $credentialsData
     * @return array
     *
     * @throws MissingApplicationIdException
     * @throws MissingApplicationPasswordHash
     */
    private function mergeCredentials(array $commonData, array $credentialsData, string $credentialsObjectPath): array
    {
        $data = [];
        foreach ($commonData as $appName => $appData) {
            if (isset($credentialsData[$appName])) {
                // All apps MUST have `id` and `password_hash` keys defined
                if (!isset($credentialsData[$appName]['id'])) {
                    throw new MissingApplicationIdException(
                        'Invalid configuration for application `' . $appName . '` in credentials file `' .
                        $credentialsObjectPath . '`. Missing required key `id`.'
                    );
                }
                if (!isset($credentialsData[$appName]['password_hash'])) {
                    throw new MissingApplicationPasswordHash(
                        'Invalid configuration for application `' . $appName . '` in credentials file `' .
                        $credentialsObjectPath . '`. Missing required key `password_hash`.'
                    );
                }

                // Add common and required data
                $data[$appName] = $appData;
                $data[$appName]['id'] = $credentialsData[$appName]['id'];
                $data[$appName]['password_hash'] = $credentialsData[$appName]['password_hash'];

                // Add optional `kms_key_id` item
                if (isset($credentialsData[$appName]['kms_key_id'])) {
                    if (!isset($data[$appName]['jwt'])) {
                        $data[$appName]['jwt'] = [];
                    }
                    $data[$appName]['jwt']['kms_key_id'] = $credentialsData[$appName]['kms_key_id'];
                }

                // Add option `restricted_to` item
                if (isset($credentialsData[$appName]['restricted_to'])) {
                    if (!isset($data[$appName]['jwt'])) {
                        $data[$appName]['jwt'] = [];
                    }
                    if (!isset($data[$appName]['jwt']['access'])) {
                        $data[$appName]['jwt']['access'] = [];
                    }
                    $data[$appName]['jwt']['access']['restricted_to'] = $credentialsData[$appName]['restricted_to'];
                }
            }
        }
        return $data;
    }
}
