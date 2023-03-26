<?php

namespace Serato\SwsApp\Test\Slim\Middleware;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Middleware\EmptyWare;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;
use Serato\SwsApp\Slim\Middleware\FrameAncestorsCspHeader;


/**
 * Class FrameAncestorsCspHeaderTest
 * @package Serato\SwsApp\Test\Slim\Middleware
 */
class FrameAncestorsCspHeaderTest extends TestCase
{
    private const CSP_VALUE = "frame-ancestors 'none'";

    /**
     * Create an Request object and Response object.
     *
     * Execute the middleware and get the returned Response object.
     *
     * Confirm that the Response object has:
     *
     * - A `Content-Security-Policy` header whose value is always "frame-ancestors 'none';".
     */
    public function testWithOriginRequestHeader()
    {
        $middleware = new FrameAncestorsCspHeader();

        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            new Response(),
            new EmptyWare()
        );

        $this->assertNotFalse(
            strpos($response->getHeaderLine('Content-Security-Policy'), self::CSP_VALUE)
        );
    }

    /**
     * Create a Request object.
     * Create a Response object with a `Content-Security-Policy` header that contains an initial value.
     *
     * Execute the middleware and get the returned Response object.
     *
     * Confirm that the Response object has:
     *
     * - A `Content-Security-Policy` header whose value contains both the initial value and "frame-ancestors 'none'".
     */
    public function testWithOriginRequestHeaderAndExistingCspResponseHeader()
    {
        $initialValue = "default-src 'none';";

        $middleware = new FrameAncestorsCspHeader();

        $response = new Response();
        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            $response->withHeader('Content-Security-Policy', $initialValue),
            new EmptyWare()
        );

        $this->assertNotFalse(strpos($response->getHeaderLine('Content-Security-Policy'), $initialValue));
        $this->assertNotFalse(
            strpos($response->getHeaderLine('Content-Security-Policy'), self::CSP_VALUE)
        );
    }
}
