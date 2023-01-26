<?php

namespace Serato\SwsApp\Slim\Middleware;

use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Vary Header Middleware
 *
 * A middleware that sets the `Vary` header
 */
class VaryHeader extends AbstractHandler
{
    /**
     * Invoke the middleware
     *
     * @param Request           $request   The most recent Request object
     * @param Response          $response  The most recent Response object
     * @param Callable          $next      The next middleware to call
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $response = $next($request, $response);
        $response = $response->withAddedHeader('Vary', ['Origin', 'Accept-Encoding']);

        return $response;
    }
}
