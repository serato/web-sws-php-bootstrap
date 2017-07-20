<?php
namespace Serato\SwsApp\Test\Slim\Middleware\AccessScopes;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Test\Slim\Middleware\AccessScopes\AccessToken as MockAccessToken;
use Serato\SwsApp\Slim\Middleware\AccessScopes\AccessToken as AccessTokenMiddleware;
use Serato\SwsApp\Slim\Middleware\EmptyWare;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Serato\Slimulator\Authorization\BearerToken;
use Slim\Http\Response;
use Aws\Sdk;

/**
 * Unit tests for Serato\SwsApp\Slim\Middleware\AccessScopes\AccessToken
 */
class AccessTokenTest extends TestCase
{
    const MOCK_ENCRYPTION_KEY = '123456789abcdefg';
    const WEBSERVICE_NAME = 'my.webservice.me';

    /**
     * @expectedException \Serato\SwsApp\Http\Rest\Exception\InvalidAccessTokenException
     */
    public function testAccessToken()
    {
        $middleware = new AccessTokenMiddleware(
            $this->getAwsSdk(),
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            self::WEBSERVICE_NAME
        );
        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            new Response,
            new EmptyWare
        );
    }

    /**
     * Call the middleware with a valid token. ie. The token:
     * - Includes the provided webservice name with it's `aud` claim
     * - Is not expired
     */
    public function testMiddlewareWithValidateTokenInAuthHeader()
    {
        $awsSdk = $this->getAwsSdkWithKmsResults();

        $token = $this->getAccessToken(
            $awsSdk,
            time() + 300,
            [self::WEBSERVICE_NAME]
        );

        $middleware = new AccessTokenMiddleware(
            $awsSdk,
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            self::WEBSERVICE_NAME
        );

        $nextMiddleware = new EmptyWare;

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create((string)$token))
            ),
            new Response,
            $nextMiddleware
        );

        foreach ($this->getAccessTokenCustomClaims() as $name => $value) {
            if ($name == 'scopes') {
                $scopes = $nextMiddleware->getRequestInterface()->getAttribute($name);
                $this->assertEquals(
                    $value[self::WEBSERVICE_NAME],
                    $scopes,
                    "Assert value of '$name' from request attributes"
                );
            } else {
                $this->assertEquals(
                    $value,
                    $nextMiddleware->getRequestInterface()->getAttribute($name),
                    "Assert value of '$name' from request attributes"
                );
            }
        }
    }

    /**
     * Force a token validation error by checking the `aud` claim against a value
     * that doesn't exist in the token
     *
     * @expectedException \Serato\SwsApp\Http\Rest\Exception\InvalidAccessTokenException
     */
    public function testMiddlewareWithInvalidAudienceTokenInAuthHeader()
    {
        $awsSdk = $this->getAwsSdkWithKmsResults();

        $token = $this->getAccessToken(
            $awsSdk,
            time() + 300,
            [self::WEBSERVICE_NAME]
        );

        $middleware = new AccessTokenMiddleware(
            $awsSdk,
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            self::WEBSERVICE_NAME . ' invalidate'
        );

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create((string)$token))
            ),
            new Response,
            new EmptyWare
        );
    }

    /**
     * @expectedException \Serato\SwsApp\Http\Rest\Exception\ExpiredAccessTokenException
     */
    public function testMiddlewareWithExpiredTokenInAuthHeader()
    {
        $awsSdk = $this->getAwsSdkWithKmsResults();

        $token = $this->getAccessToken(
            $awsSdk,
            time() - 5, // Expired
            [self::WEBSERVICE_NAME]
        );

        $middleware = new AccessTokenMiddleware(
            $awsSdk,
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            self::WEBSERVICE_NAME
        );

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create((string)$token))
            ),
            new Response,
            new EmptyWare
        );
    }

    private function getAccessToken(Sdk $awsSdk, int $expiry, array $audience)
    {
        $token = new MockAccessToken($awsSdk);
        return $token->create(
            $audience,
            $expiry,
            $this->getAccessTokenCustomClaims()
        );
    }

    private function getAwsSdkWithKmsResults(): Sdk
    {
        return $this->getAwsSdk([
            [
                'CiphertextBlob'    => base64_encode(self::MOCK_ENCRYPTION_KEY),
                'Plaintext'         => self::MOCK_ENCRYPTION_KEY
            ],
            [
                'Plaintext'         => self::MOCK_ENCRYPTION_KEY
            ]
        ]);
    }

    private function getAccessTokenCustomClaims()
    {
        return [
            'app_id'            => 'my_app_id',
            'app_name'          => 'my_app_name',
            'uid'               => 'my_uid',
            'email'             => 'my_email@test.com',
            'email_verified'    => true,
            'scopes'            => [self::WEBSERVICE_NAME => ['scope1', 'scope2']]
        ];
    }
}
