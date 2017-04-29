<?php
namespace Serato\SwsApp\Slim\Middleware;

use Slim\Http\Body;
use \Slim\Handlers\AbstractHandler;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * EmptyWare Middleware
 *
 * An empty middleware for testing purposes.
 */
class EmptyWare extends AbstractHandler
{
    /**
     * Invoke the middleware
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     *
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response) : Response
    {
        return $response;
    }
}
