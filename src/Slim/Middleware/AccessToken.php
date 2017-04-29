<?php
namespace Serato\SwsApp\Slim\Middleware;

use Aws\Sdk;
use Exception;
use Psr\Log\LoggerInterface;
use Slim\Handlers\AbstractHandler;
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
class AccessToken extends AbstractHandler
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
     * @param Sdk               $awsSdk
     * @param LoggerInterface   $logger
     * @param string            $webServiceName
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

        if ($tokenString === null) {
            throw new InvalidAccessTokenException;
        }

        $accessToken = new JwtAccessToken($this->getAwsSdk());

        try {
            $accessToken->parseTokenString((string)$tokenString, $this->cache);
            $accessToken->validate($this->webServiceName);
            foreach ($this->getAccessTokenClaims($accessToken) as $k => $v) {
                $request = $request->withAttribute($k, $v);
            }
        } catch (TokenExpiredException $e) {
            throw new ExpiredAccessTokenException;
        } catch (Exception $e) {
            throw new InvalidAccessTokenException;
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
     * Get claims from JWT Access Token
     *
     * @param JwtAccessToken $accessToken
     *
     * @return array
     *
     */
    private function getAccessTokenClaims(JwtAccessToken $accessToken): array
    {
        $scopes = [];

        if (is_array($accessToken->getClaim('scopes')) &&
            isset($accessToken->getClaim('scopes')[$this->webServiceName])
        ) {
            $scopes = $accessToken->getClaim('scopes')[$this->webServiceName];
        }

        return [
            'app_id'            => $accessToken->getClaim('app_id'),
            'app_name'          => $accessToken->getClaim('app_name'),
            'uid'               => $accessToken->getClaim('uid'),
            'email'             => $accessToken->getClaim('email'),
            'email_verified'    => $accessToken->getClaim('email_verified'),
            'scopes'            => $scopes
        ];
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
