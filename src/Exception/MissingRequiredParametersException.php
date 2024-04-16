<?php

namespace Serato\SwsApp\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractBadRequestException;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Missing Parameter Exception (Missing Parameter(s) should be passed)
 *
 */
class MissingRequiredParametersException extends AbstractBadRequestException
{
    /**
     * @var int
     */
    protected $code = 2013;

    /**
     * @var string
     */
    protected $message;

    /**
     * MissingRequiredParametersException constructor.
     *
     * @param Request|null $request
     */
    public function __construct(?string $message = null, Request $request = null, array $missingParams = [])
    {
        if (empty($message)) {
            $message = 'Missing required parameter(s) `' . implode('`, `', $missingParams) . '`';
        }

        parent::__construct($message, $request);
    }
}
