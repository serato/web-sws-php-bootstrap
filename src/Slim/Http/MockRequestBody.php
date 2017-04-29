<?php
namespace Serato\SwsApp\Slim\Http;

use Slim\Http\Body;

/**
 * Provides a means of mocking an HTTP request body.
 *
 * Heavily based on the Slim framework Slim\Http\RequestBody. The only change
 * is that it doesn't initially read request body from the php://input stream.
 */
class MockRequestBody extends Body
{
    /**
     * Create a new RequestBody.
     */
    public function __construct()
    {
        $stream = fopen('php://temp', 'w+');
        parent::__construct($stream);
    }
}
