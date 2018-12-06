<?php
namespace Serato\SwsApp\Test\ClientApplication;

use Aws\Sdk;
use Serato\SwsApp\ClientApplication\DataLoader;
use Serato\SwsApp\Test\TestCase;

class DataLoaderTest extends TestCase
{
    /**
     * @expectedException \Serato\SwsApp\ClientApplication\Exception\InvalidEnvironmentNameException
     */
    public function testInvalidEnvironmentName()
    {
        $dataLoader = new DataLoader('invalidEnvName', $this->getAwsSdk(), $this->getFileSystemCachePool());
    }

    /**
     * @expectedException \Serato\SwsApp\ClientApplication\Exception\InvalidFileContentsException
     */
    public function testMalformedAppData()
    {
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('apps.malformed.json', 'credentials.json')),
            $this->getFileSystemCachePool()
        );
        $dataLoader->getApp();
    }

    /**
     * @expectedException \Serato\SwsApp\ClientApplication\Exception\InvalidFileContentsException
     */
    public function testMalformedCredentialsData()
    {
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('apps.json', 'credentials.malformed.json')),
            $this->getFileSystemCachePool()
        );
        $dataLoader->getApp();
    }

    /**
     * @expectedException \Serato\SwsApp\ClientApplication\Exception\MissingApplicationIdException
     */
    public function testCredentialsMissingAppId()
    {
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('apps.json', 'credentials.missing-id.json')),
            $this->getFileSystemCachePool()
        );
        $dataLoader->getApp();
    }

    /**
     * @expectedException \Serato\SwsApp\ClientApplication\Exception\MissingApplicationPasswordHash
     */
    public function testCredentialsMissingPasswordHash()
    {
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('apps.json', 'credentials.missing-password.json')),
            $this->getFileSystemCachePool()
        );
        $dataLoader->getApp();
    }

    public function testSuccessfulLoad()
    {
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('apps.json', 'credentials.json')),
            $this->getFileSystemCachePool()
        );
        $appData = $dataLoader->getApp();

        # Make sure that the correct number of apps are loaded
        $this->assertEquals(4, count($appData));
        
        # *** Validate some details ***
        
        # 1. App 1 should have JWT settings but no 'restricted_to` setting
        $this->assertTrue(isset($appData['App1']['jwt']));
        $this->assertFalse(isset($appData['App1']['jwt']['access']['restricted_to']));

        # 2. App 2 should have JWT settings and a 'restricted_to` setting
        $this->assertTrue(isset($appData['App2']['jwt']));
        $this->assertTrue(isset($appData['App2']['jwt']['access']['restricted_to']));

        # 3. App 3 should have no JWT settings
        $this->assertFalse(isset($appData['App3']['jwt']));

        # 4. App 4 should have JWT settings with `permissioned_scopes`
        $this->assertTrue(isset($appData['App4']['jwt']));
        $this->assertTrue(isset($appData['App4']['jwt']['access']['permissioned_scopes']));

        # 5. App 5 should no be present in data (it's defined in `apps.json` but not `credentials.json`)
        $this->assertFalse(isset($appData['App5']));

        # 6. App 6 should no be present in data (it's defined in `credentials.json` but not `apps.json`)
        $this->assertFalse(isset($appData['App6']));
    }

    /**
     * Creates an array of mock AWS Result objects.
     *
     * The array contains two items corresponding to S3 GetObject
     * requests for the `apps.json` file and `credentials.json` file.
     *
     * @return array
     */
    private function getAwsMockResponses(string $appsFileName, string $credentialsFileName): array
    {
        # FYI `Serato\SwsApp\ClientApplication\DataLoader` always loads the apps file first
        return [
            ['Body' => file_get_contents(__DIR__ . '/data/' . $appsFileName)],
            ['Body' => file_get_contents(__DIR__ . '/data/' . $credentialsFileName)]
        ];
    }
}
