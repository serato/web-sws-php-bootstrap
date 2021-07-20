<?php

namespace Serato\SwsApp\Slim\Controller\Traits;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Adds functionality to a `Serato\SwsApp\Slim\Controller\AbstractController` instance
 * to allow for the constructing and outputting of a JSON response.
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
     * @return Response
     */
    protected function writeJsonBody(Response $response): Response
    {
        $response = $response->withHeader('Content-type', 'application/json');

        $content = json_encode(
            $this->jsonResponseBody,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        $etag = self::formatEtagValue(md5($content));

        if (in_array($etag, $this->getIfNoneMatchEtags())) {
            $response = $response
                ->withStatus(304)
                ->withHeader('Etag', $etag);
        } else {
            $response = $response
                ->withHeader('Etag', $etag)
                ->write($content);
        }

        return $response;
    }
}
