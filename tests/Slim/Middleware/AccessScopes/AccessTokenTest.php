<?php

namespace Serato\SwsApp\Test\Slim\Middleware\AccessScopes;

use Aws\Kms\KmsClient;
use Aws\Result;
use Serato\SwsApp\Http\Rest\Exception\ExpiredAccessTokenException;
use Serato\SwsApp\Http\Rest\Exception\InvalidAccessTokenException;
use Serato\SwsApp\Slim\Middleware\AbstractRequestWithAttributeMiddleware;
use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Middleware\AccessScopes\AccessToken as AccessTokenMiddleware;
use Serato\SwsApp\Slim\Middleware\EmptyWare;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Serato\Slimulator\Authorization\BearerToken;
use Slim\Http\Response;

/**
 * Unit tests for Serato\SwsApp\Slim\Middleware\AccessScopes\AccessToken
 */
class AccessTokenTest extends TestCase
{
    private const WEBSERVICE_NAME = 'my.webservice.me';
    private const APP_ID = 'my_app_id';
    private const APP_NAME = 'my_app_name';
    private const USER_ID = 242342342;
    private const USER_EMAIL_ADDRESS = 'my_email@test.com';
    private const SCOPES = [self::WEBSERVICE_NAME => ['scope1', 'scope2']];
    protected const MOCK_ENCRYPTION_KEY = '123456789abcdefg';

    /**
     * Call the middleware without a token at all.
     *
     * This is allowed and should work ok. But, obviously, there should be no
     * `scopes` added the $request object
     */
    public function testAccessToken()
    {
        $middleware = new AccessTokenMiddleware(
            $this->getKmsClient(),
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            self::WEBSERVICE_NAME
        );
        $nextMiddleware = new EmptyWare();
        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            new Response(),
            $nextMiddleware
        );

