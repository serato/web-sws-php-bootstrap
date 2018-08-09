<?php
namespace Serato\SwsApp\Test\Slim\Controller\Status;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Controller\AbstractController;
use Serato\SwsApp\Slim\Controller\Status\StatusController;
use Serato\SwsApp\Slim\Middleware\GeoIpLookup;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;
use Serato\SwsApp\Slim\Handlers\Error as ErrorHandler;
use Serato\SwsApp\Http\Rest\Exception\AbstractException as ClientException;
use GeoIp2\Model\City;

/**
 * Unit tests for Serato\SwsApp\Slim\Controller\StatusController
 */
class StatusControllerTest extends TestCase
{
    /**
     * Test the controller JSON output with a git commit file path value that
     * evaluates to a non-existent file
     *
     * @group controller
     */
    public function testJsonContentTypeNoGitCommitFile()
    {
        $response = $this->executeControllerTest(
            new StatusController($this->getDebugLogger(), './not_such_file'),
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()->addHeader('Accept', 'application/json')
            )
        );

        $this->assertEquals($response->getStatusCode(), 200);
        $obj = json_decode((string)$response->getBody(), true);
        $this->assertTrue(isset($obj['current_time']));
        $this->assertTrue(isset($obj['host']));
        $this->assertTrue(isset($obj['web_app_commit']));
    }

    /**
     * Test the controller JSON output with a git commit file path value that
     * evaluates to a file in the 'resources' directory
     *
     * @group controller
     */
    public function testJsonContentTypeGitCommitFile()
    {
        $response = $this->executeControllerTest(
            new StatusController($this->getDebugLogger(), __DIR__ . '/../../../resources/git_commit.txt'),
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()->addHeader('Accept', 'application/json')
            )
        );

        $this->assertEquals($response->getStatusCode(), 200);
        $obj = json_decode((string)$response->getBody(), true);
        $this->assertTrue(isset($obj['current_time']));
        $this->assertTrue(isset($obj['host']));
        $this->assertTrue(isset($obj['web_app_commit']));
        $this->assertEquals($obj['web_app_commit'], 'abc123456789def');
    }

    /**
     * As above but now also include IP address and GeoLiteDB data added to the request
     *
     * @group controller
     */
    public function testJsonContentTypeGitCommitFileAndGeoIp()
    {
        $ip_address = '192.168.4.1';

        $request = Request::createFromEnvironmentBuilder(
            EnvironmentBuilder::create()->addHeader('Accept', 'application/json')
        );

        // FYI, these attributes can be added via the `Serato\SwsApp\Slim\Middleware\GeoIpLookup` middleware
        $request = $request
            ->withAttribute(GeoIpLookup::IP_ADDRESS, $ip_address)
            ->withAttribute(GeoIpLookup::GEOIP_RECORD, new City([]));

        $response = $this->executeControllerTest(
            new StatusController($this->getDebugLogger(), __DIR__ . '/../../../resources/git_commit.txt'),
            $request
        );

        $this->assertEquals($response->getStatusCode(), 200);
        $obj = json_decode((string)$response->getBody(), true);
        $this->assertTrue(isset($obj['current_time']));
        $this->assertTrue(isset($obj['host']));
        $this->assertTrue(isset($obj['web_app_commit']));
        $this->assertEquals($obj['web_app_commit'], 'abc123456789def');
        $this->assertEquals($obj['remote_address'], $ip_address);
        $this->assertTrue(is_array($obj['remote_location']));
    }

    /**
     * Test the controller HTML output with a git commit file path value that
     * evaluates to a file in the 'resources' directory
     *
     * @group controller
     */
    public function testHtmlContentTypeGitCommitFile()
    {
        $response = $this->executeControllerTest(
            new StatusController($this->getDebugLogger(), __DIR__ . '/../../../resources/git_commit.txt'),
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()->addHeader('Accept', 'text/html')
            )
        );

        $this->assertEquals($response->getStatusCode(), 200);
        $html = (string)$response->getBody();

        $this->assertRegExp('/\<html\>/', $html);
        $this->assertRegExp('/\<\/html\>/', $html);
        $this->assertRegExp('/\<body\>/', $html);
        $this->assertRegExp('/\<\/body\>/', $html);
        $this->assertRegExp('/Application Status/', $html);
    }


    /**
     * As above but now also include IP address and GeoLiteDB data added to the request
     *
     * @group controller
     */
    public function testHtmlContentTypeGitCommitFileAndGeoIp()
    {
        $ip_address = '192.168.4.1';

        $request = Request::createFromEnvironmentBuilder(
            EnvironmentBuilder::create()->addHeader('Accept', 'text/html')
        );

        // FYI, these attributes can be added via the `Serato\SwsApp\Slim\Middleware\GeoIpLookup` middleware
        $request = $request
            ->withAttribute(GeoIpLookup::IP_ADDRESS, $ip_address)
            ->withAttribute(GeoIpLookup::GEOIP_RECORD, new City([]));

        $response = $this->executeControllerTest(
            new StatusController($this->getDebugLogger(), __DIR__ . '/../../../resources/git_commit.txt'),
            $request
        );

        $this->assertEquals($response->getStatusCode(), 200);
        $html = (string)$response->getBody();

        $this->assertRegExp('/\<html\>/', $html);
        $this->assertRegExp('/\<\/html\>/', $html);
        $this->assertRegExp('/\<body\>/', $html);
        $this->assertRegExp('/\<\/body\>/', $html);
        $this->assertRegExp('/Application Status/', $html);
        $this->assertRegExp('/Remote IP address/', $html);
        $this->assertRegExp('/' . $ip_address .'/', $html);
        $this->assertRegExp('/Location:/', $html);
    }

    /**
     * Test the controller output with a header that specifies a content type
     * `text/html` but a `content_type` URL parameter that overrides this to
     * a valid value of `application/json`
     *
     * In this case the reponse should be JSON
     *
     * @group controller
     */
    public function testHtmlContentTypeValidUrlOverride()
    {
        $response = $this->executeControllerTest(
            new StatusController($this->getDebugLogger(), __DIR__ . '/../../../resources/git_commit.txt'),
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->addHeader('Accept', 'text/html')
                    ->addGetParam('content_type', 'application/json')
            )
        );

        $this->assertEquals($response->getStatusCode(), 200);
        $obj = json_decode((string)$response->getBody(), true);
        $this->assertTrue(isset($obj['current_time']));
        $this->assertTrue(isset($obj['host']));
        $this->assertTrue(isset($obj['web_app_commit']));
        $this->assertEquals($obj['web_app_commit'], 'abc123456789def');
    }

    /**
     * Test the controller output with a header that specifies a content type
     * `text/html` but a `content_type` URL parameter that overrides this to
     * invalid value of `nonsense`
     *
     * In this case the reponse should be HTML
     *
     * @group controller
     */
    public function testHtmlContentTypeInvalidUrlOverride()
    {
        $response = $this->executeControllerTest(
            new StatusController($this->getDebugLogger(), __DIR__ . '/../../resources/git_commit.txt'),
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->addHeader('Accept', 'text/html')
                    ->addGetParam('content_type', 'nonsense')
            )
        );

        $this->assertEquals($response->getStatusCode(), 200);
        $html = (string)$response->getBody();

        $this->assertRegExp('/\<html\>/', $html);
        $this->assertRegExp('/\<\/html\>/', $html);
        $this->assertRegExp('/\<body\>/', $html);
        $this->assertRegExp('/\<\/body\>/', $html);
        $this->assertRegExp('/Application Status/', $html);
    }

    /**
     * Create and invoke a controller and return a Response object. Optionally
     * catch client errors and pass the error through the custom error handler
     * middleware.
     *
     * @param AbstractController    $controller         Controller instance
     * @param Request               $request            Request interface
     * @param array                 $uriArgs            Name/value pairs of dynamic URI parameters
     * @param bool                  $catchClientErrors  When true catch client errors and invoke the error handler
     *
     * @return Response
     */
    protected function executeControllerTest(
        AbstractController $controller,
        Request $request,
        array $uriArgs = [],
        bool $catchClientErrors = false
    ) : Response {
        $response = new Response();

        if ($catchClientErrors) {
            try {
                return $controller($request, $response, $uriArgs);
            } catch (ClientException $e) {
                $error = new ErrorHandler('App Test', false, $this->getLogger());
                // Returns a Response object
                return $error($request, $response, $e);
            }
        } else {
            return $controller($request, $response, $uriArgs);
        }
    }
}
