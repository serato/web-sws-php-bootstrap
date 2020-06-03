<?php
namespace Serato\SwsApp\Slim\Handlers;

use Serato\SwsApp\AccessLogWriter;
use Slim\Http\Body;
use Slim\Handlers\Error as SlimError;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface as Logger;
use Exception;
use UnexpectedValueException;

/**
 * Application Error Handler
 *
 * Extends the base Slim Error handler by:
 *
 * 1. Using a provided logger interface to write error messages to.
 * 2. Better handle Client Exceptions (ie. format the response in a specific way
 *    and don't log these errors)
 */
class Error extends SlimError
{
    public const ERROR_CODE_HTTP_HEADER = 'X-Serato-ErrorCode';
    public const ERROR_MESSAGE_HTTP_HEADER = 'X-Serato-ErrorMessage';
    const BASE_CLASS = '\Serato\SwsApp\Exception\AbstractException';
    public const GENRIC_ERROR_MESSAGE = 'Oops, something went wrong, please try again.';

    /**
     * Application name
     *
     * @var string
     */
    protected $applicationName;

    /**
     * PSR-3 logger interface
     *
     * @var Logger
     */
    protected $logger;

    /**
     * HTTP request method
     *
     * @var string
     */
    protected $requestMethod;

    /**
     * HTTP request URI path
     *
     * @var string
     */
    protected $requestPath;

    /**
     * HTTP request URI query string
     *
     * @var string
     */
    protected $requestQueryString;

    /**
     * Language preference as specified by the request
     *
     * @var string
     */
    protected $language = 'en';

    /* @var AccessLogWriter */
    private $accessLogWriter;

    /**
     * Construct the error handler
     *
     * @param string    $applicationName        Human readable name of application
     * @param bool      $displayErrorDetails    Display full error message including stack trace
     * @param Logger    $logger                 PSR-3 logger interface
     */
    public function __construct(string $applicationName, bool $displayErrorDetails, Logger $logger)
    {
        parent::__construct($displayErrorDetails);
        $this->logger = $logger;
        $this->applicationName = $applicationName;
        $this->accessLogWriter = new AccessLogWriter($logger);
    }

    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Exception             $exception The caught Exception object
     *
     * @return ResponseInterface
     * @throws UnexpectedValueException
     */
    public function __invoke(Request $request, Response $response, Exception $exception): Response
    {
        $this->requestMethod        = $request->getMethod();
        $this->requestPath          = $request->getUri()->getPath();
        $this->requestQueryString   = $request->getUri()->getQuery();

        $http_response_code = 500;
        if (is_a($exception, self::BASE_CLASS)) {
            $http_response_code = $exception->getHttpResponseCode();
        }

        $contentType = $this->determineContentType($request);

        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonErrorMessage($exception);
                break;
            case 'text/xml':
            case 'application/xml':
                $output = $this->renderXmlErrorMessage($exception);
                break;
            case 'text/html':
                $output = $this->renderHtmlErrorMessage($exception);
                break;
            default:
                throw new UnexpectedValueException('Cannot render unknown content type ' . $contentType);
        }

        if (!is_a($exception, self::BASE_CLASS)) {
            $this->writeToErrorLog($exception);
        }

        $body = new Body(fopen('php://temp', 'r+'));

        $body->write($output);

        $response = $response
            ->withStatus($http_response_code)
            ->withHeader('Content-type', $contentType)
            ->withHeader(self::ERROR_CODE_HTTP_HEADER, $exception->getCode())
            ->withHeader(self::ERROR_MESSAGE_HTTP_HEADER, str_replace("\n", ' ', $exception->getMessage()))
            ->withBody($body);

        if (is_a($exception, self::BASE_CLASS)) {
            $this->accessLogWriter->log($exception->getRequest(), $response);
        }

        return $response;
    }

    /**
     * Render HTML error page
     *
     * @param  \Exception $exception
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(\Exception $exception)
    {
        $title = $this->applicationName;
        $html = '';

        if (is_a($exception, self::BASE_CLASS)) {
            switch ($exception->getHttpResponseCode()) {
                case 403:
                    $html = '<h2>403 Forbidden</h2>';
                    break;
                case 401:
                    $html = '<h2>401 Unauthorized</h2>';
                    break;
                case 409:
                    $html = '<h2>409 Conflict</h2>';
                    break;
                case 400:
                    $html = '<h2>400 Bad Request</h2>';
                    break;
            }
        } elseif (!$this->displayErrorDetails) {
            $html .= '<p>' . self::GENRIC_ERROR_MESSAGE . '</p>';
        }

        if ($this->displayErrorDetails) {
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlException($exception);

            while ($exception = $exception->getPrevious()) {
                $html .= '<h2>Previous exception</h2>';
                $html .= $this->renderHtmlException($exception);
            }
        }

        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}h1{margin:0;font-size:32px;font-weight:normal;line-height:40px;}strong{" .
            "display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>",
            $title,
            $title,
            $html
        );

        return $output;
    }

    /**
     * Render JSON error
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderJsonErrorMessage(\Exception $exception): string
    {
        $msg = self::GENRIC_ERROR_MESSAGE;

        $error = ['message' => $msg];

        if (is_a($exception, self::BASE_CLASS)) {
            $error = [
                'error' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ];
        } elseif ($this->displayErrorDetails) {
            $json = json_decode(parent::renderJsonErrorMessage($exception), true);
            $json['message'] = $msg;
            return json_encode($json, JSON_PRETTY_PRINT);
        }

        return json_encode($error, JSON_PRETTY_PRINT);
    }

    /**
     * Write to the error log if displayErrorDetails is false
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param \Exception|\Throwable $throwable
     *
     * @return void
     */
    protected function writeToErrorLog($throwable)
    {
        $error = $this->renderThrowableAsArray($throwable);
        while ($throwable = $throwable->getPrevious()) {
            if (isset($error['previous'])) {
                $error['previous'] = [];
            }
            $error['previous'][] = $this->renderThrowableAsArray($throwable);
        }

        $this->logger->critical(
            'Slim Application Unhandled Exception',
            array_merge(
                $error,
                [
                    'request_method'        => $this->requestMethod,
                    'request_path'          => $this->requestPath,
                    'request_query_string'  => $this->requestQueryString
                ]
            )
        );
    }

    /**
     * @param \Exception|\Throwable $throwable
     *
     * @return array
     */
    private function renderThrowableAsArray($throwable): array
    {
        $error = [];
        $error['type'] = get_class($throwable);
        if ($code = $throwable->getCode()) {
            $error['code'] = $code;
        }
        if ($message = $throwable->getMessage()) {
            $error['message'] = htmlentities($message);
        }
        if ($file = $throwable->getFile()) {
            $error['file'] = $file;
        }
        if ($line = $throwable->getLine()) {
            $error['line'] = $line;
        }
        if ($trace = $throwable->getTraceAsString()) {
            $error['trace'] = explode("\n", $throwable->getTraceAsString());
        }
        return $error;
    }
}
