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
        foreach ($appData as $app => $data) {
            // Compare array structure with expected structure ignoring the password_hash
            $arrayDiff = array_diff(
                $data,
                DataLoaderTest::EXPECTED_SUCCESSFUL_OUTPUT[$app]['expectedArrayMinusPasswordHash']
            );

            // Check password_hash
            $this->assertEquals(1, count($arrayDiff));
            $this->assertTrue(isset($arrayDiff['password_hash']));
            $this->assertTrue(password_verify(
                DataLoaderTest::EXPECTED_SUCCESSFUL_OUTPUT[$app]['password'],
                $arrayDiff['password_hash']
            ));
        }
    }

    private const EXPECTED_SUCCESSFUL_OUTPUT = [
        [
            'expectedArrayMinusPasswordHash' => [
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
                    'kms_key_id' => 'kmsKeyId1'
                ],
                'id' => '1'
            ],
            'password' => 'password1'
        ],
        [
            'expectedArrayMinusPasswordHash' => [
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
                    'kms_key_id' => 'kmsKeyId2'
                ],
                'id' => '2'
            ],
            'password' => 'password2'
        ],
        [
            'expectedArrayMinusPasswordHash' => [
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
                    'kms_key_id' => 'kmsKeyId3'
                ],
                'id' => '3'
            ],
            'password' => 'password3'
        ],
        [
            'expectedArrayMinusPasswordHash' => [
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
                    'kms_key_id' => 'kmsKeyId4'
                ],
                'id' => '4'
            ],
            'password' => 'password4'
        ],
        [
            'expectedArrayMinusPasswordHash' => [
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
                    'kms_key_id' => 'kmsKeyId5'
                ],
                'id' => '5'
            ],
            'password' => 'password5'
        ],
        [
            'expectedArrayMinusPasswordHash' => [
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
                    ],
                    'refresh' => [
                        'expires' => 31536000
                    ],
                    'kms_key_id' => 'kmsKeyId2'
                ],
                'custom_template_path' => [
                    'errors' => [
                        '403' => 'pages/error/403.studio.html'
                    ]
                ],
                'id' => '6'
            ],
            'password' => 'password6'
        ]
    ];
}
