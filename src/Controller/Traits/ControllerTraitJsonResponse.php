<?php
namespace Serato\SwsApp\Controller\Traits;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Adds functionality to a controller to allow for the constructing and outputting
 * of a JSON response.
 */
trait ControllerTraitJsonResponse
{
    /**
     * Response body to be JSON encoded and sent to the client
     *
     * @var array
     */
    private $jsonResponseBody = [];

    /**
     * Append content to response body
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param  array $content  Content to append
     */
    protected function appendJsonBody(array $content)
    {
        foreach ($content as $k => $v) {
            if (is_int($k)) {
                $this->jsonResponseBody[] = $v;
            } else {
                $this->jsonResponseBody[$k] = $v;
            }
        }
    }

    /**
     * Write final content to response body. JSON-encodes the response body and
     * sets a `Content-type application/json` response header.
     *
     * @param  Response    $response           Response interface
     * @param  int         $httpResponseCode   HTTP response code
     * @return Response
     */
    protected function writeJsonBody(Response $response, int $httpResponseCode = 200) : Response
    {
        return $response
                ->withStatus($httpResponseCode)
                ->withHeader('Content-type', 'application/json')
                ->write(json_encode(
                    $this->jsonResponseBody,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                ));
    }
}
