<?php

namespace Serato\SwsApp\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractBadRequestException;

/**
 * Class InvalidRequestParametersException
 * @package App\Exception\RequestValidation
 */
class InvalidRequestParametersException extends AbstractBadRequestException
{
    /**
     * @var int
     */
    protected $code = 4032;

    /**
     * @var string
     */
    protected $message;
}
