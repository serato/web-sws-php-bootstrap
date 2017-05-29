<?php
namespace Serato\SwsApp\Test\Controller\Status;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Controller\AbstractController;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;

/**
 * Unit tests for Serato\SwsApp\Controller\AbstractController
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

    public function testSetGetHttpResponseCode()
    {
        $logger = $this->getDebugLogger();
        $controller = $this->getMockForAbstractClass(AbstractController::class, [$logger]);

        $controller->setHttpResponseCode(400);

        $this->assertEquals(400, $controller->getHttpResponseCode());
    }

    public function testMockInvoke()
    {
        $logger = $this->getDebugLogger();
        $controller = $this->getMockForAbstractClass(AbstractController::class, [$logger]);
        $response = $controller->mockInvoke(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create())
        );

        $this->assertTrue(is_a($response, '\Slim\Http\Response'));
    }
}
