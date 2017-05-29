<?php

namespace Serato\SwsApp\Slim\App;

use Slim\App;
use Psr\Container\ContainerInterface;

/**
 * Bootstrap the Slim application by adding routes, controllers, error handlers
 * and middleware.
 */
abstract class Bootstrap
{
    /**
     * Slim application
     *
     * @var App
     */
    private $app;

    /**
     * PSR dependency container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructs the Bootstrap instance
     *
     * @param array $settings   Configuration settings for a Slim app
     *
     * @return void
     */
    public function __construct(array $settings = [])
    {
        $this->app = new App($settings);
        $this->container = $this->app->getContainer();
    }

    /**
     * Get the bootstrapped Slim application instance
     *
     * @return App
     */
    public function getApp(): App
    {
        // Register and configure common services
        $this->registerControllers();
        $this->registerErrorHandlers();
        // Add routes and middleware
        $this->addAppMiddleware();
        $this->addRoutes();

        return $this->app;
    }

    /**
     * Get the dependency container
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Register a service in the dependency container
     *
     * @param string    $key   Identifier for the service in the container
     * @param callable  $f     Callable to register in the container
     *
     * @return self
     */
    public function register(string $key, callable $f): self
    {
        $this->container[$key] = $f;
        return $this;
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
