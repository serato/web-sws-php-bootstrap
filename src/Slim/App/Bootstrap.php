<?php

namespace Serato\SwsApp\Slim\App;

define('DISPATCHER', 'event-dispatcher');

use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteGroupInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest;
use Serato\SwsApp\RequestToContainerTrait;
use Serato\SwsApp\Slim\Middleware\RequestToContainer as RequestToContainerMiddleware;

/**
 * Bootstrap the Slim application by adding routes, controllers, error handlers
 * and middleware.
 */
abstract class Bootstrap
{
    use RequestToContainerTrait;

    /**
     * Slim application
     *
     * @var App
     */
    private $app;

    /**
     * Slim container
     *
     * @var Container
     */
    private Container $container;

    /** @var RequestToContainerMiddleware */
    private $requestToContainerMiddleware;

    /**
     * Constructs the Bootstrap instance
     *
     * @return void
     */
    public function __construct( )
    {
        $this->container = new Container();
        AppFactory::setContainer($this->container);
        $this->app = AppFactory::create();
        $this->container->set(DISPATCHER, new EventDispatcher());
        $this->requestToContainerMiddleware = new RequestToContainerMiddleware($this->container);
    }

    /**
     * Get the bootstrapped Slim application instance
     */
    public function createApp(): App
    {
        // Register and configure common services
        $this->registerControllers();
        $this->registerErrorHandlers();
        // Add routes and middleware
        $this->addAppMiddleware();
        $this->addRoutes();

        return $this->getApp();
    }

    public function addRouteGroup(string $pattern, callable $callable): RouteGroupInterface
    {
        # Create the route group...
        $group = $this->getApp()->group($pattern, $callable);
        # ...and add the `RequestToContainerMiddleware` to it
        return $group->add($this->getRequestToContainerMiddleware());
    }

    /**
     * Run application
     */
    public function run(bool $silent = false): void
    {
        // Run the Slim app
        $this->getApp()->run();

        // Commented out for testing. I think we'd want to move this to middleware.
//        // Create the `SwsHttpRequest` event...
//        $event = new SwsHttpRequest();
//        $event['response'] = $response;
//        $event['request'] = $this->getRequestFromContainer($this->container);
//        if ($event['request'] === null) {
//            $event['request'] = $this->container->get('request');
//        }
//
//        // ...and dispatch it
//        $dispatcher = $this->container->get(DISPATCHER);
//        $dispatcher->dispatch($event, SwsHttpRequest::getEventName());
//
//        return $response;
    }

    /**
     * Get the dependency container
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Register a service in the dependency container
     *
     * @param string    $key   Identifier for the service in the container
     * @param callable  $f     Callable to register in the container
     */
    public function register(string $key, callable $f): self
    {
        $this->container->set($key, $f);
        return $this;
    }


    /**
     * Adds an event listener to the dispatcher.
     *
     * @param string   $eventName The event to listen on
     * @param callable $listener  The listener
     * @param int      $priority  The higher this value, the earlier an event
     *                            listener will be triggered in the chain (defaults to 0)
     */
    public function addEventListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->container->get(DISPATCHER)->addListener($eventName, $listener, $priority);
    }

    /**
     * Adds an event subscriber.
     *
     * The subscriber is asked for all the events it is
     * interested in and added as a listener for these events.
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->container->get(DISPATCHER)->addSubscriber($subscriber);
    }

    /**
     * Get the `Serato\SwsApp\Slim\Middleware\RequestToContainer` instance
     */
    private function getRequestToContainerMiddleware(): RequestToContainerMiddleware
    {
        return $this->requestToContainerMiddleware;
    }

    /**
     * Get the Slim application instance
     */
    protected function getApp(): App
    {
        return $this->app;
    }

    /**
     * Register custom error handlers
     *
     * @return void
     *
     * @link https://www.slimframework.com/docs/handlers/error.html
     */
    abstract protected function registerErrorHandlers();

    /**
     * Register app middleware (ie. middleware that is applied to all routes)
     *
     * @return void
     */
    abstract protected function addAppMiddleware();

    /**
     * Add routes
     *
     * @return void
     */
    abstract protected function addRoutes();

    /**
     * Register controllers
     *
     * @return void
     */
    abstract protected function registerControllers();
}
