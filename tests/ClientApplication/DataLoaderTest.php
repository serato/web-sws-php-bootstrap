<?php

namespace Serato\SwsApp\Test\ClientApplication;

use Serato\SwsApp\ClientApplication\DataLoader;
use Serato\SwsApp\Test\TestCase;
use Exception;
use Serato\SwsApp\ClientApplication\Exception\InvalidEnvironmentNameException;
use Serato\SwsApp\ClientApplication\Exception\InvalidFileContentsException;
use Serato\SwsApp\ClientApplication\Exception\MissingApplicationIdException;
use Serato\SwsApp\ClientApplication\Exception\MissingApplicationPassword;
use Serato\SwsApp\ClientApplication\Exception\MissingKmsKeyIdException;

class DataLoaderTest extends TestCase
{
    /**
     * Test invalid environment name is provided
     */
    public function testInvalidEnvironmentName()
    {
        $this->expectException(InvalidEnvironmentNameException::class);
        new DataLoader('invalidEnvName', $this->getAwsSdk(), $this->getFileSystemCachePool());
    }

    /**
     * Test importing malformed app data json
     */
    public function testMalformedAppData()
    {
        $this->expectException(InvalidFileContentsException::class);
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('client-applications.malformed.json', 'secrets.json')),
            $this->getFileSystemCachePool()
        );
        $dataLoader->getApp();
    }

    /**
     * Test secret credentials is missing App id
     */
    public function testCredentialsMissingAppId()
    {
        $this->expectException(MissingApplicationIdException::class);
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('client-applications.json', 'secrets.missing-id.json')),
            $this->getFileSystemCachePool()
        );
        $dataLoader->getApp();
    }

    /**
     * Test secret credentials is missing App password
     */
    public function testCredentialsMissingPassword()
    {
        $this->expectException(MissingApplicationPassword::class);
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('client-applications.json', 'secrets.missing-password.json')),
            $this->getFileSystemCachePool()
        );
        $dataLoader->getApp();
    }

    /**
     * Test secret credentials is missing App kms key id
     */
    public function testCredentialsMissingKmsKeyId()
    {
        $this->expectException(MissingKmsKeyIdException::class);
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('client-applications.json', 'secrets.missing-kms-key.json')),
            $this->getFileSystemCachePool()
        );
        $dataLoader->getApp();
    }

    public function testSuccessfulLoad()
    {
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('client-applications.json', 'secrets.json')),
            $this->getFileSystemCachePool()
        );

        $this->assertValidAppData($dataLoader->getApp());
    }

    public function testLocalDirInvalidDirPath()
    {
        try {
            new DataLoader(
                'dev',
                $this->getAwsSdk(),
                $this->getFileSystemCachePool(),
                './no-such-directory'
            );
            // Shouldn't get this far
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(strpos($e->getMessage(), 'Path does not exist') !== false);
        }
    }

    public function testLocalDirSpecifyFilePath()
    {
        try {
            new DataLoader(
                'dev',
                $this->getAwsSdk(),
                $this->getFileSystemCachePool(),
                rtrim(__DIR__, '/') . '/data/client-applications.json'
            );
            // Shouldn't get this far
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(strpos($e->getMessage(), 'Path is not a directory') !== false);
        }
    }

    public function testLocalDirInvalidJsonFiles()
    {
        try {
            $dataLoader = new DataLoader(
                'dev',
                $this->getAwsSdk(),
                $this->getFileSystemCachePool(),
                rtrim(__DIR__, '/') . '/data/local_malformed'
            );
            $dataLoader->getApp();
            // Shouldn't get this far
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(strpos($e->getMessage(), 'File does not contain valid JSON') !== false);
        }
    }

    public function testLocalDirInvalidFilesDontExist()
    {
        try {
            $dataLoader = new DataLoader(
                'dev',
                $this->getAwsSdk(),
                $this->getFileSystemCachePool(),
                rtrim(__DIR__, '/') . '/data/local_does_not_exist'
            );
            $dataLoader->getApp();
            // Shouldn't get this far
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(strpos($e->getMessage(), 'File does not exist') !== false);
        }
    }

    public function testLocalDirValidFiles()
    {
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk(),
            $this->getFileSystemCachePool(),
            rtrim(__DIR__, '/') . '/data'
        );
        $this->assertValidAppData($dataLoader->getApp());
    }

    /**
     * Creates an array of mock AWS Result objects.
     *
     * The array contains the responses to S3 GetObject requests for the `client-applications.json` file
     * and the SecretsManager GetSecretValue for the client app secrets.
     *
     * @return array
     */
    private function getAwsMockResponses(string $appsFileName, string $secretsFileName): array
    {
        $clientAppResponse = [
            ['Body' => file_get_contents(__DIR__ . '/data/' . $appsFileName)]
        ];

        $secretReponses = json_decode(file_get_contents(__DIR__ . '/data/' . $secretsFileName), true) ?? [];

        return array_merge($clientAppResponse, $secretReponses);
    }

    private function assertValidAppData(array $appData): void
    {
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

        # 5. App 5 should no be present in data (it's defined in `apps.json` but not `credentials.dev.json`)
        $this->assertFalse(isset($appData['App5']));

        # 6. App 6 should no be present in data (it's defined in `credentials.dev.json` but not `apps.json`)
        $this->assertFalse(isset($appData['App6']));
    }
}
