<?php

namespace Serato\SwsApp\Exception;

use RuntimeException;

/**
 * Abstract Exception
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
