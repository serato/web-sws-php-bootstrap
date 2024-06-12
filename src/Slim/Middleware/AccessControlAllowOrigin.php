<?php

namespace Serato\SwsApp\Slim\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Access Control Allow Origin Middleware
 *
 * A middleware that sets the `Access-Control-Allow-Origin` header
 */
class AccessControlAllowOrigin
{
    /**
     * Invoke the middleware
     *
     * @param Request $request The most recent Request object
     * @param RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        if ($request->getHeaderLine('Origin') !== '') {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $request->getHeaderLine('Origin'))
                ->withAddedHeader('Vary', 'Origin');
        }
        return $response;
    }
}
