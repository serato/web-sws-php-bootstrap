<?php

namespace Serato\SwsApp\Slim\Middleware;

use GuzzleHttp\Psr7\Utils;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Maintenance Middleware
 *
 * This middleware renders a 503 Service Unavailable response when constructed
 * with $maintenanceMode = `true`.
 */
class MaintenanceMode
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
     * @param Request $request The most recent Request object
     * @param RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if ($this->maintenanceMode) {
            return $this->defaultMaintenancePage($request, $handler);
        } else {
            return $handler->handle($request);
        }
    }

    /**
     * Render page content and set response headers
     *
     * @param Request $request The most recent Request object
     * @param RequestHandler $handler
     * @return Response
     */
    public function defaultMaintenancePage(Request $request, RequestHandler $handler): Response
    {
        // For testing
        $negotiator = new Negotiator();
        $acceptHeader = $negotiator->getBest(
            $request->getHeaderLine('Accept'),
            ['application/json', 'text/xml', 'text/html']
        );
        $contentType = $acceptHeader ? $acceptHeader->getType() : 'text/html';

        $output = match ($contentType) {
            'application/json' => json_encode(
                ['message' => '503 Service Unavailable'],
                JSON_PRETTY_PRINT
            ),
            'text/xml', 'application/xml' => '<xml><message>503 Service Unavailable</message></xml>',
            default => '<html><body>503 Service Unavailable</body></html>',
        };

        $resource = Utils::tryFopen('php://temp', 'r+');
        $body = Utils::streamFor($resource);
        $body->write($output);

        $response = $handler->handle($request);

        return $response
            ->withHeader('Content-type', $contentType)
            ->withStatus(503)
            ->withBody($body);
    }
}
