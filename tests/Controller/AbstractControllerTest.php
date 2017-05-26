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
    public function testMockInvoke()
    {
        $logger = $this->getDebugLogger();

        $mock = $this->getMockForAbstractClass(AbstractController::class, [$logger]);

        $mock
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->callback(function($arg){
                    return is_a($arg, '\Serato\Slimulator\Request');
                }),
                $this->callback(function($arg){
                    return is_a($arg, '\Slim\Http\Response');
                }),
                $this->callback(function($arg){
                    return is_array($arg);
                })
            );

        $mock(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            new Response(),
            []
        );
    }

    public function testMockSetGetHttpResponseCode()
    {
        $logger = $this->getDebugLogger();
        $mock = $this->getMockForAbstractClass(AbstractController::class, [$logger]);

        $mock->setHttpResponseCode(400);

        $this->assertEquals(400, $mock->getHttpResponseCode());
    }
}
