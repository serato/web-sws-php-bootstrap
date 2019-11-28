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
    private const DEFAULT_CACHE_CONTROL = 'no-store';

    /**
     * PSR-3 Logger interface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /** @var string */
    private $etag;

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
     *
     * @return LoggerInterface
     */
    public function getLogger() : LoggerInterface
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
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args) : Response
    {
        // Require a client to specify a `Content-Type` header with a supported value for POST and PUT requests
        if (in_array(strtolower($request->getMethod()), ['post', 'put'])) {
            // Supported content types are limited by the specific implementation of the Request object
            // In this instance we use the Request object from the Slim framework.
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
        $response = $this->execute($request, $response, $args);
        if ($this->getEtag() !== null) {
            $response = $response->withHeader('Etag', $this->getEtag());
        }
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
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            return strtolower($contentTypeParts[0]);
        }
        return null;
    }

    /**
     * Returns an array of Etags contained within an `If-None-Match` HTTP
     * request header.
     *
     * @param Request $request
     * @return array
     */
    public static function getIfNoneMatchEtags(Request $request): array
    {
        return self::getRequestEtags($request->getHeader('If-None-Match'));
    }

    /**
     * Returns an array of Etags contained within an `If-Match` HTTP
     * request header.
     *
     * @param Request $request
     * @return array
     */
    public static function getIfMatchEtags(Request $request): array
    {
        return self::getRequestEtags($request->getHeader('If-Match'));
    }

    private static function getRequestEtags(array $rawHeaderValue): array
    {
        if (count($rawHeaderValue) > 0) {
            return array_map('trim', explode(',', $rawHeaderValue[0]));
        }
        return [];
    }

    /**
     * Sets an etag value. When provided, the value will returned in the HTTP
     * response within the `Etag` header.
     *
     * @param string $etag
     * @return void
     */
    public function setEtag(string $etag, bool $weakValidation = true): void
    {
        $this->etag = ($weakValidation ? 'W/' : '') . '"' . trim($etag, '"') . '"';
    }

    /**
     * Returns the etag value.
     *
     * @return string|null
     */
    public function getEtag(): ?string
    {
        return $this->etag;
    }

    /**
     * Simulate a controller invocation. For testing purposes only.
     *
     * @param Request       $request            Request interface
     * @param array         $uriArgs            Name/value pairs of dynamic URI parameters
     * @param bool          $catchClientErrors  When true catch client errors and invoke the error handler
     *
     * @return Response
     */
    public function mockInvoke(
        Request $request,
        array $uriArgs = [],
        bool $catchClientErrors = false
    ) : Response {
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
     *
     * @return Response
     */
    abstract protected function execute(
        Request $request,
        Response $response,
        array $args
    ) : Response;
}
