<?php

namespace Serato\SwsApp;

use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Serato\SwsApp\Utils\MonologJsonFormatter;
use Serato\SwsApp\Slim\Middleware\GeoIpLookup;
use Serato\SwsApp\Slim\Middleware\AbstractRequestWithAttributeMiddleware as RequestMiddleware;
use Serato\SwsApp\Slim\Handlers\Error as ErrorHandler;

class AccessLogWriter
{
    /* @var Logger */
    private $logger;

    /* @var string */
    private $logLevel;

    /* @var array */
    private $bodyParamNames;

    /**
     * Construct the error handler
     *
     * @param Logger    $logger                 PSR-3 logger interface
     * @param string    $logLevel               The log level to write entries to
     * @param array     $bodyParamNames         Body parameter names to log
     */
    public function __construct(Logger $logger, string $logLevel = 'INFO', array $bodyParamNames = [])
    {
        $this->logger = $logger;
        $this->logLevel = $logLevel;
        $this->bodyParamNames = $bodyParamNames;
        // Set the formatter to JSON
        foreach ($this->logger->getHandlers() as $handler) {
            $handler->setFormatter(new MonologJsonFormatter());
        }
    }

    /**
     * Writes a log entry
     *
     * @param Request           $request            The most recent Request object
     * @param Response          $response           The most recent Response object
     * @param Array             $extra              Extra information to log
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
                'extra'             => $extra,
                'stream'            => 'access'
            ];
            if (is_array($request->getParsedBody())) {
                $logBodyParams = array_filter(
                    $request->getParsedBody(),
                    function ($key) {
                        return in_array($key, $this->bodyParamNames);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                if (!empty($logBodyParams)) {
                    $data['body_params'] = $logBodyParams;
                }
            }
            if ($response !== null) {
                $data['http_status_code'] = $response->getStatusCode();
                $seratoErrorCode = $response->getHeaderLine(ErrorHandler::ERROR_CODE_HTTP_HEADER);
                if ($seratoErrorCode !== null && $seratoErrorCode !== '') {
                    $data['serato_error_code'] = $seratoErrorCode;
                }
                $seratoErrorMessage = $response->getHeaderLine(ErrorHandler::ERROR_MESSAGE_HTTP_HEADER);
                if ($seratoErrorMessage !== null && $seratoErrorMessage !== '') {
                    $data['serato_error_message'] = $seratoErrorMessage;
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
