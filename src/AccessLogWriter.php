<?php
namespace Serato\SwsApp;

use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Serato\SwsApp\Slim\Middleware\GeoIpLookup;
use Serato\SwsApp\Slim\Middleware\AbstractRequestWithAttributeMiddleware as RequestMiddleware;

class AccessLogWriter
{
    /* @var Logger */
    private $logger;

    /* @var string */
    private $logLevel;

    /**
     * Construct the error handler
     *
     * @param Logger    $logger          PSR-3 logger interface
     * @param string    $logLevel        The log level to write entries to
     */
    public function __construct(Logger $logger, string $logLevel = 'INFO')
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
    }

    /**
     * Writes a log entry
     *
     * @param Request           $request   The most recent Request object
     * @param Response          $response  The most recent Response object
     *
     * @return void
     */
    public function log(Request $request, Response $response): void
    {
        $geo = [];
        if ($request->getAttribute(GeoIpLookup::GEOIP_RECORD) !== null) {
            $record = $request->getAttribute(GeoIpLookup::GEOIP_RECORD);
            $geo = [
                'city'          => $record->city->name,
                'postcode'      => $record->postal->code,
                'country_name'  => $record->country->name,
                'country_iso'   => $record->country->isoCode,
                'continent_iso' => $record->continent->code
            ];
        }

        $app = [];
        if ($request->getAttribute(RequestMiddleware::APP_ID) !== null) {
            $app = [
                'id'    => $request->getAttribute(RequestMiddleware::APP_ID),
                'name'  => $request->getAttribute(RequestMiddleware::APP_NAME, '')
            ];
        }

        $data = [
            'http_status_code'  => $response->getStatusCode(),
            'query_params'      => $request->getQueryParams(),
            'remote_ip_address' => $request->getAttribute(GeoIpLookup::IP_ADDRESS, ''),
            'geo_ip_info'       => $geo,
            'client_app'        => $app,
            'request_scopes'    => $request->getAttribute(RequestMiddleware::SCOPES, []),
            'request_user_id'   => $request->getAttribute(RequestMiddleware::USER_ID, '')
        ];

        $this->logger->log(
            $this->logLevel,
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            $data
        );
    }
}
