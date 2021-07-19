<?php

namespace Serato\SwsApp\Test\Slim\Middleware;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Middleware\MaintenanceMode;
use Serato\SwsApp\Slim\Middleware\EmptyWare;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;

/**
 * Unit tests for Serato\SwsApp\Slim\Middleware\MaintenanceMode
 */
class MaintenanceModeTest extends TestCase
{
    public function testMaintenanceModeDisabled()
    {
        $middleware = new MaintenanceMode(false);

        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            new Response(),
            new EmptyWare()
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMaintenanceModeEnabledJson()
    {
        $middleware = new MaintenanceMode(true);

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->addHeader('Accept', 'application/json')
            ),
            new Response(),
            new EmptyWare()
        );

        $json = json_decode((string)$response->getBody(), true);

        $this->assertEquals($json['message'], '503 Service Unavailable');
        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testMaintenanceModeEnabledHtml()
    {
        $middleware = new MaintenanceMode(true);

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->addHeader('Accept', 'text/html')
            ),
            new Response(),
            new EmptyWare()
        );

        $this->assertRegExp('/503 Service Unavailable/', (string)$response->getBody());
        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testMaintenanceModeEnabledXml1()
    {
        $middleware = new MaintenanceMode(true);

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->addHeader('Accept', 'text/xml')
            ),
            new Response(),
            new EmptyWare()
        );

        $xml = simplexml_load_string((string)$response->getBody());

        $this->assertEquals($xml->message, '503 Service Unavailable');
        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testMaintenanceModeEnabledXml2()
    {
        $middleware = new MaintenanceMode(true);

        $response = $middleware(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->addHeader('Accept', 'application/xml')
            ),
            new Response(),
            new EmptyWare()
        );

        $xml = simplexml_load_string((string)$response->getBody());

        $this->assertEquals($xml->message, '503 Service Unavailable');
        $this->assertEquals(503, $response->getStatusCode());
    }
}
