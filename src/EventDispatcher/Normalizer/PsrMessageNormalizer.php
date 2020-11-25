<?php
namespace Serato\SwsApp\EventDispatcher\Normalizer;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PsrMessageNormalizer
{
    public function normalizePsrServerRequestInterface(ServerRequestInterface $request): array
    {
        $callbacks = [
            'headers' => function ($innerObject) {
                return $this->normalizeHttpHeaders($innerObject);
            },
            'serverParams' => function ($innerObject) {
                return $this->normalizeServerParams($innerObject);
            },
            'attributes' => function ($innerObject) {
                return $this->normalizeRequestAttributes($innerObject);
            }
        ];

        $normalizer = $this->createObjectNormalizer($callbacks);
        $data = $this->normalizePsrMessageInterface(new Serializer([$normalizer]), $request);
        return $data;
    }

    public function normalizePsrServerResponseInterface(ResponseInterface $response): array
    {
        $normalizer = $this->createObjectNormalizer();
        $data = $this->normalizePsrMessageInterface(new Serializer([$normalizer]), $response);
        return $data;
    }

    /**
     * Normalizes the collection of HTTP headers.
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
        $normalizedHeaders = [];
        foreach ($headers as $key => $value) {
            # Normalize header names
            $key = strtr(strtolower($key), '_', '-');
            if (strpos($key, 'http-') === 0) {
                $key = substr($key, 5);
            }
            # Normalize values
            if ($key === 'content-type') {
                if (is_array($value) && count($value) === 1 && strpos($value[0], '; ') !== false) {
                    $value = explode('; ', $value[0]);
                }
            }
            $normalizedHeaders[ucwords($key, '-')] = $value;
        }
        return $normalizedHeaders;
    }

    /**
     * Normalizes the collection of server params.
     *
     * - Strips out redundant and/or sensitive data.
     * - Restructures data.
     *
     * @param array $params
     * @return array
     */
    public function normalizeServerParams(array $params): array
    {
        $whitelist = [
            'USER',
            'SERVER_NAME',
            'SERVER_PORT',
            'SERVER_ADDR',
            'REMOTE_PORT',
            'FCGI_ROLE',
            'SERVER_SOFTWARE',
            'SERVER_PROTOCOL',
            'GATEWAY_INTERFACE',
            'REMOTE_ADDR',
            'REQUEST_SCHEME',
            'REQUEST_TIME_FLOAT',
            'REQUEST_TIME'
        ];
        foreach ($params as $key => $value) {
            if (!in_array($key, $whitelist)) {
                unset($params[$key]);
            }
        }
        return $params;
    }

    /**
     * Normalizes the collection of request attributes.
     *
     * These are attributes added to the request object by the web application
     * (typically via middleware).
     *
     * @param array $attributes
     * @return array
     */
    public function normalizeRequestAttributes(array $attributes): array
    {
        if (isset($attributes['geoIpRecord']) && is_a($attributes['geoIpRecord'], 'GeoIp2\Model\City')) {
            $attributes['geoIpRecord'] = $attributes['geoIpRecord']->jsonSerialize();
        }
        return $attributes;
    }

    /**
     * Returns an array representation of Psr\Http\Message\MessageInterface instance
     *
     * @param Serializer $serializer
     * @param MessageInterface $message
     * @return array
     */
    public function normalizePsrMessageInterface(Serializer $serializer, MessageInterface $message): array
    {
        return $serializer->normalize($message);
    }

    private function createObjectNormalizer(array $callbacks = [], array $ignoredAttributes = []): ObjectNormalizer
    {
        $defaultContext = [
            AbstractNormalizer::IGNORED_ATTRIBUTES => array_merge(
                [
                    'get',
                    'post',
                    'put',
                    'patch',
                    'delete',
                    'head',
                    'options',
                    'contentType',
                    'mediaType',
                    'mediaTypeParams',
                    'params'
                ],
                $ignoredAttributes
            )
        ];
        if (count($callbacks) > 0) {
            $defaultContext[AbstractNormalizer::CALLBACKS] = $callbacks;
        }
        return new ObjectNormalizer(
            null,
            null,
            null,
            null,
            null,
            null,
            $defaultContext
        );
    }
}