        $this->assertEquals(null, $nextMiddleware->getRequestInterface()->getAttribute(
            AbstractRequestWithAttributeMiddleware::SCOPES));
    }

    /**
     * Call the middleware with a valid token. ie. The token:
     * - Includes the provided webservice name with it's `aud` claim
     * - Is not expired
     */
    public function testMiddlewareWithValidateTokenInAuthHeader()
    {
        $kmsClient = $this->getKmsClient();
        $accessToken = new \Serato\Jwt\AccessToken($kmsClient,  $this->getFileSystemCachePool());
        $accessToken->set(
            self::APP_ID,
            self::APP_NAME,
            self::MOCK_ENCRYPTION_KEY,
            [self::WEBSERVICE_NAME],
            100,
            self::SCOPES,
            AbstractRequestWithAttributeMiddleware::REFRESH_TOKEN_ID,
            self::USER_ID,
            self::USER_EMAIL_ADDRESS,
            true
        );
        $token = $accessToken->__toString();

        $middleware = new AccessTokenMiddleware(
            $kmsClient,
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            self::WEBSERVICE_NAME
        );

        $nextMiddleware = new EmptyWare();

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create($token))
            ),
            new Response(),
            $nextMiddleware
        );

        foreach ($this->getAccessTokenCustomClaims() as $name => $value) {
            if ($name === AbstractRequestWithAttributeMiddleware::SCOPES) {
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
     * Call the middleware with an otherwise valid token. ie. The token:
     * - Includes the provided webservice name with it's `aud` claim
     * - Is not expired
     *
     * However its parent refresh token has been invalidated i.e. it exists in memcache.
     *
     */
    public function testMiddlewareWithValidAccessTokenWithInvalidRefreshToken()
    {
        $this->expectException(ExpiredAccessTokenException::class);
        $kmsClient = $this->getKmsClient();
        $cachePool = $this->getFileSystemCachePool();
        $accessToken = new \Serato\Jwt\AccessToken($kmsClient,  $cachePool);
        $accessToken->set(
            self::APP_ID,
            self::APP_NAME,
            self::MOCK_ENCRYPTION_KEY,
            [self::WEBSERVICE_NAME],
            100,
            self::SCOPES,
            AbstractRequestWithAttributeMiddleware::REFRESH_TOKEN_ID,
            self::USER_ID,
            self::USER_EMAIL_ADDRESS,
            true
        );
        $token = $accessToken->__toString();

        $rtid = $accessToken->getClaim(AbstractRequestWithAttributeMiddleware::REFRESH_TOKEN_ID);

        # EXPIRE the refresh token by adding it to the cache.
        $refreshToken = $cachePool->getItem('r-' .  AbstractRequestWithAttributeMiddleware::REFRESH_TOKEN_ID);
        if (!$refreshToken->isHit())
        {
            $refreshToken->set($rtid);
            $cachePool->save($refreshToken);
        }

        $middleware = new AccessTokenMiddleware(
            $kmsClient,
            $this->getLogger(),
            $cachePool,
            self::WEBSERVICE_NAME
        );

        $nextMiddleware = new EmptyWare();

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create($token))
            ),
            new Response(),
            $nextMiddleware
        );
    }

    /**
     * Force a token validation error by checking the `aud` claim against a value
     * that doesn't exist in the token
     *
     */
    public function testMiddlewareWithInvalidAudienceTokenInAuthHeader()
    {
        $this->expectException(InvalidAccessTokenException::class);
        $kmsClient = $this->getKmsClient();

        $accessToken = new \Serato\Jwt\AccessToken($kmsClient);
        $accessToken->set(
            self::APP_ID,
            self::APP_NAME,
            self::MOCK_ENCRYPTION_KEY,
            [self::WEBSERVICE_NAME],
            100,
            self::SCOPES,
            AbstractRequestWithAttributeMiddleware::REFRESH_TOKEN_ID,
            self::USER_ID,
            self::USER_EMAIL_ADDRESS,
            true
        );

        $token = $accessToken->__toString();

        $middleware = new AccessTokenMiddleware(
            $kmsClient,
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            self::WEBSERVICE_NAME . ' invalidate'
        );

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create($token))
            ),
            new Response(),
            new EmptyWare()
        );
    }

    /**
     * Unit test to test if the correct exception is thrown for expired access tokens.
     */
    public function testMiddlewareWithExpiredTokenInAuthHeader()
    {
        $this->expectException(ExpiredAccessTokenException::class);
        $kmsClient = $this->getKmsClient();

        $accessToken = new \Serato\Jwt\AccessToken($kmsClient);
        $accessToken->set(
            self::APP_ID,
            self::APP_NAME,
            self::MOCK_ENCRYPTION_KEY,
            [self::WEBSERVICE_NAME],
            -50,
            self::SCOPES,
            AbstractRequestWithAttributeMiddleware::REFRESH_TOKEN_ID,
            self::USER_ID,
            self::USER_EMAIL_ADDRESS,
            true
        );

        $token = $accessToken->__toString();

        $middleware = new AccessTokenMiddleware(
            $kmsClient,
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            self::WEBSERVICE_NAME
        );

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create($token))
            ),
            new Response(),
            new EmptyWare()
        );
    }

    /**
     * @return array
     */
    private function getAccessTokenCustomClaims(): array
    {
        return [
            AbstractRequestWithAttributeMiddleware::APP_ID               => self::APP_ID,
            AbstractRequestWithAttributeMiddleware::APP_NAME             => self::APP_NAME,
            AbstractRequestWithAttributeMiddleware::USER_ID              => self::USER_ID,
            AbstractRequestWithAttributeMiddleware::USER_EMAIL           => self::USER_EMAIL_ADDRESS,
            AbstractRequestWithAttributeMiddleware::USER_EMAIL_VERIFIED  => true,
            AbstractRequestWithAttributeMiddleware::SCOPES               => self::SCOPES,
            AbstractRequestWithAttributeMiddleware::REFRESH_TOKEN_ID     => AbstractRequestWithAttributeMiddleware::REFRESH_TOKEN_ID,

        ];
    }

    /**
     * @return KmsClient
     */
    protected function getKmsClient(): KmsClient
    {
        $mockKMSClient =  \Mockery::mock(KmsClient::class);
        $mockKMSClient->shouldReceive('generateDataKey')->once()->andReturns(new Result(
            [
                'CiphertextBlob'    => base64_encode(self::MOCK_ENCRYPTION_KEY),
                'Plaintext'         => self::MOCK_ENCRYPTION_KEY
            ]
        ));
        $mockKMSClient->shouldReceive('decrypt')->once()->andReturns(new Result(
            [
                'CiphertextBlob'    => base64_encode(self::MOCK_ENCRYPTION_KEY),
                'Plaintext'         => self::MOCK_ENCRYPTION_KEY
            ]
        ));
        return $mockKMSClient;
    }
}
