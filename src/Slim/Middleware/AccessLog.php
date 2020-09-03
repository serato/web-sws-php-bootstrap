<?php
namespace Serato\SwsApp\Slim\Middleware;

use Serato\SwsApp\AccessLogWriter;
use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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
     * @param Request           $request   The most recent Request object
     * @param Response          $response  The most recent Response object
     * @param Callable          $next      The next middleware to call
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next) : Response
    {
        $response = $next($request, $response);
        $this->accessLogWriter->log($request, $response);
        return $response;
    }
}
