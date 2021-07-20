<?php

namespace Serato\SwsApp\Slim\Middleware;

use Slim\Http\Body;
use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GeoIp2\Database\Reader;
use GeoIp2\Model\City;
use Exception;

/**
 * GeoIpLookup Middleware
 *
 * A middleware that exposes the results of a geo IP lookup to the RequestInterface.
 *
 * Two attributes are added the RequestInterface:
 *
 * 1. `ipAddress`       The source IP of the request. If the IP address can not be determined
 *                      the value will be NULL.
 * 2. `geoIpRecord`     A `GeoIp2\Model\City` record of the IP address lookup.
 */
class GeoIpLookup extends AbstractHandler
{
    public const IP_ADDRESS = 'ipAddress';
    public const GEOIP_RECORD = 'geoIpRecord';

    /* @var string */
    private $geoLiteDbPath;

    /**
     * Constructs the object
     *
     * @param string $geoLiteDbPath     Path to a GeoLite2 database file
     */
    public function __construct(string $geoLiteDbPath)
    {
        $this->geoLiteDbPath = $geoLiteDbPath;
    }

    /**
     * Invoke the middleware
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param Callable               $next      The next middleware to call
     *
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $ip = $request->getServerParam('HTTP_X_FORWARDED_FOR', '');
        if ($ip === '') {
            $ip = $request->getServerParam('REMOTE_ADDR', '');
        }

        $request = $request
                        ->withAttribute(self::IP_ADDRESS, ($ip === '' ? null : $ip))
                        ->withAttribute(self::GEOIP_RECORD, $this->getGeoIpCityRecord($ip));

        return $next($request, $response);
    }

    /**
     * @param string    $ipAddress     IP address
     * @return City
     */
    private function getGeoIpCityRecord(string $ipAddress): City
    {
        $reader = new Reader($this->geoLiteDbPath);

        try {
            return $reader->city($ipAddress);
        } catch (Exception $e) {
            return new City([]);
        }
    }
}
