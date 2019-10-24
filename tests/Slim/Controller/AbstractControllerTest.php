<?php
namespace Serato\SwsApp\Test\Slim\Controller\Status;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Controller\AbstractController;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;

/**
 * Unit tests for Serato\SwsApp\Slim\Controller\AbstractController
 */
class AbstractControllerTest extends TestCase
{
    public function testInvoke()
    {
        $logger = $this->getDebugLogger();

        $controller = $this->getMockForAbstractClass(AbstractController::class, [$logger]);

        $controller
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->callback(function ($arg) {
                    return is_a($arg, '\Serato\Slimulator\Request');
                }),
                $this->callback(function ($arg) {
                    return is_a($arg, '\Slim\Http\Response');
                }),
                $this->callback(function ($arg) {
                    return is_array($arg);
                })
            );

        $controller(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            new Response(),
            []
        );
    }

    public function testMockInvoke()
    {
        $logger = $this->getDebugLogger();
        $controller = $this->getMockForAbstractClass(AbstractController::class, [$logger]);
        $controller->expects($this->any())
            ->method('execute')
            ->willReturn(new Response);
        
        $response = $controller->mockInvoke(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create())
        );

        # Assert that we've received a Response object back
        $this->assertTrue(is_a($response, '\Slim\Http\Response'));
        # Assert that no etag header is set
        $this->assertEquals($response->getHeader('Etag'), []);
    }

    public function testSetEtag()
    {
        $logger = $this->getDebugLogger();
        $controller = $this->getMockForAbstractClass(AbstractController::class, [$logger]);
        $controller->setEtag(md5('test-value'));
        $etag = $controller->getEtag();
        $controller->expects($this->any())
            ->method('execute')
            ->willReturn(new Response);
        
        $response = $controller->mockInvoke(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create())
        );

        $this->assertEquals($response->getHeader('Etag')[0], $etag);
    }
}
