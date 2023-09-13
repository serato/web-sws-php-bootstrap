<?php

namespace Serato\SwsApp\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractBadRequestException;

/**
 * Class InvalidRequestParametersException
 * The request param is invalid with html tags
 * @package App\Exception\RequestValidation
 */
class InvalidTagRequestParametersException extends AbstractBadRequestException
{
    /**
     * @var int
     */
    protected $code = 5023;

    /**
     * @var string
     */
    protected $message;
}
