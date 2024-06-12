<?php

namespace Serato\SwsApp\Slim\Controller;

use GuzzleHttp\Psr7\Utils;
use Serato\SwsApp\Slim\Controller\AbstractController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Highlight\Highlighter;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteParserInterface;

class JsonSchemaViewerController extends AbstractController
{
    public const SCHEMAS_LIST_NAMED_ROUTE = 'schemasList';

    private const string HTML_VIEW = 'html';
    private const string JSON_VIEW = 'json';
    private const string JSONLIST_VIEW = 'jsonlist';

    private const string STYLE_SHEET = 'github-gist.css';

    /* @var RouteParserInterface */
    private $router;

    /* @var string */
    private $schemaDirectoryPath;

    /* @var string */
    private $stylesDirectoryPath;

    /**
     * Construct the controller
     *
     * @param LoggerInterface   $logger                 A PSR-3 logger interface
     * @param RouteParserInterface   $router            Slim route parser instance
     * @param string            $appName                Application name
     * @param string            $schemaDirectoryPath    Path to directory containing JSON schema files
     * @param string|null       $namedRoute             The named route for the base URI that the controller
     *                                                  is mapped to
     */
    public function __construct(
        LoggerInterface $logger,
        RouteParserInterface $router,
        private readonly string $appName,
        string $schemaDirectoryPath,
        string $stylesDirectoryPath,
        private ?string $namedRoute = null
    ) {
        parent::__construct($logger);
        $this->router = $router;
        $this->schemaDirectoryPath = rtrim($schemaDirectoryPath, '/');
        $this->stylesDirectoryPath = rtrim($stylesDirectoryPath, '/');
    }

    /**
     * Execute the endpoint action
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param  Request     $request            Request interface
     * @param  Response    $response           Response interface
     * @param  array       $args               Request URI args
     */
    #[\Override]
    public function execute(Request $request, Response $response, array $args): Response
    {
        $baseUri = $this->router->urlFor($this->getNamedRoute());

        $contentType = 'text/html';

        if (isset($args['view'])) {
            if (!in_array($args['view'], [self::HTML_VIEW, self::JSON_VIEW, self::JSONLIST_VIEW])) {
                throw new HttpNotFoundException($request);
            }
            if ($args['view'] === self::JSONLIST_VIEW) {
                // Display file list as JSON
                if (isset($args['file_name'])) {
                    throw new HttpNotFoundException($request);
                }
                $contentType = 'application/json';
                $content = json_encode([
                    'description' => $this->appName . ' - REST API JSON Schemas',
                    'baseUri' => $request->getUri()->getScheme() . '://' .
                                    $request->getUri()->getHost() . $baseUri . '/' . self::JSON_VIEW,
                    'items' => $this->getJsonFileList()
                ]);
            } else {
                // View individual file
                if (file_exists($this->schemaDirectoryPath . '/' . $args['file_name'])) {
                    $json = file_get_contents($this->schemaDirectoryPath . '/' . $args['file_name']);
                    if ($args['view'] === self::HTML_VIEW) {
                        $content = $this->makeHtmlDoc(
                            $this->makeJsonFileHtml($baseUri, $args['file_name'], $json),
                            $this->readCssFile()
                        );
                    }
                    if ($args['view'] === self::JSON_VIEW) {
                        $contentType = 'application/json';
                        $content = $json;
                    }
                } else {
                    throw new HttpNotFoundException($request);
                }
            }
        } else {
            $content = $this->makeHtmlDoc($this->makeFileListHtml($baseUri));
        }

        $response = $response->withHeader('Content-type', $contentType);

        $etag = self::formatEtagValue(md5($content));

        if (in_array($etag, $this->getIfNoneMatchEtags())) {
            $response = $response
                ->withStatus(304)
                ->withHeader('Etag', $etag);
        } else {
            $response = $response
                ->withStatus(200)
                ->withHeader('Etag', $etag)
                ->withBody(Utils::streamFor($content));
        }
        return $response;
    }

    /**
     * Sets the named route for the base URI that the controller is mapped to
     */
    public function setNamedRoute(string $namedRoute): void
    {
        $this->namedRoute = $namedRoute;
    }

    /**
     * Returns the named route for the base URI that the controller is mapped to
     */
    public function getNamedRoute(): string
    {
        # This function MUST return self::SCHEMAS_LIST_NAMED_ROUTE if no value is
        # explicitly set for $this->namedRoute to maintain backwards compatibility.
        return $this->namedRoute ?? self::SCHEMAS_LIST_NAMED_ROUTE;
    }

    private function makeFileListHtml(string $baseUri): string
    {
        $str = "<h2>JSON schema files:</h2>\n";
        $str .= "<ul>\n";
        foreach ($this->getJsonFileList() as $fileName) {
            $str .= "<li style=\"line-height:1.3em; width:600px;\">" .
                    "<div style=\"float:left; width:250px;\">$fileName</div>" .
                    " <small>" .
                    "<a href=\"$baseUri/" . self::HTML_VIEW . "/$fileName\">HTML</a>" .
                    " &middot; " .
                    "<a href=\"$baseUri/" . self::JSON_VIEW . "/$fileName\" target=\"_blank\">JSON</a>" .
                    "</small></li>\n";
        }
        $str .= "</ul>\n";

        return $str . ("<p><a href=\"$baseUri/" . self::JSONLIST_VIEW . "\" target=\"_blank\">View list as JSON</p>");
    }

    private function makeJsonFileHtml(string $baseUri, string $fileName, string $json): string
    {
        $highlighter = new Highlighter();
        $hl = $highlighter->highlight('json', $json);

        $str = "<h2>$fileName</h2>\n";
        $str .= "<p><a href=\"$baseUri\">Back</a></p>\n";
        $str .= "<pre class=\"hljs {$hl->language}\">" . $this->makeJsonDocLinks($baseUri, $hl->value) . "</pre>\n";
        return $str . "<p><a href=\"$baseUri\">Back</a></p>\n";
    }

    private function makeJsonDocLinks(string $baseUri, string $body): string
    {
        preg_match_all('/".*\.json/', $body, $matches);

        foreach ($matches[0] as $match) {
            $jsonFileName = substr((string) $match, strrpos((string) $match, '"') + 1);
            $jsonLink = '<a href="' . $baseUri . '/' . self::HTML_VIEW . '/' . $jsonFileName . '">' .
                        $jsonFileName . '</a>';
            $body = str_replace($jsonFileName, $jsonLink, $body);
        }

        return $body;
    }

    private function makeHtmlDoc(string $body, string $css = ''): string
    {
        return sprintf(
            $this->getHtmlTemplate(),
            $this->appName,
            $css,
            $this->appName,
            $body
        );
    }

    private function readCssFile(): string
    {
        return file_get_contents($this->stylesDirectoryPath . '/' . self::STYLE_SHEET);
    }

    private function getHtmlTemplate(): string
    {
        return <<<EOT
    <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
            <title>%s - REST API JSON Schemas</title>
            <style>
            %s
            </style>
        </head>
        <body>
            <h1>%s REST API JSON Schemas</h1>
            %s
        </body>
    </html>
EOT;
    }

    private function getJsonFileList(): array
    {
        $files = [];
        foreach (glob($this->schemaDirectoryPath . '/*.json') as $filePath) {
            $files[] = substr($filePath, strrpos($filePath, '/') + 1);
        }
        return $files;
    }
}
