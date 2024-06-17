<?php

namespace Serato\SwsApp\Test\ClientApplication;

use Mockery;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Serato\SwsApp\ClientApplication\DataLoader;
use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\ClientApplication\Exception\InvalidEnvironmentNameException;
use Serato\SwsApp\ClientApplication\Exception\InvalidFileContentsException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter as FileSystemCachePool;
use Symfony\Component\Cache\CacheItem;

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
        $dataLoader->getApp(null, false);
    }

    public function testSuccessfulLoad()
    {
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('client-applications-dev.json', 'secrets.json')),
            $this->getFileSystemCachePool()
        );

        $this->assertValidAppData($dataLoader->getApp(null, false));
    }

    public function testSuccessfulLoadUsingCache()
    {
        $expectedResult = ['Client Apps array'];
        // Mock the cache to return an array
        $cacheItemMock = Mockery::mock(CacheItemInterface::class);
        $cacheItemMock->shouldReceive('get')
            ->once()
            ->andReturn($expectedResult);
        $cacheItemMock->shouldReceive('isHit')
            ->once()
            ->andReturn(true);

        $cachePoolMock = Mockery::mock(CacheItemPoolInterface::class);
        $cachePoolMock
            ->shouldReceive('getItem')
            ->once()
            ->andReturn($cacheItemMock);


        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('client-applications-dev.json', 'secrets.json')),
            $cachePoolMock
        );

        $result = $dataLoader->getApp();

        // Should return what's in the cache and not the parsed data
        $this->assertEquals($expectedResult, $result);
        Mockery::close();
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
        foreach ($appData as $index => $data) {
            // Check resulting array with expected array including the hashed_password
            $this->assertEquals(
                DataLoaderTest::EXPECTED_SUCCESSFUL_OUTPUT[$index]['expectedArrayWithHashedPassword'],
                $data
            );
        }
    }

    private const EXPECTED_SUCCESSFUL_OUTPUT = [
        [
            'expectedArrayWithHashedPassword' => [
                'name' => 'Application 1',
                'description' => 'Application with JWT params basic default scopes',
                'seas' => false,
                'seasAfterSignIn' => false,
                'forcePasswordReEntryOnLogout' => false,
                'requiresPasswordReEntry' => false,
                'jwt' => [
                    'access' => [
                        'default_scopes' => [
                            'ecom.serato.com' => ['user-read', 'user-write']
                        ],
                        'expires' => 900,
                        'default_audience' => ['ecom.serato.com']
                    ],
                    'refresh' => [
                        'expires' => 31536000
                    ],
                    'kms_key_id' => 'kms-key-id-1'
                ],
                'id' => 'id-1',
                'hashed_password' => '$2y$10$JALUJZhEAwwechMrF5Ixfe/4x8VG5pmJLod1FEchAFw0TkFUWc90a'
            ]
        ],
        [
            'expectedArrayWithHashedPassword' => [
                'name' => 'Application 2',
                'description' => 'Application with JWT params and `restricted_to` settings',
                'seas' => false,
                'seasAfterSignIn' => false,
                'forcePasswordReEntryOnLogout' => false,
                'requiresPasswordReEntry' => false,
                'jwt' => [
                    'access' => [
                        'default_scopes' => [
                            'license.serato.io' => ['user-license', 'user-license-activation']
                        ],
                        'expires' => 900,
                        'default_audience' => ['license.serato.io'],
                        'restricted_to' => ['Serato']
                    ],
                    'refresh' => [
                        'expires' => 31536000
                    ],
                    'kms_key_id' => 'kms-key-id-2'
                ],
                'id' => 'id-2',
                'hashed_password' => '$2y$10$JALUJZhEAwwechMrF5Ixfe/4x8VG5pmJLod1FEchAFw0TkFUWc90b'
            ]
        ],
        [
            'expectedArrayWithHashedPassword' => [
                'name' => 'Application 3',
                'description' => 'Application with JWT params and lots default and permissioned scopes',
                'seas' => true,
                'seasAfterSignIn' => false,
                'forcePasswordReEntryOnLogout' => false,
                'requiresPasswordReEntry' => false,
                'refreshTokenGroup' => 'serato-website',
                'jwt' => [
                    'access' => [
                        'default_scopes' => [
                            'license.serato.io' => ['user-license', 'user-license-activation'],
                            'id.serato.io' => ['user-get', 'user-update'],
                            'ecom.serato.com' => ['user-read', 'user-write']
                        ],
                        'permissioned_scopes' => [
                            'license.serato.io' => [
                                'user-license-admin' => [
                                    ['Root'],
                                    ['Serato', 'Support'],
                                    ['Serato', 'License Admin']
                                ],
                                'product-batch-read' => [
                                    ['Root'],
                                    ['Serato', 'Product Batch - Read only'],
                                    ['Serato', 'Product Batch - Admin']
                                ],
                                'product-batch-admin' => [
                                    ['Root'],
                                    ['Serato', 'Product Batch - Admin']
                                ]
                            ],
                            'id.serato.io' => [
                                'user-admin' => [
                                    ['Root'],
                                    ['Serato', 'Support']
                                ],
                                'user-groups-admin' => [
                                    ['Root'],
                                    ['Serato']
                                ]
                            ],
                            'ecom.serato.com' => [
                                'admin-user-read' => [
                                    ['Root'],
                                    ['Serato', 'Support']
                                ],
                                'admin-user-write' => [
                                    ['Root'],
                                    ['Serato', 'Support']
                                ]
                            ]
                        ],
                        'expires' => 900,
                        'default_audience' => [
                            'id.serato.io',
                            'license.serato.io',
                            'ecom.serato.com'
                        ]
                    ],
                    'refresh' => [
                        'expires' => 31536000
                    ],
                    'kms_key_id' => 'kms-key-id-3'
                ],
                'id' => 'id-3',
                'hashed_password' => '$2y$10$JALUJZhEAwwechMrF5Ixfe/4x8VG5pmJLod1FEchAFw0TkFUWc90c'
            ]
        ],
        [
            'expectedArrayWithHashedPassword' => [
                'name' => 'Application 4',
                'description' => 'Application with default scopes',
                'seas' => false,
                'seasAfterSignIn' => false,
                'forcePasswordReEntryOnLogout' => false,
                'requiresPasswordReEntry' => false,
                'scopes' => [
                    'profile.serato.com' => ['profile-edit-admin'],
                ],
                'jwt' => [
                    'kms_key_id' => 'kms-key-id-4'
                ],
                'id' => 'id-4',
                'hashed_password' => '$2y$10$JALUJZhEAwwechMrF5Ixfe/4x8VG5pmJLod1FEchAFw0TkFUWc90d'
            ]
        ],
        [
            'expectedArrayWithHashedPassword' => [
                'name' => 'Application 5',
                'description' => 'Combination of basic scopes and JWT token',
                'seas' => false,
                'seasAfterSignIn' => false,
                'forcePasswordReEntryOnLogout' => false,
                'requiresPasswordReEntry' => false,
                'scopes' => [
                    'license.serato.io' => ['app-license-admin', 'user-license'],
                ],
                'jwt' => [
                    'access' => [
                        'default_scopes' => [
                            'license.serato.io' => ['user-license', 'user-license-activation']
                        ],
                        'expires' => 900,
                        'default_audience' => ['license.serato.io'],
                    ],
                    'refresh' => [
                        'expires' => 31536000
                    ],
                    'kms_key_id' => 'kms-key-id-5'
                ],
                'id' => 'id-5',
                'hashed_password' => '$2y$10$JALUJZhEAwwechMrF5Ixfe/4x8VG5pmJLod1FEchAFw0TkFUWc90e'
            ]
        ],
        [
            'expectedArrayWithHashedPassword' => [
                'name' => 'Application 6',
                'description' => 'Application with JWT params and `custom_template_path` settings',
                'seas' => false,
                'seasAfterSignIn' => true,
                'forcePasswordReEntryOnLogout' => true,
                'requiresPasswordReEntry' => false,
                'jwt' => [
                    'access' => [
                        'default_scopes' => [
                            'license.serato.io' => ['user-license', 'user-license-activation']
                        ],
                        'expires' => 900,
                        'default_audience' => ['license.serato.io'],
                        'restricted_to' => ['Serato']
                    ],
                    'refresh' => [
                        'expires' => 31536000
                    ],
                    'kms_key_id' => 'kms-key-id-6'
                ],
                'custom_template_path' => [
                    'errors' => [
                        '403' => 'pages/error/403.studio.html'
                    ]
                ],
                'id' => 'id-6',
                'hashed_password' => '$2y$10$JALUJZhEAwwechMrF5Ixfe/4x8VG5pmJLod1FEchAFw0TkFUWc90f'
            ]
        ]
    ];
}
