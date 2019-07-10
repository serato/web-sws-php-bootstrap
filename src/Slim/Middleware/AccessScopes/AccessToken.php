<?php
namespace Serato\SwsApp\Slim\Middleware\AccessScopes;

use Aws\Sdk;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Serato\SwsApp\Slim\Middleware\AccessScopes\AbstractAccessScopesMiddleware;
use Serato\Jwt\AccessToken as JwtAccessToken;
use Serato\Jwt\Exception\TokenExpiredException;
use Serato\SwsApp\Http\Rest\Exception\ExpiredAccessTokenException;
use Serato\SwsApp\Http\Rest\Exception\InvalidAccessTokenException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Cache\CacheItemPoolInterface;

/**
 * AccessToken Middleware
 *
 * A Slim middleware for parsing a JWT access token string, validating the token,
 * and extracting claims from the token.
 *
 */
class AccessToken extends AbstractAccessScopesMiddleware
{
    /**
     * AWS Sdk
     *
     * @var Sdk
     */
    private $awsSdk;

    /**
     * Web Service Name
     *
     * @var string
     */
    protected $webServiceName;

    /**
     * PSR-3 Logger interface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * PSR-6 cache item pool
     *
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @param Sdk                       $awsSdk             AWS SDK v3.x
     * @param LoggerInterface           $logger             PSR-3 logger interface
     * @param CacheItemPoolInterface    $cache              PSR-6 cache item pool
     * @param string                    $webServiceName     Name of the host web application
     *
     */
    public function __construct(
        Sdk $awsSdk,
        LoggerInterface $logger,
        CacheItemPoolInterface $cache,
        string $webServiceName
    ) {
        $this->awsSdk = $awsSdk;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->webServiceName = $webServiceName;
    }

    /**
     * Invoke the middleware
     *
     * @param Request $request The most recent Request object
     * @param Response $response The most recent Response object
     * @param callable $next
     *
     * @throws ExpiredAccessTokenException
     * @throws InvalidAccessTokenException
     *
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $tokenString = null;

        // Look the token string in the `Authorization` header
        $tokenString = $this->getTokenStringFromAuthHeader($request);

        // TODO in the future
        # Look for the token string in other places. eg a Cookie

        if ($tokenString !== null) {
            $accessToken = new JwtAccessToken($this->getAwsSdk());
            try {
                $accessToken->parseTokenString((string)$tokenString, $this->cache);
                $accessToken->validate($this->webServiceName);

                $scopes = $this->getAccessTokenScopes($accessToken);

                $request = $this->setClientAppRequestAttributes(
                    $request,
                    $accessToken->getClaim('app_id'),
                    $accessToken->getClaim('app_name'),
                    $scopes
                );


                $refreshTokenId = '';

                # Need to gracefully handle tokens that don't have the 'rtid' claim because there will
                # in-flight tokens that don't have this claim prior to when this functionality was added.
                try {
                    $refreshTokenId = $accessToken->getClaim('rtid');
                } catch (InvalidArgumentException $e) {
                    // Ignore for this claim
                }

                $request = $request
                    ->withAttribute(self::USER_ID, $accessToken->getClaim('uid'))
                    ->withAttribute(self::USER_EMAIL, $accessToken->getClaim('email'))
                    ->withAttribute(self::USER_EMAIL_VERIFIED, $accessToken->getClaim('email_verified'))
                    ->withAttribute(self::REFRESH_TOKEN_ID, $refreshTokenId);
            } catch (TokenExpiredException $e) {
                throw new ExpiredAccessTokenException;
            } catch (Exception $e) {
                throw new InvalidAccessTokenException;
            }
        }
        return $next($request, $response);
    }

    /**
     * Get the Access Token string from the `Authorization` HTTP header
     *
     * @param Request $request Request interface
     *
     * @return string|null
     *
     */
    private function getTokenStringFromAuthHeader(Request $request)
    {
        if (count($request->getHeader('Authorization')) === 0) {
            return;
        }
        $authorizationHeader = $request->getHeader('Authorization')[0];
        if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return $matches[1];
        }
        return;
    }

    /**
     * Get `scopes` claim from JWT Access Token
     *
     * @param JwtAccessToken $accessToken
     *
     * @return array
     *
     */
    private function getAccessTokenScopes(JwtAccessToken $accessToken): array
    {
        $scopes = [];

        if (is_array($accessToken->getClaim('scopes')) &&
            isset($accessToken->getClaim('scopes')[$this->webServiceName])
        ) {
            $scopes = $accessToken->getClaim('scopes')[$this->webServiceName];
        }

        return $scopes;
    }

    /**
     * Get the AWS Sdk
     *
     * @return Sdk
     */
    private function getAwsSdk(): Sdk
    {
        return $this->awsSdk;
    }

    /**
     * Get the logger interface instance
     *
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
