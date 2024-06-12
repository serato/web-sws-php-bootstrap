<?php

namespace Serato\SwsApp\Slim\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * EmptyWare Middleware
 *
 * An empty middleware for testing purposes.
 */
class EmptyWare
{
    /**
     * Request Interface
     *
     * @var Request
     */
    protected $request;

    /**
     * Response Interface
     *
     * @var Response
     */
    protected $response;

    /**
     * Invoke the middleware
     *
     * @param Request   $request   The most recent Request object
     * @param Response  $response  The most recent Response object
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $this->request = $request;
        $this->response = $handler->handle($request);

        return $this->response;
    }

    /**
     * Get the Request Interface
     */
    public function getRequestInterface(): Request
    {
        return $this->request;
    }

    /**
     * Get the Response Interface
     */
    public function getResponseInterface(): Response
    {
        return $this->response;
    }
}
