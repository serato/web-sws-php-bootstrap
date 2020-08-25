<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractUnsupportedMediaTypeException;

/**
 * Unsupported Content-Type Exception
 *
 * The exception is thrown when the request's indicated Content-Type is not supported.
 */
class UnsupportedContentTypeException extends AbstractUnsupportedMediaTypeException
{
    protected $code = 2003;
    protected $message = 'Invalid Request Content-Type. Refer to the API spec for more details.';
}
