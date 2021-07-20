<?php

namespace Serato\SwsApp\Test\Slim\Handlers;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Handlers\PhpError as PhpErrorHandler;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Exception;

/**
 * Unit tests for Serato\SwsApp\Slim\Handler\Error
 */
class PhpErrorTest extends TestCase
{
    private const APP_NAME = 'My Web App';

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

    public function testSmokeTest()
    {
        $errorHandler = new PhpErrorHandler(
            self::APP_NAME,
            true,
            $this->getErrorLogger()
        );

        $response = $errorHandler(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
                    ->addHeader('Accept', 'application/json')
            ),
            new Response(),
            new Exception()
        );


        $this->assertEquals('application/json', $response->getHeader('Content-type')[0]);
        $this->assertTrue(true);
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
}
