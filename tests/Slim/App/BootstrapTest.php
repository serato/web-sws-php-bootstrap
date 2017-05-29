<?php
namespace Serato\SwsApp\Test\Slim\App;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\App\Bootstrap;

/**
 * Unit tests for Serato\SwsApp\Slim\App\Bootstrap
 */
class BootstrapTest extends TestCase
{
    const CONTAINER_ITEM = 'A string of text';

    public function testSmokeTest()
    {
        $bootstrap = $this->getMockForAbstractClass(Bootstrap::class, []);

        $this->assertTrue(is_a($bootstrap->createApp(), '\Slim\App'));
        $this->assertTrue(is_a($bootstrap->getContainer(), '\Psr\Container\ContainerInterface'));

        $bootstrap->register('mykey', function () {
            return self::CONTAINER_ITEM;
        });
        $this->assertEquals(self::CONTAINER_ITEM, $bootstrap->getContainer()['mykey']);
    }

    public function testAbstractMethods()
    {
        $bootstrap = $this->getMockForAbstractClass(Bootstrap::class, []);

        $bootstrap
            ->expects($this->once())
            ->method('registerErrorHandlers');

        $bootstrap
            ->expects($this->once())
            ->method('addAppMiddleware');

        $bootstrap
            ->expects($this->once())
            ->method('addRoutes');

        $bootstrap
            ->expects($this->once())
            ->method('registerControllers');

        $bootstrap->createApp();
    }
}
