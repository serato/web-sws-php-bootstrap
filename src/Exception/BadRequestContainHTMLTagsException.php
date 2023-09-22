<?php

namespace Serato\SwsApp\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractBadRequestException;

/**
 * Class BadRequestContainHTMLTagsException
 * The request param is invalid with html tags
 * @package App\Exception\RequestValidation
 */
class BadRequestContainHTMLTagsException extends AbstractBadRequestException
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
