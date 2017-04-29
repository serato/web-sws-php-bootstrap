<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractException;

/**
 * Abstract Permission Exception
 *
 * This class of exception is thrown in response to the client attempting an
 * operation for which it does not have permissions.
 *
 * Child classes return an error code between 2000 - 2999.
 */

abstract class AbstractPermissionException extends AbstractException
{
    /**
     * The HTTP response code associated with the client error.
     *
     * @var int
     */
    protected $http_response_code = 403;
}
