<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractException;

/**
 * Abstract Unsupported Media Type Exception
 *
 * This exception is thrown when the server refuses to accept the request because the payload format is in an
 * unsupported format.
 *
 * The format problem might be due to the request's indicated Content-Type or Content-Encoding.
 */

abstract class AbstractUnsupportedMediaTypeException extends AbstractException
{
    /**
     * The HTTP response code associated with the client error.
     *
     * @var int
     */
    protected $http_response_code = 415;
}
