<?php
namespace Serato\SwsApp\Slim\Middleware;

use Slim\Http\Body;
use Slim\Handlers\AbstractHandler;
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
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response) : Response
    {
        $this->request = $request;
        $this->response = $response;
        
        return $response;
    }

    /**
     * Get the Request Interface
     *
     * @return Request
     */
    public function getRequestInterface(): Request
    {
        return $this->request;
    }

    /**
     * Get the Response Interface
     *
     * @return Response
     */
    public function getResponseInterface(): Response
    {
        return $this->response;
    }
}
