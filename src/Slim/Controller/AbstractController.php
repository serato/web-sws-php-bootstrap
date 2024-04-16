<?php

namespace Serato\SwsApp\Slim\Controller;

use Serato\SwsApp\Slim\Handlers\Error as ErrorHandler;
use Slim\Http\Response as SlimResponse;
use Serato\SwsApp\Exception\AbstractException as ClientException;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Abstract Controller
 *
 * Base controller class from which all controllers should extend.
 */
abstract class AbstractController
{
    private const DEFAULT_CACHE_CONTROL = 'no-cache';

    /**
     * PSR-3 Logger interface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /** @var array */
    private $ifNoneMatchRequestEtags = [];

    /** @var array */
    private $ifMatchRequestEtags = [];
    /**
     * Construct the controller
     *
     * @param LoggerInterface   $logger   A PSR-3 logger interface
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get the logger interface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Callable implementation. Controllers are registered to routes as
     * callables which dictates the method signature.
     *
     * @param  Request     $request            Request interface
     * @param  Response    $response           Response interface
     * @param  array       $args               Request args
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        # Require a client to specify a `Content-Type` header with a supported value for POST and PUT requests
        if (in_array(strtolower($request->getMethod()), ['post', 'put'])) {
            # Supported content types are limited by the specific implementation of the Request object
            # In this instance we use the Request object from the Slim framework.
            $supportContentTypes = [
                'application/json',
                'application/xml',
                'text/xml',
                'application/x-www-form-urlencoded'
            ];
            $contentType = $this->getRequestContentType($request);
            if ($contentType === null || !in_array($contentType, $supportContentTypes)) {
                // Error
            }
        }

        # Capture Etag values from Request headers
        $this->setIfNoneMatchEtags($request);
        $this->setIfMatchEtags($request);

        $response = $this->execute($request, $response, $args);

        # Return the response with a default `Cache-Control` header value if there's no other value set
        return $response->withHeader(
            'Cache-Control',
            $response->getHeaderLine('Cache-Control') === '' ?
                self::DEFAULT_CACHE_CONTROL :
                $response->getHeaderLine('Cache-Control')
        );
    }

    /**
     * Get request content type, if known.
     *
     * @todo Specify nullable string return type in PHP 7.1
     *
     * @param  Request     $request            Request interface
     * @return string|null The request media type, minus content-type params
     */
    public function getRequestContentType(Request $request)
    {
        $contentType = $request->getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', (string) $contentType);
            return strtolower($contentTypeParts[0]);
        }
        return null;
    }

    /**
     * Returns an array of Etags contained within an `If-None-Match` HTTP
     * request header.
     */
    protected function getIfNoneMatchEtags(): array
    {
        return $this->ifNoneMatchRequestEtags;
    }

    /**
     * Returns an array of Etags contained within an `If-Match` HTTP
     * request header.
     */
    protected function getIfMatchEtags(Request $request): array
    {
        return $this->ifMatchRequestEtags;
    }

    private function setIfNoneMatchEtags(Request $request): void
    {
        $this->ifNoneMatchRequestEtags = self::getRequestEtags($request, 'If-None-Match');
    }

    private function setIfMatchEtags(Request $request): void
    {
        $this->ifMatchRequestEtags = self::getRequestEtags($request, 'If-Match');
    }

    private static function getRequestEtags(Request $request, string $headerName): array
    {
        # This is actually a nasty hack that works around limitations of some other controller
        # behaviour. Currently we only want downstream functionality to match etag values for
        # GET and HEAD requests. In some cases that logic where this comparison happens does
        # not have a means to determine the HTTP method. So we can hack it here by only reading
        # out the header values for GET and HEAD requests.
        # For now this is OK. But we'll need to rethink the logic if we ever want this access these
        # header values for other HTTP methods.
        if (in_array(strtolower($request->getMethod()), ['get', 'head'])) {
            $rawHeaderValue = $request->getHeader($headerName);
            if (count($rawHeaderValue) > 0) {
                return array_map('trim', explode(',', $rawHeaderValue[0]));
            }
        }
        return [];
    }

    /**
     * Formats a string into a value suitible for use in an HTTP ETag header.
     */
    protected static function formatEtagValue(string $val, bool $weakValidation = true): string
    {
        return ($weakValidation ? 'W/' : '') . '"' . trim($val, '"') . '"';
    }

    /**
     * Simulate a controller invocation. For testing purposes only.
     *
     * @param Request       $request            Request interface
     * @param array         $uriArgs            Name/value pairs of dynamic URI parameters
     * @param bool          $catchClientErrors  When true catch client errors and invoke the error handler
     */
    public function mockInvoke(
        Request $request,
        array $uriArgs = [],
        bool $catchClientErrors = false
    ): Response {
        $response = new SlimResponse();

        if ($catchClientErrors) {
            try {
                return $this->__invoke($request, $response, $uriArgs);
            } catch (ClientException $e) {
                $error = new ErrorHandler('Controller MockInvoke', false, $this->getLogger());
                // Returns a Response object
                return $error($request, $response, $e);
            }
        } else {
            return $this->__invoke($request, $response, $uriArgs);
        }
    }

    /**
     * Execute the endpoint action
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param  Request     $request            Request interface
     * @param  Response    $response           Response interface
     * @param  array       $args               Request URI args
     */
    abstract protected function execute(
        Request $request,
        Response $response,
        array $args
    ): Response;
}
