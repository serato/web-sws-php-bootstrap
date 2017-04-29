<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use RuntimeException;

/**
 * Abstract Exception
 *
 * Exception class to be thrown during a REST API request.
 *
 * The exception should be caught and have its `message`, `code` and `http_response_code`
 * values formatted and returned to the client.
 */

abstract class AbstractException extends RuntimeException
{
    /**
     * The HTTP response code associated with the client error.
     *
     * @var int
     */
    protected $http_response_code = 400;

    /**
     * Get the HTTP response code associated with the client error.
     *
     * @return int The HTTP response code.
     */
    public function getHttpResponseCode() : int
    {
        return $this->http_response_code;
    }
}
