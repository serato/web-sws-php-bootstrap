<?php

namespace Serato\SwsApp\Slim\Middleware;

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Serato\SwsApp\RequestToContainerTrait;

class RequestToContainer
{
    use RequestToContainerTrait;

    /** @var Container */
    private $container;

    /**
     * @param bool $maintenanceMode
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
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
        # Execute all other middleware first
        $response = $handler->handle($request);
        $this->setRequestToContainer($request, $this->container);
        return $response;
    }
}
