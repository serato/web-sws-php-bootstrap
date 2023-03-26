<?php

namespace Serato\SwsApp\Slim\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Handlers\AbstractHandler;

/**
 * Class FrameAncestorsCspHeader
 *
 * Adds a CSP header with frame-ancestors rule, which gets merged with the other CSP rules by a browser.
 *
 * @package App\Middleware
 */
class FrameAncestorsCspHeader extends AbstractHandler
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
        $response = $response
            ->withAddedHeader('Content-Security-Policy', "frame-ancestors 'none';");

        return $response;
    }
}
