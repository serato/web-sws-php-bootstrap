<?php
namespace Serato\SwsApp\Test\Controller\Status;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Controller\AbstractController;
use Serato\SwsApp\Controller\Status\StatusController;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;
use Serato\SwsApp\Slim\Handlers\Error as ErrorHandler;
use Serato\SwsApp\Http\Rest\Exception\AbstractException as ClientException;

/**
 * Unit tests for Serato\SwsApp\Controller\Status\StatusController
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
            new StatusController('./not_such_file'),
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
            new StatusController(__DIR__ . '/../../resources/git_commit.txt'),
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
     * Test the controller HTML output with a git commit file path value that
     * evaluates to a file in the 'resources' directory
     *
     * @group controller
     */
    public function testHtmlContentTypeGitCommitFile()
    {
        $response = $this->executeControllerTest(
            new StatusController(__DIR__ . '/../../resources/git_commit.txt'),
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
            new StatusController(__DIR__ . '/../../resources/git_commit.txt'),
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
            new StatusController(__DIR__ . '/../../resources/git_commit.txt'),
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
