<?php

namespace Serato\SwsApp\Test\Slim\Middleware;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Middleware\AccessControlAllowOrigin;
use Serato\SwsApp\Slim\Middleware\EmptyWare;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;

/**
 * Unit tests for Serato\SwsApp\Slim\Middleware\AccessControlAllowOrigin
 */
class AccessControlAllowOriginTest extends TestCase
{
    /**
     * Create a Request object with no value provided for the `Origin` header.
     * Create an empty Response object.
     *
     * Execute the middleware and get the returned Response object.
     *
     * Confirm that the Response object has:
     *
     * - Has no `Access-Control-Allow-Origin` header.
     */
    public function testNoOriginRequestHeader()
    {
        $middleware = new AccessControlAllowOrigin();

        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            new Response(),
            new EmptyWare()
        );

        $this->assertEquals('', $response->getHeaderLine('Access-Control-Allow-Origin'));
    }

    /**
     * Create a Request object with a value provided for the `Origin` header.
     * Create an empty Response object.
     *
     * Execute the middleware and get the returned Response object.
     *
     * Confirm that the Response object has:
     *
     * - An `Access-Control-Allow-Origin` header whose value is the same as the `Origin` request header.
     * - A `Vary` header whose value is always "Origin".
     */
    public function testWithOriginRequestHeader()
    {
        $origin = 'https://my.origin.net';

        $middleware = new AccessControlAllowOrigin();

        $request = Request::createFromEnvironmentBuilder(EnvironmentBuilder::create())
                        ->withHeader('Origin', $origin);
        $response = $middleware(
            $request,
            new Response(),
            new EmptyWare()
        );

        $this->assertEquals($origin, $response->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals('Origin', $response->getHeaderLine('Vary'));
    }

    /**
     * Create a Request object with a value provided for the `Origin` header.
     * Create a Response object with a `Vary` header that contains an initial value.
     *
     * Execute the middleware and get the returned Response object.
     *
     * Confirm that the Response object has:
     *
     * - An `Access-Control-Allow-Origin` header whose value is the same as the `Origin` request header.
     * - A `Vary` header whose value contains both the initial value and "Origin".
     */
    public function testWithOriginRequestHeaderAndExistingVaryResponseHeader()
    {
        $varyInitialValue = 'Content-Type';
        $origin = 'https://my.origin.net';

        $middleware = new AccessControlAllowOrigin();

        $request = Request::createFromEnvironmentBuilder(EnvironmentBuilder::create())
                        ->withHeader('Origin', $origin);

        $response = new Response();
        $response = $middleware(
            $request,
            $response->withHeader('Vary', $varyInitialValue),
            new EmptyWare()
        );

        $this->assertEquals($origin, $response->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertTrue(in_array('Origin', $response->getHeader('Vary')));
        $this->assertTrue(in_array($varyInitialValue, $response->getHeader('Vary')));
    }
}
