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
use Mockery;
use Mockery\MockInterface;

/**
 * Unit tests for Serato\SwsApp\Slim\Middleware\AccessScopes\AccessToken
 */
class AccessTokenTest extends TestCase
{
    private const MOCK_ENCRYPTION_KEY = '123456789abcdefg';
    private const WEBSERVICE_NAME = 'my.webservice.me';

    /**
     * Call the middleware without a token at all.
     *
     * This is allowed and should work ok. But, obviously, there should be no
     * `scopes` added the $request object
     */
    public function testAccessToken()
    {
        $middleware = new AccessTokenMiddleware(
            $this->getAwsSdk(),
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            $this->getMockMemcached(),
            self::WEBSERVICE_NAME
        );
        $nextMiddleware = new EmptyWare();
        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            new Response(),
            $nextMiddleware
        );

        $this->assertEquals(null, $nextMiddleware->getRequestInterface()->getAttribute(AccessTokenMiddleware::SCOPES));
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

        $rtid = $token->getClaim(AccessTokenMiddleware::REFRESH_TOKEN_ID);
        $mockMemcached = $this->getMockMemcached();

        // Mock cache miss
        $mockMemcached
            ->shouldReceive('get')
            ->withArgs(['r-' . $rtid])
            ->andReturn(false);

        $middleware = new AccessTokenMiddleware(
            $awsSdk,
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            $mockMemcached,
            self::WEBSERVICE_NAME
        );

        $nextMiddleware = new EmptyWare();

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create((string)$token))
            ),
            new Response(),
            $nextMiddleware
        );

        foreach ($this->getAccessTokenCustomClaims() as $name => $value) {
            if ($name === AccessTokenMiddleware::SCOPES) {
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
     * @expectedException \Serato\SwsApp\Http\Rest\Exception\ExpiredAccessTokenException
     */
    public function testMiddlewareWithValidAccessTokenWithInvalidRefreshToken()
    {
        $awsSdk = $this->getAwsSdkWithKmsResults();

        $token = $this->getAccessToken(
            $awsSdk,
            time() + 300,
            [self::WEBSERVICE_NAME]
        );

        $rtid = $token->getClaim(AccessTokenMiddleware::REFRESH_TOKEN_ID);
        $mockMemcached = $this->getMockMemcached();

        // Mock cache hit
        $mockMemcached
            ->shouldReceive('get')
            ->withArgs(['r-' . $rtid])
            ->andReturn($rtid);

        $middleware = new AccessTokenMiddleware(
            $awsSdk,
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            $mockMemcached,
            self::WEBSERVICE_NAME
        );

        $nextMiddleware = new EmptyWare();

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create((string)$token))
            ),
            new Response(),
            $nextMiddleware
        );
    }

    /**
     * Create an Access Token that does NOT have ab 'rtid' claim and ensure that that Request
     * object contains an empty string value for the 'rtid' custom attribute.
     *
     * We need to do this because the 'rtid' claim was added to the Access Token at a later date
     * and some in-flight tokens won't have this claim.
     */
    public function testMiddlewareWithValidateTokenInAuthHeaderNoRefreshTokenId()
    {
        $awsSdk = $this->getAwsSdkWithKmsResults();

        $claims = $this->getAccessTokenCustomClaims();

        # Remove the 'rtid' claim from the data added into the Access Token
        unset($claims[AccessTokenMiddleware::REFRESH_TOKEN_ID]);

        $token = $this->getAccessToken(
            $awsSdk,
            time() + 300,
            [self::WEBSERVICE_NAME],
            $claims
        );

        $middleware = new AccessTokenMiddleware(
            $awsSdk,
            $this->getLogger(),
            $this->getFileSystemCachePool(),
            $this->getMockMemcached(),
            self::WEBSERVICE_NAME
        );

        $nextMiddleware = new EmptyWare();

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create((string)$token))
            ),
            new Response(),
            $nextMiddleware
        );

        # Add the expected empty value back into the data used to check against the custom
        # attributes of the Request object
        $claims[AccessTokenMiddleware::REFRESH_TOKEN_ID] = '';

        foreach ($claims as $name => $value) {
            if ($name === AccessTokenMiddleware::SCOPES) {
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
            $this->getMockMemcached(),
            self::WEBSERVICE_NAME . ' invalidate'
        );

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create((string)$token))
            ),
            new Response(),
            new EmptyWare()
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
            $this->getMockMemcached(),
            self::WEBSERVICE_NAME
        );

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->setAuthorization(BearerToken::create((string)$token))
            ),
            new Response(),
            new EmptyWare()
        );
    }

    /**
     * Returns a mocked memcache instance
     *
     * @return \Memcached&MockInterface
     */
    private function getMockMemcached()
    {
        return Mockery::mock(\Memcached::class);
    }

    private function getAccessToken(Sdk $awsSdk, int $expiry, array $audience, ?array $claims = null)
    {
        $tokenClaims = $claims ?? $this->getAccessTokenCustomClaims();
        $token = new MockAccessToken($awsSdk);
        return $token->create(
            $audience,
            $expiry,
            $tokenClaims
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
            AccessTokenMiddleware::APP_ID               => 'my_app_id',
            AccessTokenMiddleware::APP_NAME             => 'my_app_name',
            AccessTokenMiddleware::USER_ID              => 'my_uid',
            AccessTokenMiddleware::USER_EMAIL           => 'my_email@test.com',
            AccessTokenMiddleware::USER_EMAIL_VERIFIED  => true,
            AccessTokenMiddleware::SCOPES               => [self::WEBSERVICE_NAME => ['scope1', 'scope2']],
            AccessTokenMiddleware::REFRESH_TOKEN_ID     => 'my_refresh_token_id',

        ];
    }
}
