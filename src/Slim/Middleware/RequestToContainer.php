<?php

namespace Serato\SwsApp\Slim\Middleware;

use Slim\Container;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Serato\SwsApp\RequestToContainerTrait;

class RequestToContainer
{
    use RequestToContainerTrait;

    /** @var Container */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Invoke the middleware
     *
     * @param Request $request The most recent Request object
     * @param Response $response The most recent Response object
     * @param Callable $next The next middleware to call
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        # Execute all other middleware first
        $response = $next($request, $response);
        $this->setRequestToContainer($request, $this->container);
        return $response;
    }
}
