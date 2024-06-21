<?php

namespace Serato\SwsApp\Test\ClientApplication;

use Mockery;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Serato\SwsApp\ClientApplication\DataLoader;
use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\ClientApplication\Exception\InvalidEnvironmentNameException;
use Serato\SwsApp\ClientApplication\Exception\InvalidFileContentsException;

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
            $this->getAwsSdk($this->getAwsMockResponses('client-applications.malformed.json')),
            $this->getFileSystemCachePool()
        );
        $dataLoader->getApp(null, false);
    }

    public function testSuccessfulLoad()
    {
        $dataLoader = new DataLoader(
            'dev',
            $this->getAwsSdk($this->getAwsMockResponses('client-applications-dev.json')),
            $this->getFileSystemCachePool()
        );

        $this->assertEquals(
            DataLoaderTest::EXPECTED_SUCCESSFUL_OUTPUT,
            $dataLoader->getApp(null, false)
        );
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
            $this->getAwsSdk($this->getAwsMockResponses('client-applications-dev.json')),
            $cachePoolMock
        );

        $result = $dataLoader->getApp();

        // Should return what's in the cache and not the parsed data
        $this->assertEquals($expectedResult, $result);
        Mockery::close();
    }

    /**
     * Creates an array of mock AWS responses.
     *
     * The array contains the response from `client-applications-dev.json` in S3
     *
     * @param string $appsFileName The name of the JSON file
     * @return array
     */
    private function getAwsMockResponses(string $appsFileName): array
    {
        return [
            ['Body' => file_get_contents(__DIR__ . '/data/' . $appsFileName)]
        ];
    }

    /**
    * The expected output array with the correct array structure used by the client applications.
    * This should not be changed unless we change it in the client applications!
    */
    private const EXPECTED_SUCCESSFUL_OUTPUT = [
        [
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
            'id' => '11111111-1111-1111-1111-111111111111',
            'password_hash' => 'password-hash-1'
        ],
        [
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
            'id' => '22222222-2222-2222-2222-222222222222',
            'password_hash' => 'password-hash-2'
        ],
        [
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
            'id' => '33333333-3333-3333-3333-333333333333',
            'password_hash' => 'password-hash-3'
        ],
        [
            'name' => 'Application 4',
            'description' => 'Application with default scopes',
            'seas' => false,
            'seasAfterSignIn' => false,
            'forcePasswordReEntryOnLogout' => false,
            'requiresPasswordReEntry' => false,
            'scopes' => [
                'profile.serato.com' => ['profile-edit-admin'],
            ],
            'id' => '44444444-4444-4444-4444-444444444444',
            'password_hash' => 'password-hash-4',
            'jwt' => [
                'kms_key_id' => 'kms-key-id-4'
            ]
        ],
        [
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
            'id' => '55555555-5555-5555-5555-555555555555',
            'password_hash' => 'password-hash-5'
        ],
        [
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
            'id' => '66666666-6666-6666-6666-666666666666',
            'password_hash' => 'password-hash-6'
        ]
    ];
}
