<?php

namespace Serato\SwsApp\Test;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Aws\Sdk;
use Aws\Result;
use Aws\MockHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as FileSystemCachePool;

class TestCase extends PHPUnitTestCase
{
    private const FILE_SYSTEM_CACHE_NAMESPACE = 'tests';

    protected $fileSystemCacheDir;
    protected static $fileSystemCachePool;

    protected function setUp(): void
    {
        $this->fileSystemCacheDir = sys_get_temp_dir() . '/fs-cache';
        $this->deleteFileSystemCacheDir();
    }

    protected function tearDown(): void
    {
        $this->deleteFileSystemCacheDir();
    }

    protected function getAwsSdk(array $mockResults = []): Sdk
    {
        $mock = new MockHandler();
        foreach ($mockResults as $result) {
            $mock->append(new Result($result));
        }
        return new Sdk([
            'region' => 'us-east-1',
            'version' => '2014-11-01',
            'credentials' => [
                'key' => 'my-access-key-id',
                'secret' => 'my-secret-access-key'
            ],
            'handler' => $mock
        ]);
    }

    protected function getLogger(): Logger
    {
        return new Logger('logger');
    }

    /**
     * @throws \Exception
     */
    protected function getDebugLogger(): Logger
    {
        $logger = $this->getLogger();
        $debugLogStream = new StreamHandler(
            __DIR__ . '/log/debug.log',
            Logger::DEBUG
        );
        $logger->pushHandler($debugLogStream);
        return $logger;
    }

    /**
     * Gets a PSR-6 compliant file system based cache pool
     *
     * @return FileSystemCachePool
     */
    protected function getFileSystemCachePool(): FileSystemCachePool
    {
        if (self::$fileSystemCachePool === null) {
            self::$fileSystemCachePool = new FileSystemCachePool('tests', 0, $this->fileSystemCacheDir);
        }
        return self::$fileSystemCachePool;
    }

    protected function deleteFileSystemCacheDir()
    {
        if ($this->fileSystemCacheDir !== null && is_dir($this->fileSystemCacheDir)) {
            exec('rm -rf ' . escapeshellarg($this->fileSystemCacheDir));
        }
    }

    protected function hasCacheFiles(): bool
    {
        $cache_dir = $this->fileSystemCacheDir . '/' . self::FILE_SYSTEM_CACHE_NAMESPACE;
        return is_dir($cache_dir) && count(glob($cache_dir . '/*')) > 0;
    }
}
