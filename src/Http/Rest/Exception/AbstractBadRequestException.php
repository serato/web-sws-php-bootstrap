<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractException;

/**
 * Abstract Bad Request Exception
 *
 * This class of exception is thrown in response to a client providing
 * incomplete and/or invalid request data.
 *
 * Exceptions of this class result in a `400 Bad Request` HTTP response being
 * to the client.
 *
 * Child classes return an error code between 1000 - 1999.
 */

abstract class AbstractBadRequestException extends AbstractException
{
}
