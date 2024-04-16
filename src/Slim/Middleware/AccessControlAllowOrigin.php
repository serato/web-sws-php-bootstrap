<?php

namespace Serato\SwsApp\Slim\Middleware;

use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Access Control Allow Origin Middleware
 *
 * A middleware that sets the `Access-Control-Allow-Origin` header
 */
class AccessControlAllowOrigin extends AbstractHandler
{
    /**
     * Invoke the middleware
     *
     * @param Request           $request   The most recent Request object
     * @param Response          $response  The most recent Response object
     * @param Callable          $next      The next middleware to call
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $response = $next($request, $response);
        if ($request->getHeaderLine('Origin') !== '') {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $request->getHeaderLine('Origin'))
                ->withAddedHeader('Vary', 'Origin');
        }
        return $response;
    }
}
