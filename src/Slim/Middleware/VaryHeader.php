<?php

namespace Serato\SwsApp\Slim\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Vary Header Middleware
 *
 * A middleware that sets the `Vary` header
 */
class VaryHeader
{
    /**
     * Invoke the middleware
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        return $response->withAddedHeader('Vary', 'Origin, Accept-Encoding');
    }
}
