<?php
namespace Serato\SwsApp\Slim\Middleware\AccessScopes;

use Serato\SwsApp\Slim\Middleware\AccessScopes\AbstractAccessScopesMiddleware;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Client App Basic Auth Middleware
 *
 * Exposes scopes of access for a client application that has authenticated using
 * HTTP Basic authentication.
 *
 * The authenticating client application is matched against a provided list of valid
 * client applications that also includes the scopes of access for a given
 * SWS web service
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
        // If there's no auth user or the auth user doesn't exist in the list
        // of client app data it doesn't matter. It simply means that no scopes
        // are assigned to the request object
        if (isset($server_params['PHP_AUTH_USER']) && isset($server_params['PHP_AUTH_PW'])) {
            foreach ($this->clientAppData as $k => $appData) {
                if ($appData['id'] == $server_params['PHP_AUTH_USER'] &&
                    password_verify($server_params['PHP_AUTH_PW'], $appData['password_hash'])
                ) {
                    if (isset($appData['name']) && isset($appData['scopes']) && is_array($appData['scopes'])) {
                        if (isset($appData['scopes'][$this->webServiceName]) &&
                            is_array($appData['scopes'][$this->webServiceName])
                        ) {
                            $request = $this->setClientAppRequestAttributes(
                                $request,
                                $appData['id'],
                                $appData['name'],
                                $appData['scopes'][$this->webServiceName]
                            );
                        }
                    }
                }
            }
        }
        return $next($request, $response);
    }
}
