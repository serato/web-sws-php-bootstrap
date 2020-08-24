<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractException;

/**
 * Unsupported Content-Type Exception
 *
 * This exception is thrown when the server refuses to accept the request because the payload format is in an
 * unsupported format.
 *
 * The format problem would be due to the request's indicated Content-Type.
 */

class UnsupportedContentTypeException extends AbstractException
{
    protected $http_response_code = 415;
    protected $code = 2003;
    protected $message = 'Invalid Request Content-Type. Refer to the API spec for more details.';
}
