<?php
namespace Serato\SwsApp\EventDispatcher\Normalizer;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PsrMessageNormalizer
{
    // The maximum size of the Body of message before being omitted
    private const MAX_BODY_SIZE = 1024 * 1024;

    // A list of HTTP headers to exclude
    private const HTTP_HEADER_BLACKLIST = [
        // This is weird PHP thing where it puts the password section of a `Basic` auth header into
        // this header (there's also `Php-Auth-User`, but we keep that)
        'Php-Auth-Pw'
    ];

    public function normalizePsrServerRequestInterface(ServerRequestInterface $request): array
    {
        $callbacks = [
            'uri' => function ($innerObject) {
                return $this->normalizeUri($innerObject);
            },
            'headers' => function ($innerObject) {
                return $this->normalizeHttpHeaders($innerObject);
            },
            'serverParams' => function ($innerObject) {
                return $this->normalizeServerParams($innerObject);
            },
            'attributes' => function ($innerObject) {
                return $this->normalizeRequestAttributes($innerObject);
            },
            'body' => function (StreamInterface $body, ServerRequestInterface $httpMessage) {
                # Determine content type from the `Content-Type` request header.
                # It may not be set.
                $contentType = null;
                $contentTypeHeader = $this->normalizeContentTypeHeader($httpMessage->getHeader('Content-Type'));
                if (count($contentTypeHeader) > 0) {
                    $contentType = $contentTypeHeader[0];
                }

                # Determine content length from the `Content-Length` request header
                # It may not be set. Assume that this a request with no body (eg a GET request)
                $contentLength = 0;
                $contentLengthHeader = $httpMessage->getHeader('Content-Length');
                if (count($contentLengthHeader) > 0 && is_numeric($contentLengthHeader[0])) {
                    $contentLength = (int)$contentLengthHeader[0];
                }

                return $this->normalizePsrMessageBody(
                    $body,
                    $contentType,
                    $contentLength,
                    $httpMessage->getParsedBody()
                );
            }
        ];

        $normalizer = $this->createObjectNormalizer($callbacks);
        $data = $this->normalizePsrMessageInterface(new Serializer([$normalizer]), $request);
        return $data;
    }

    public function normalizePsrServerResponseInterface(ResponseInterface $response): array
    {
        $callbacks = [
            'body' => function (StreamInterface $body, ResponseInterface $httpMessage) {
                # Determine content type from the `Content-Type` request header.
                # It may not be set.
                $contentType = null;
                $contentTypeHeader = $httpMessage->getHeader('Content-Type');
                if (count($contentTypeHeader) > 0) {
                    $contentType = $contentTypeHeader[0];
                }

                # Determine content length from the StreamInterface
                $contentLength = $body->getSize();

                return $this->normalizePsrMessageBody($body, $contentType, $contentLength);
            }
        ];

        $normalizer = $this->createObjectNormalizer($callbacks);
        $data = $this->normalizePsrMessageInterface(new Serializer([$normalizer]), $response);
        return $data;
    }

