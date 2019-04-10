<?php
namespace Serato\SwsApp;

use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Formatter\JsonFormatter;
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
        // Set the formatter to JSON
        foreach ($this->logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());
        }
    }

    /**
     * Writes a log entry
     *
     * @param Request           $request   The most recent Request object
     * @param Response          $response  The most recent Response object
     * @param Array             $extra     Extra information to log
     *
     * @return void
     */
    public function log(?Request $request, Response $response = null, array $extra = []): void
    {
        if ($request !== null) {
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
                'request_method'    => $request->getMethod(),
                'request_uri'       => $request->getUri()->getPath(),
                'query_params'      => $request->getQueryParams(),
                'remote_ip_address' => $request->getAttribute(GeoIpLookup::IP_ADDRESS, ''),
                'geo_ip_info'       => $geo,
                'client_app'        => $app,
                'request_scopes'    => $request->getAttribute(RequestMiddleware::SCOPES, []),
                'request_user_id'   => $request->getAttribute(RequestMiddleware::USER_ID, ''),
                'extra'             => $extra
            ];

            if ($response !== null) {
                $date['http_status_code'] = $response->getStatusCode();
                $seratoErrorCode = $response->getHeaderLine('X-Serato-ErrorCode');
                if ($seratoErrorCode !== null && $seratoErrorCode !== '') {
                    $data['serato_error_code'] = $seratoErrorCode;
                }
            }

            $this->logger->log(
                $this->logLevel,
                '',
                $data
            );
        }
    }
}
