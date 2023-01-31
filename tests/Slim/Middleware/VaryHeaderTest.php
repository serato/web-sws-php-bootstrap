<?php

namespace Serato\SwsApp\Test\Slim\Middleware;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Middleware\EmptyWare;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Serato\SwsApp\Slim\Middleware\VaryHeader;
use Slim\Http\Response;

/**
 * Unit tests for Serato\SwsApp\Slim\Middleware\VaryHeader
 */
class VaryHeaderTest extends TestCase
{
    /**
     * Create an Request object and Response object.
     *
     * Execute the middleware and get the returned Response object.
     *
     * Confirm that the Response object has:
     *
     * - A `Vary` header whose value is always "Origin, Accept-Encoding".
     */
    public function testWithOriginRequestHeader()
    {
        $middleware = new VaryHeader();

        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            new Response(),
            new EmptyWare()
        );

        $this->assertNotFalse(strpos($response->getHeaderLine('Vary'), 'Origin'));
        $this->assertNotFalse(strpos($response->getHeaderLine('Vary'), 'Accept-Encoding'));
    }

    /**
     * Create a Request object.
     * Create a Response object with a `Vary` header that contains an initial value.
     *
     * Execute the middleware and get the returned Response object.
     *
     * Confirm that the Response object has:
     *
     * - A `Vary` header whose value contains both the initial value and "Origin, Accept-Encoding ".
     */
    public function testWithOriginRequestHeaderAndExistingVaryResponseHeader()
    {
        $varyInitialValue = 'Content-Type';

        $middleware = new VaryHeader();

        $response = new Response();
        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            $response->withHeader('Vary', $varyInitialValue),
            new EmptyWare()
        );

        $this->assertNotFalse(strpos($response->getHeaderLine('Vary'), $varyInitialValue));
        $this->assertNotFalse(strpos($response->getHeaderLine('Vary'), 'Origin'));
        $this->assertNotFalse(strpos($response->getHeaderLine('Vary'), 'Accept-Encoding'));
    }
}
