<?php
namespace Serato\SwsApp\Test\Slim\Handlers;

use Aws\Exception\CredentialsException;
use Serato\SwsApp\Slim\Handlers\Error;
use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Handlers\Error as ErrorHandler;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Exception;

/**
 * Unit tests for Serato\SwsApp\Slim\Handler\Error
 */
class ErrorTest extends TestCase
{
    const APP_NAME = 'My Web App';

    public function setUp()
    {
        parent::setUp();
        if (file_exists($this->getErrorLogPath())) {
            unlink($this->getErrorLogPath());
        }
    }

    public function tearDown()
    {
        parent::setUp();
        if (file_exists($this->getErrorLogPath())) {
            unlink($this->getErrorLogPath());
        }
    }

    /**
     * @dataProvider displayErrorsProvider
     */
    public function testHandleErrorWithJsonResponse(
        $exceptionClass,
        $httpResponseCode,
        $displayErrorDetals
    ) {
        $assertText = "$exceptionClass $httpResponseCode $displayErrorDetals";
        
        $errorHandler = new ErrorHandler(
            self::APP_NAME,
            $displayErrorDetals,
            $this->getErrorLogger()
        );

        $exception = new $exceptionClass();

        $response = $errorHandler(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->addHeader('Accept', 'application/json')
            ),
            new Response,
            $exception
        );

        // Unhandled exceptions should be written to the log file
        if (!is_a($exception, '\Serato\SwsApp\Http\Rest\Exception\AbstractException')) {
            $logContents = trim(file_get_contents($this->getErrorLogPath()));
            // Should have a single line in the error log file
            $this->assertEquals(1, count(explode("\n", $logContents)), $assertText);
            // Log should contain exception text
            $this->assertRegExp('/' . $exception->getMessage() . '/', $logContents, $assertText);
        }

        $this->assertEquals($httpResponseCode, $response->getStatusCode(), $assertText);
        $this->assertEquals('application/json', $response->getHeader('Content-type')[0], $assertText);
        
        // Parse the body content
        $json = json_decode($response->getBody(), true);

        if ($exception instanceof CredentialsException) {
            $logContents = trim(file_get_contents($this->getErrorLogPath()));
            # THERE is a log entry.
            $this->assertCount(1,explode("\n", $logContents), $assertText);
            $this->assertEquals(Error::GENERIC_ERROR_MESSAGE, $json['message']);
        } elseif (!is_a($exception, '\Serato\SwsApp\Http\Rest\Exception\AbstractException')) {
            // Unhandled exceptions can output a stack trace to the client
            // when $displayErrorDetals = true.
            $this->assertEquals($displayErrorDetals, isset($json['exception']), $assertText);
        } else {
            // REST exceptions have their message and code put into the JSON response
            // sent to the client
            $this->assertEquals($exception->getMessage(), $json['error']);
            $this->assertEquals($exception->getCode(), $json['code']);
            // They also have custom HTTP headers with the error code and messages
            $this->assertEquals(
                $exception->getMessage(),
                $response->getHeaderLine(ErrorHandler::ERROR_MESSAGE_HTTP_HEADER)
            );
            $this->assertEquals(
                $exception->getCode(),
                $response->getHeaderLine(ErrorHandler::ERROR_CODE_HTTP_HEADER)
            );
        }
    }

    /**
     * @todo testHandleErrorWithHtmlResponse
     * @todo testHandleErrorWithXmlResponse
     */

    private function getErrorLogger(): Logger
    {
        $logger = $this->getLogger();
        $errorLogStream = new StreamHandler(
            $this->getErrorLogPath(),
            Logger::ERROR
        );
        $logger->pushHandler($errorLogStream);
        return $logger;
    }

    private function getErrorLogPath()
    {
        return __DIR__ . '/error.log';
    }

    public function displayErrorsProvider()
    {
        return [
            ['\Exception', 500, true],
            ['\Exception', 500, false],
            ['\Serato\SwsApp\Http\Rest\Exception\ExpiredAccessTokenException', 401, true],
            ['\Serato\SwsApp\Http\Rest\Exception\ExpiredAccessTokenException', 401, true],
            ['Aws\Exception\CredentialsException', 503, true]
        ];
    }
}
