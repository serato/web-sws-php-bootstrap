<?php

namespace Serato\SwsApp\Slim\Middleware\AccessScopes;

use Serato\SwsApp\Slim\Middleware\AccessScopes\AbstractAccessScopesMiddleware;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Client App Basic Auth Middleware
 *
 * Exposes client application ID, client application name and, optionally, scopes of access for a client application
 * that has authenticated using HTTP Basic authentication.
 *
 * The authenticating client application is matched against a provided list of valid client applications.
 * If a match is found, the client application ID and name are exposed to the web application via custom atttributes
 * of a `Psr\Http\Message\RequestInterface` instance.
 *
 * If the web application also defines scopes of access for the authenticating client application these too are exposed
 * via a custom attribute of the `Psr\Http\Message\RequestInterface` instance.
 */
class ClientAppBasicAuth extends AbstractAccessScopesMiddleware
{
    /**
     * Name of the host web application
     *
     * @var string
     */
    protected $webServiceName;

    /**
     * Array of valid client applications
     *
     * @var array
     */
    protected $clientAppData;

    /**
     * @param string    $webServiceName     Name of the host web application
     * @param array     $clientAppData      Array of valid client applications
     *
     */
    public function __construct(string $webServiceName, array $clientAppData)
    {
        $this->webServiceName = $webServiceName;
        $this->clientAppData = $clientAppData;
    }

    /**
     * Invoke the middleware
     *
     * @param Request $request The most recent Request object
     * @param Response $response The most recent Response object
     * @param callable $next
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $server_params = $request->getServerParams();
        if (isset($server_params['PHP_AUTH_USER']) && isset($server_params['PHP_AUTH_PW'])) {
            foreach ($this->clientAppData as $k => $appData) {
                if (
                    $appData['id'] == $server_params['PHP_AUTH_USER'] &&
                    password_verify($server_params['PHP_AUTH_PW'], $appData['password_hash'])
                ) {
                    if (isset($appData['name'])) {
                        # There may be no per-service scopes defined for a given application (that's valid and
                        # allowed).
                        # If there are no scopes, we'll only have an app ID and name added to the request object.
                        # But if there are, we're only interested in the scopes defined for a specific web service.
                        # This will (usually? always?) correlate with the web service that is implementing this
                        # middleware.
                        $scopes = [];
                        if (
                            isset($appData['scopes']) && is_array($appData['scopes']) &&
                            isset($appData['scopes'][$this->webServiceName]) &&
                            is_array($appData['scopes'][$this->webServiceName])
                        ) {
                            $scopes = $appData['scopes'][$this->webServiceName];
                        }
                        $request = $this->setClientAppRequestAttributes(
                            $request,
                            $appData['id'],
                            $appData['name'],
                            $scopes
                        );
                    }
                }
            }
        }
        return $next($request, $response);
    }
}
