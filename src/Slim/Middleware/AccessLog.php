<?php

namespace Serato\SwsApp\Slim\Middleware;

use Serato\SwsApp\AccessLogWriter;
use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AccessLog
{
    /* @var AccessLogWriter */
    private $accessLogWriter;

    /**
     * Construct the error handler
     *
     * @param Logger    $logger          PSR-3 logger interface
     * @param string    $logLevel        The log level to write entries to
     * @param array     $bodyParamNames         Body parameter names to log
     */
    public function __construct(Logger $logger, string $logLevel = 'INFO', array $bodyParamNames = [])
    {
        $this->accessLogWriter = new AccessLogWriter($logger, $logLevel, $bodyParamNames);
    }

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
        $this->accessLogWriter->log($request, $response);
        return $response;
    }
}
