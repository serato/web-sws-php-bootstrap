<?php

namespace Serato\SwsApp\Test\Slim\Middleware;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Middleware\GeoIpLookup;
use Serato\SwsApp\Slim\Middleware\EmptyWare;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;

/**
 * Unit tests for Serato\SwsApp\Slim\Middleware\GeoIpLookup
 *
 * Note:
 * These tests are tagged into the `geoip-database` group and this group of tests is not
 * run by default. These tests require a GeoLite2 database. This database file is 50+ MB in size
 * and it doesn't make sense to store this file in the repo.
 *
 * So, to run these tests:
 *
 *  - Get a GeoLite2 database file named `GeoLite2-City.mmdb` and place it in the root directory of this repo.
 *  - Run the tests from a terminal with the `--group geoip-database` option.
 */
class GeoIpLookupTest extends TestCase
{
    /**
     * @dataProvider ipAddressAttributeProvider
     * @group geoip-database
     */
    public function testNoIpAddress($requestRemoteIp, $requestXForwardedIp, $requestAttributeIp)
    {
        $request = $this->getRequestViaMiddleware($requestRemoteIp, $requestXForwardedIp);

        $this->assertEquals(
            $requestAttributeIp,
            $request->getAttribute(GeoIpLookup::IP_ADDRESS)
        );
    }

    public function ipAddressAttributeProvider()
    {
        return [
            ['1.1.1.1', '', '1.1.1.1'],
            ['1.1.1.1', '2.2.2.2', '2.2.2.2'],
            ['1.1.1.1', null, '1.1.1.1'],
            [null, '1.1.1.1', '1.1.1.1'],
            [null, null, null]
        ];
    }

    /**
     * This test is a bit fragile because the source IP addresses could (in theory) result
     * in different geo IP records in the future. Somewhat unlikely thought.
     *
     * @dataProvider geoIpRecordProvider
     * @group geoip-database
    */
    public function testGeoIpRecord($requestRemoteIp, $requestXForwardedIp, array $lookupData)
    {
        $request = $this->getRequestViaMiddleware($requestRemoteIp, $requestXForwardedIp);
        $record = $request->getAttribute(GeoIpLookup::GEOIP_RECORD);
        // print_r([
        //     $record->city->name,
        //     $record->postal->code,
        //     $record->country->name,
        //     $record->country->isoCode,
        //     $record->continent->code
        // ]);
        $this->assertEquals($record->city->name, $lookupData[0]);
        $this->assertEquals($record->postal->code, $lookupData[1]);
        $this->assertEquals($record->country->name, $lookupData[2]);
        $this->assertEquals($record->country->isoCode, $lookupData[3]);
        $this->assertEquals($record->continent->code, $lookupData[4]);
    }

    public function geoIpRecordProvider()
    {
        return [
            ['', '', ['', '', '', '', '']],
            ['', '192.168.1.0', ['', '', '', '', '']],
            ['203.94.44.199', '', ['', '', 'New Zealand', 'NZ', 'OC']],
            ['', '64.0.0.0', ['', '', 'United States', 'US', 'NA']],
            ['', '90.76.106.209', ['Blagnac', '31700', 'France', 'FR','EU']],
            ['123.125.71.24', '', ['Beijing', '', 'China', 'CN', 'AS']],
            ['213.205.194.98', '', ['Woking', 'GU22', 'United Kingdom', 'GB', 'EU']],
            ['67.83.121.56', '', ['Port Washington', '11050', 'United States', 'US', 'NA']]
        ];
    }

    /**
     * Returns a Request instance that has been created by running a mock request
     * through the GeoIpLookup middleware.
     */
    private function getRequestViaMiddleware(mixed $remoteIp = null, mixed $xForwardedIp = null): Request
    {
        $middleware = new GeoIpLookup(realpath(__DIR__ . '/../../../GeoLite2-City.mmdb'));
        $emptyMiddleware = new EmptyWare();

        $env = EnvironmentBuilder::create();

        if ($remoteIp !== null) {
            $env = $env->setRemoteIpAddress($remoteIp);
        }
        if ($xForwardedIp !== null) {
            $env = $env->setXForwardedForIpAddress($xForwardedIp);
        }

        $middleware(
            Request::createFromEnvironmentBuilder($env),
            new Response(),
            $emptyMiddleware
        );

        return $emptyMiddleware->getRequestInterface();
    }
}