    /**
     * Normalizes a `Psr\Http\Message\UriInterface` instance
     *
     * @param UriInterface $uri
     * @return array
     */
    public function normalizeUri(UriInterface $uri): array
    {
        $data = [];
        $serializer = new Serializer([new ObjectNormalizer]);
        $data = $serializer->normalize($uri);
        if (isset($data['userInfo']) && $data['userInfo'] !== '') {
            # The `authority`, `userInfo` and `baseUrl` keys will all contain user name and password in clear text
            # if the request uses Basic auth. We need to remove the password component.
            # The format is '<user name>:<password>'.
            foreach ($data as $k => $v) {
                if (in_array($k, ['authority', 'userInfo', 'baseUrl'])) {
                    $data[$k] = $this->removeUriPassword($v);
                } else {
                    $data[$k] = $v;
                }
            }
        }
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
            // Normalize header names
            $key = strtr(strtolower($key), '_', '-');
            if (strpos($key, 'http-') === 0) {
                $key = substr($key, 5);
            }
            
            // Normalize values
            
            # `Content-Type`
            # Can have ';' a delimiter when value is 'form/multipart'.
            # The other side of the delimiter is the multi-part MIME separator
            # Slim ordinarily deals with headers like this correctly, but it treats
            # Requests with a Content-Type = 'form/multipart' a bit differently under the hood.
            if (strtolower($key) === 'content-type') {
                $value = $this->normalizeContentTypeHeader($value);
            }

            # `Authorization`
            # We want to strip out the value here because it's sensitive.
            # Basic auth values are user name/password in clear text.
            # Bearer token values are less sensitive because they are a short JWT access token.
            # But there's no value in keeping them, so remove too
            if (strtolower($key) === 'authorization') {
                if (is_array($value)) {
                    $value = $value[0];
                }
                if (stripos($value, 'basic ') === 0) {
                    $value = ['Basic [APP ID + SECRET]'];
                } elseif (stripos($value, 'bearer ') === 0) {
                    $value = ['Bearer [JWT ACCESS TOKEN]'];
                }
            }

            if (!in_array(
                strtolower($key),
                array_map('strtolower', self::HTTP_HEADER_BLACKLIST)
            )) {
                $normalizedHeaders[ucwords($key, '-')] = $value;
            }
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
        # Slim adds route info to the Request object.
        # It doesn't add anything useful in my opinion, and would require writing a custom normalizer.
        unset($attributes['route'], $attributes['routeInfo']);
        return $attributes;
    }

    /**
     * Normalizes a PSR message body
     *
     * @param StreamInterface $body
     * @param string|null $contentType
     * @param int $contentLength
     * @param null|array|object $parsedBody
     * @return null|array
     */
    public function normalizePsrMessageBody(
        StreamInterface $requestBodyStream,
        ?string $contentType,
        int $contentLength,
        $parsedBody = null
    ): ?array {
        $body = null;

        # Maybe there is no body (eg GET request, 201 response etc)
        if ($contentLength === 0) {
            return $body;
        }

        $body['contentLength'] = $contentLength;
        if ($contentType !== null) {
            $body['contentType'] = $contentType;
        }

        if ($contentLength >self::MAX_BODY_SIZE) {
            $body['notice'] = 'Body content omitted. Content length of ' . $contentLength . ' bytes ' .
                                'exceeds maximum allowable length of ' . self::MAX_BODY_SIZE . ' bytes.';
        } else {
            if ($parsedBody === null) {
                # Is the content type `multipart/form-data`?
                # See: https://www.php.net/manual/en/wrappers.php.php
                # We HAVE to use $_POST vars. These are passed via $parsedBody.
                if ($contentType === 'multipart/form-data') {
                    # If we have no $parsedBody, no use in proceeding
                    return $body;
                }
            } else {
                $body['parsed'] = $parsedBody;
            }
            $requestBodyStream->rewind();
            $raw = $requestBodyStream->getContents();
            if ($raw !== '') {
                $body['raw'] = $raw;
            }
        }
        return $body;
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
            ),
            AbstractNormalizer::CALLBACKS => $callbacks
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

    private function normalizeContentTypeHeader($value): array
    {
        if (is_array($value) && count($value) === 1 && strpos($value[0], '; ') !== false) {
            return explode('; ', $value[0]);
        }
        return $value;
    }

    private function removeUriPassword(string $uri): string
    {
        $uriRemoved = '';
        $bits = explode('://', $uri);
        if (count($bits) === 1) {
            $bits[1] = $bits[0];
            $bits[0] = '';
        } elseif (count($bits) === 2) {
            $bits[0] = $bits[0] . '://';
        }
        if (strpos($bits[1], '@') !== false) {
            $uriRemoved = $bits[0] . preg_replace('/:(.+)@/', ':PASSWORD_REMOVED@', $bits[1]);
        } else {
            $uriRemoved = $bits[0] . preg_replace('/:(.+)/', ':PASSWORD_REMOVED', $bits[1]);
        }
        return $uriRemoved;
    }
}
