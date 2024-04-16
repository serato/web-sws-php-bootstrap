<?php

namespace Serato\SwsApp\Slim\Middleware;

use Slim\Http\Body;
use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Maintenance Middleware
 *
 * This middleware renders a 503 Service Unavailable response when constructed
 * with $maintenanceMode = `true`.
 */
class MaintenanceMode extends AbstractHandler
{
    /**
     * Maintenance mode flag
     *
     * @var bool
     */
    protected $maintenanceMode;

    public function __construct(bool $maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;
    }

    /**
     * Invoke the middleware
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Callable               $next      The next middleware to call
     *
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        if ($this->maintenanceMode) {
            return $this->defaultMaintenancePage($request, $response);
        } else {
            return $next($request, $response);
        }
    }

    /**
     * Render page content and set response headers
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     *
     * @return ResponseInterface
     */
    public function defaultMaintenancePage(Request $request, Response $response): Response
    {
        $contentType = $this->determineContentType($request);

        switch ($contentType) {
            case 'application/json':
                $output = json_encode(
                    ['message' => '503 Service Unavailable'],
                    JSON_PRETTY_PRINT
                );
                break;
            case 'text/xml':
            case 'application/xml':
                $output = '<xml><message>503 Service Unavailable</message></xml>'; // TODO
                break;
            case 'text/html':
                $output = '<html><body>503 Service Unavailable</body></html>'; // TODO
                break;
        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response
            ->withHeader('Content-type', $contentType)
            ->withStatus(503)
            ->withBody($body);
    }
}
