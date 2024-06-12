<?php

namespace Serato\SwsApp\Slim\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Class FrameAncestorsCspHeader
 *
 * Adds a CSP header with frame-ancestors rule, which gets merged with the other CSP rules by a browser.
 * Applications where this middleware is used, can't be framed inside a frame or iframe tag.
 *
 * @package App\Middleware
 */
class FrameAncestorsCspHeader
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

        return $response
            ->withAddedHeader('Content-Security-Policy', "frame-ancestors 'none';");
    }
}
