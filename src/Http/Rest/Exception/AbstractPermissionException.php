<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use RuntimeException;

/**
 * Abstract Permission Exception
 *
 * This class of exception is thrown in response to the client attempting an
 * operation for which it does not have permissions.
 *
 * Exceptions of this class result in a `403 Forbidden` HTTP response being
 * to the client.
 *
 * Child classes return an error code between 2000 - 2999.
 */

abstract class AbstractPermissionException extends RuntimeException
{
    /**
     * The HTTP response code associated with the client error.
     *
     * @var int
     */
    protected $http_response_code = 403;

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
