<?php

namespace Serato\SwsApp\EventDispatcher\Event;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * SwsHttpRequest
 *
 * An event dispatched upon completion of an HTTP request to an SWS web service.
 *
 * Properties:
 *
 * - 'request': A Psr\Http\Message\ServerRequestInterface instance representing the HTTP request
 * - 'response': A Psr\Http\Message\ResponseInterface instanc respresenting the HTTP response
 */
class SwsHttpRequest extends AbstractEvent
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    protected function getArrayKeys(): array
    {
        return ['request', 'response'];
    }
}
