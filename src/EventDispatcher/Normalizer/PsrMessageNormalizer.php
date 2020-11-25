<?php
namespace Serato\SwsApp\EventDispatcher\Normalizer;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Headers;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PsrMessageNormalizer
{
    /** @var Serializer */
    private $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([new ObjectNormalizer()]);
    }

    public function normalizePsrServerRequestInterface(ServerRequestInterface $request): array
    {
        $data = $this->normalizePsrMessageInterface($request);
        $data['headers'] = $this->normalizeHttpHeaders($data['headers']);
        return $data;
    }

    public function normalizePsrServerResponseInterface(ResponseInterface $response): array
    {
        $data = $this->normalizePsrMessageInterface($response);
        // normalizeKey($key)
        return $data;
    }

    /**
     * Normalize the collection of HTTP headers.
     *
     * Currently this is only required for requests objects. Something to do with how the
     * Slim request object uses the raw `HTTP_xxx` header name under the hood but the normalized
     * form when using getter method.
     *
     * @param array $headers
     * @return array
     */
    public function normalizeHttpHeaders(array $headers): array
    {
        # Only need to normalize header names. Use a method in the `Slim\Http\Headers` instance for this.
        $normalizedHeaders = [];
        $slimHeadersCollection = new Headers();
        foreach ($headers as $key => $value) {
            $normalizedHeaders[ucwords($slimHeadersCollection->normalizeKey($key), '-')] = $value;
        }
        return $normalizedHeaders;
    }

    /**
     * Returns an array representation of Psr\Http\Message\MessageInterface instance
     *
     * @param MessageInterface $message
     * @return array
     */
    public function normalizePsrMessageInterface(MessageInterface $message): array
    {
        return $this->serializer->normalize($message);
    }
}
