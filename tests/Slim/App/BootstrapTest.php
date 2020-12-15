<?php
namespace Serato\SwsApp\Test\Slim\App;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\App\Bootstrap;
use Serato\SwsApp\EventDispatcher\Subscriber\LogToFileSubscriber;

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

    public function testAbstractMethodExecution()
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

    /**
     * Tests that the `Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest` event is dispatched.
     */
    public function testSwsHttpRequestEventDispatch()
    {
        # As best as I can tell, the Symfony Event dispatcher does not provide any means of resporting on
        # what events is has dispatched (would LOVE to be proven wrong about this).
        # So to determine whether or not an event has dispatched we need to provide a lister or subscriber
        # that allows us to assert on the event(s) we're interested in.
        # Over time it may make sense to create an event subscriber class specifically for testing purposes
        # but for now I'm just going to use the LogToFileSubscriber class.
        $tmpFileDir = rtrim(sys_get_temp_dir(), '/') . '/phpunit_BootstrapTest/' .
            str_replace(' ', '', ltrim(microtime(), '0.')) . '/';
        $bootstrap = $this->getMockForAbstractClass(Bootstrap::class, []);
        $bootstrap->addEventSubscriber(
            new LogToFileSubscriber('LibBootstrap', 'dev', 1, $tmpFileDir)
        );
        $bootstrap->createApp();
        # The `Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest` event is dispatched in the `run` method.
        # So call `run` but provide `true` for the "silent" argument.
        # This will prevent any output being written by the Bootstrap app instance.
        $bootstrap->run(true);

        $logFiles = glob($tmpFileDir . '*');

        # Assert that the directory that stores that log files has been created
        $this->assertTrue(is_dir($tmpFileDir));
        # Assert that we have two files in the directory (this is fragile, but maybe in a good way)
        $this->assertEquals(2, count($logFiles));

        # Delete the temp directory. We have to delete all files first. Groan.
        foreach ($logFiles as $filePath) {
            unlink($filePath);
        }
        rmdir($tmpFileDir);
    }
}
