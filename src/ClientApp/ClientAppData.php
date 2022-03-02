<?php

namespace Serato\SwsApp\ClientApp;

use DateTime;
use Exception;
use Aws\Sdk;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Client App Data
 *
 * Fetch a list of client application data from a cache, S3 object or file on a
 * file system.
 *
 * A file or S3 object should a JSON string representation of the data array.
 *
 * Cached data is a native PHP array structure.
 */

class ClientAppData
{
    private const CACHE_KEY = 'SWS-App-Request-ClientAppData';
    private const CACHE_EXPIRY_TIME = 3600; // seconds

    /**
     * AWS SDK client
     *
     * @var Sdk
     */
    protected $aws;

    /**
     * PSR-6 cache item pool
     *
     * @var CacheItemPoolInterface
     */
    protected $psrCache;

    /**
     * Indicates whether the most recent data load came from the cache
     *
     * @var bool
     */
    protected $isCacheHit = false;

    /**
     * Constructs the object
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param CacheItemPoolInterface    $psrCache   PSR-6 cache item pool
     */
    public function __construct(CacheItemPoolInterface $psrCache)
    {
        $this->psrCache = $psrCache;
    }

    /**
     * Returns a new ClientAppData object.
     *
     * @param CacheItemPoolInterface    $psrCache   PSR-6 cache item pool
     *
     * @return ClientAppData
     */
    public static function create(CacheItemPoolInterface $psrCache): ClientAppData
    {
        return new ClientAppData($psrCache);
    }

    /**
     * Load client app data from a file
     *
     * @param string    $filePath   Path to file
     * @return array
     * @throws Exception
     */
    public function loadFromFile(string $filePath): array
    {
        $data = $this->getFromCache();
        if ($data !== null) {
            return $data;
        }
        if (file_exists($filePath)) {
            return $this->saveToCache(
                $this->parseJsonString(file_get_contents($filePath))
            );
        }
        throw new Exception(
            "Invalid client app data file path '$filePath'. " .
            "File does not exist."
        );
    }

    /**
     * Load client app data from an object in an S3 bucket
     *
     * @param Sdk       $aws            AWS client
     * @param string    $bucketName     Name of S3 bucket
     * @param string    $key            Path of S3 object
     *
     * @return array
     */
    public function loadFromS3Object(Sdk $aws, string $bucketName, string $key): array
    {
        $data = $this->getFromCache();
        if ($data !== null) {
            return $data;
        }
        $result = $aws->createS3(['version' => '2006-03-01'])->getObject(
            ['Bucket' => $bucketName, 'Key' => $key]
        );
        return $this->saveToCache(
            $this->parseJsonString((string)$result['Body'])
        );
    }

    /**
     * Determine if most recent load of data came from the cache
     *
     * @return bool
     */
    public function isCacheHit(): bool
    {
        return $this->isCacheHit;
    }

    /**
     * Parse file contents into an array
     *
     * @param string $json JSON string data
     * @return array
     * @throws Exception
     */
    private function parseJsonString(string $json): array
    {
        $data = json_decode($json, true);
        if ($data === null || !is_array($data)) {
            throw new Exception("Invalid client app data. Data is not valid JSON.");
        }
        return $data;
    }

    /**
     * Get client app data from the cache
     *
     * @todo Specify nullable return type in PHP 7.1
     *
     * @return array
     */
    private function getFromCache()
    {
        $this->isCacheHit = false;
        $item = $this->psrCache->getItem(self::CACHE_KEY);
        if ($item->isHit()) {
            $this->isCacheHit = true;
            return $item->get();
        }
    }

    /**
     * Save client app data to the cache
     *
     * @param array  $data   Array of Client App data
     * @return array Data stored in the cache
     */
    private function saveToCache(array $data): array
    {
        $item = $this->psrCache->getItem(self::CACHE_KEY);
        $expiryTime = new DateTime();
        $expiryTime->setTimestamp(time() + self::CACHE_EXPIRY_TIME);
        $item->set($data);
        $item->expiresAt($expiryTime);
        $this->psrCache->save($item);
        return $data;
    }
}
