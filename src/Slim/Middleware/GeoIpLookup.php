<?php

namespace Serato\SwsApp\Slim\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
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
class GeoIpLookup
{
    public const IP_ADDRESS = 'ipAddress';
    public const GEOIP_RECORD = 'geoIpRecord';

    /* @var string */
    private $realIpHeader;

    /**
     * Constructs the object
     *
     * @param string $geoLiteDbPath     Path to a GeoLite2 database file
     * @param string $realIpHeader      Name of the HTTP header that contains the client's real IP address
     */
    public function __construct(private readonly string $geoLiteDbPath, string $realIpHeader = '')
    {
        $this->realIpHeader = $realIpHeader === '' ? 'X-Forwarded-For' : $realIpHeader;
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
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if ($this->realIpHeader !== '' && $request->hasHeader($this->realIpHeader)) {
            $header = $request->getHeaderLine($this->realIpHeader);

            // Some client IP headers, like 'CloudFront-Viewer-Address', include a port number
            $ip = explode(':', $header)[0];
        } else {
            $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '';
        }

        $request = $request
                        ->withAttribute(self::IP_ADDRESS, ($ip === '' ? null : $ip))
                        ->withAttribute(self::GEOIP_RECORD, $this->getGeoIpCityRecord($ip));

        return $handler->handle($request);
    }

    /**
     * @param string    $ipAddress     IP address
     */
    private function getGeoIpCityRecord(string $ipAddress): City
    {
        $reader = new Reader($this->geoLiteDbPath);

        try {
            return $reader->city($ipAddress);
        } catch (Exception) {
            return new City([]);
        }
    }
}
