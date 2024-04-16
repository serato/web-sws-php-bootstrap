<?php

namespace Serato\SwsApp\Exception;

use RuntimeException;
use Psr\Http\Message\RequestInterface as Request;

/**
 * Abstract Exception
 */

abstract class AbstractException extends RuntimeException
{
    public function __construct(?string $message = null, private readonly ?Request $request = null)
    {
        if ($message !== null && $message !== '') {
            parent::__construct($message);
        }
    }

    /**
     * The HTTP response code associated with the client error.
     *
     * @var int
     */
    protected $http_response_code = 400;

    /**
     * Get the HTTP response code associated with the client error.
     *
     * @return int The HTTP response code.
     */
    public function getHttpResponseCode(): int
    {
        return $this->http_response_code;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
