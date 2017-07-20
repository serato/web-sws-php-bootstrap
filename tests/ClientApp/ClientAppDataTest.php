<?php
namespace Serato\SwsApp\Test\ClientApp;

use Aws\Sdk;
use Serato\SwsApp\ClientApp\ClientAppData;
use Serato\SwsApp\Test\TestCase;

class ClientAppDataTest extends TestCase
{
    /**
     * Load a file that doesn't exist
     *
     * @expectedException \Exception
     */
    public function testCreateFromNonExistentFile()
    {
        $this->deleteFileSystemCacheDir();
        $filePath = __DIR__ . '/../resources/client_apps_invalid.json.nosuchthing';
        $clientAppData = ClientAppData::create($this->getFileSystemCachePool());
        $clientAppData->loadFromFile($filePath);
    }

    /**
     * Load a file containing a non-valid JSON string
     *
     * @expectedException \Exception
     */
    public function testCreateFromInvalidJsonFile()
    {
        $this->deleteFileSystemCacheDir();
        $filePath = __DIR__ . '/../resources/client_apps_invalid.json';
        $clientAppData = ClientAppData::create($this->getFileSystemCachePool());
        $clientAppData->loadFromFile($filePath);
    }

    /**
     * Load a file containing a valid JSON string
     */
    public function testCreateFromValidJsonFile()
    {
        $this->deleteFileSystemCacheDir();
        $filePath = __DIR__ . '/../resources/client_apps.json';
        $clientAppData = ClientAppData::create($this->getFileSystemCachePool());
        // Should load OK and return the data
        $this->assertTrue(
            is_array($clientAppData->loadFromFile($filePath))
        );
        // This is the first load, so it should not come from the cache
        $this->assertFalse($clientAppData->isCacheHit());
        // Re-load the data
        $this->assertTrue(
            is_array($clientAppData->loadFromFile($filePath))
        );
        // This SHOULD come from the cache
        $this->assertTrue($clientAppData->isCacheHit());
    }

    /**
     * Load invalid data from S3 bucket
     *
     * @expectedException \Exception
     */
    public function testCreateInvalidFromS3Bucket()
    {
        $aws = $this->getAwsSdk([['Body' => 'not valid json content']]);
        $clientAppData = ClientAppData::create($this->getFileSystemCachePool());
        $clientAppData->loadFromS3Object($aws, 'mybucket', 'mykey');
    }

    /**
     * Load valid data from S3 bucket
     */
    public function testCreateValidFromS3Bucket()
    {
        $this->deleteFileSystemCacheDir();
        $filePath = __DIR__ . '/../resources/client_apps.json';
        $aws = $this->getAwsSdk([['Body' => file_get_contents($filePath)]]);

        $clientAppData = ClientAppData::create($this->getFileSystemCachePool());

        // Should load OK and return the data
        $this->assertTrue(
            is_array($clientAppData->loadFromS3Object($aws, 'mybucket', 'mykey'))
        );

        // This is the first load, so it should not come from the cache
        $this->assertFalse($clientAppData->isCacheHit());
        // Re-load the data
        $this->assertTrue(
            is_array($clientAppData->loadFromS3Object($aws, 'mybucket', 'mykey'))
        );
        // This SHOULD come from the cache
        $this->assertTrue($clientAppData->isCacheHit());
    }
}
