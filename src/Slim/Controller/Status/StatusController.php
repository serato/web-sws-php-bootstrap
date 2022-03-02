<?php

namespace Serato\SwsApp\Slim\Controller\Status;

use Psr\Log\LoggerInterface;
use Serato\SwsApp\Slim\Controller\AbstractController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Negotiation\Negotiator;
use Serato\SwsApp\Slim\Middleware\GeoIpLookup;
use GeoIp2\Model\City;
use Datetime;

/**
 * Status Controller
 *
 * A controller that outputs information about the web application
 */
final class StatusController extends AbstractController
{
    /**
     * Path to file containing most recent git commit hash
     *
     * @var string
     */
    protected $gitCommitFilePath;

    /**
     * Constructs the controller
     *
     * @param LoggerInterface   $logger   A PSR-3 logger interface
     * @param string    $gitCommitFilePath  Path to file containing most recent git commit hash
     */
    public function __construct(LoggerInterface $logger, string $gitCommitFilePath)
    {
        parent::__construct($logger);
        $this->gitCommitFilePath = $gitCommitFilePath;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(Request $request, Response $response, array $args): Response
    {
        $negotiator = new Negotiator();
        $contentTypePriorities = ['application/json', 'text/html'];
        $contentType = $contentTypePriorities[0];
        $content = '';

        if ($request->hasHeader('Accept')) {
            $mediaType = $negotiator->getBest(
                $request->getHeaderLine('Accept'),
                $contentTypePriorities
            );
            if ($mediaType !== null) {
                $contentType = $mediaType->getType();
            }
        }

        // Allow for setting the content type via a GET parameter
        // (I'm looking at you Jenkins, you POS)
        if ($request->getQueryParam('content_type', null) !== null) {
            $mediaType = $negotiator->getBest(
                $request->getQueryParam('content_type'),
                $contentTypePriorities
            );
            if ($mediaType !== null) {
                $contentType = $mediaType->getType();
            }
        }

        $date = new Datetime();

        $vars = [
            'current_time' => $date->format(DateTime::ATOM),
            'host' => $request->getHeaderLine('Host'),
            'web_app_commit' => $this->getGitCommitHash()
        ];

        if ($contentType == 'text/html') {
            $content = sprintf(
                $this->getHtmlTemplate(),
                'Application Status',
                'Application Status',
                $vars['current_time'],
                $vars['host'],
                $vars['web_app_commit'],
                str_replace('()', '', $this->getGeoIpHtml($request))
            );
        } else {
            $data = [
                'current_time' => $date->format(DateTime::ATOM),
                'host' => $request->getHeaderLine('Host'),
                'web_app_commit' => $this->getGitCommitHash()
            ];

            if ($request->getAttribute(GeoIpLookup::IP_ADDRESS) !== null) {
                $data['remote_address'] = $request->getAttribute(GeoIpLookup::IP_ADDRESS);
            }

            if ($request->getAttribute(GeoIpLookup::GEOIP_RECORD) !== null) {
                $data['remote_location'] = $this->getGeoIpArray($request->getAttribute(GeoIpLookup::GEOIP_RECORD));
            }

            $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        return $response
            ->withStatus(200)
            ->withHeader('Content-type', $contentType)
            ->write($content);
    }

    /**
     * Read the Git commit hash from a file
     *
     * @return string
     */
    private function getGitCommitHash(): string
    {
        if (file_exists($this->gitCommitFilePath)) {
            return file_get_contents($this->gitCommitFilePath);
        } else {
            return '';
        }
    }

    private function getGeoIpHtml(Request $request): string
    {
        $str = '';
        if ($request->getAttribute(GeoIpLookup::IP_ADDRESS) !== null) {
            $str .= '<div><strong>Remote IP address:</strong> ' .
                    $request->getAttribute(GeoIpLookup::IP_ADDRESS) . '</div>';
        }
        if ($request->getAttribute(GeoIpLookup::GEOIP_RECORD) !== null) {
            $str .= '<div><strong>Location:</strong><br>' .
                    implode('<br>', $this->getGeoIpArray($request->getAttribute(GeoIpLookup::GEOIP_RECORD))) .
                    '</div>';
        }
        return $str;
    }

    private function getHtmlTemplate(): string
    {
        return <<<EOT
    <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
            <title>%s</title>
            <style>
                body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}
                h1{margin:0;font-size:32px;font-weight:normal;line-height:40px;}
                strong{display:inline-block;width:170px;}
            </style>
        </head>
        <body>
            <h1>%s</h1>
            <p>
                <div><strong>System time:</strong> %s</div>
                <div><strong>Host:</strong> %s</div>
                <div><strong>Web application commit:</strong> %s</div>
                %s
            </p>
        </body>
    </html>
EOT;
    }

    private function getGeoIpArray(City $record): array
    {
        return array_filter(
            [
                $record->city->name,
                (isset($record->subdivisions[0]) ? $record->subdivisions[0]->name . ' ' : '') . $record->postal->code,
                $record->country->name . ' (' . $record->country->isoCode . ')',
                $record->continent->name
            ],
            function ($val) {
                return $val !== null && $val !== '';
            }
        );
    }
}
