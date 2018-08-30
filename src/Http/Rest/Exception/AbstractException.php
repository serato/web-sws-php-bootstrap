<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use Serato\SwsApp\Exception\AbstractException as SwsAppException;

/**
 * Abstract Exception
 *
 * Exception class to be thrown during a REST API request.
 *
 * The exception should be caught and have its `message`, `code` and `http_response_code`
 * values formatted and returned to the client.
 */

abstract class AbstractException extends SwsAppException
{
}
