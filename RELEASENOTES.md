# Release notes

## Event dispatcher

The Symfony Event dispatcher is now available as a standard component of the library.

A `Symfony\Component\EventDispatcher\EventDispatcher` instance is added to the dependency container using the key `event-dispatcher`.
This is implemented in the `Serato\SwsApp\Slim\App\Bootstrap` constructor.

This `event-dispatcher` literal value is defined in a global PHP constant called `DISPATCHER`. This constant
should always be used when referencing the item in the dependency container.

### Adding listeners and event subscribers

The `Serato\SwsApp\Slim\App\Bootstrap` class has two new methods for adding listeners and event subscribers to the event dispatcher:

- `Bootstrap::addEventListener`
- `Bootstrap::addEventSubscriber`

## Abstract event class

TODO

## The SwsHttpRequest event

An SWS web application can now be configured such that it dispatches a `Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest` event for every HTTP request.

### Bootstrapping

Dispatching the `SwsHttpRequest` requires changing the usage of the per-application concrete child instance of the `Serato\SwsApp\Slim\App\Bootstrap` abstract class.

Typically, a per-application concreate Bootstrap object is created in an `index.php` that contains code like this:

```php
<?php
# Create an instance `App\Bootstrap`, a concrete child class of `Serato\SwsApp\Slim\App\Bootstrap`
$bootstrap = new \App\Bootstrap(['settings' => require '../config.php']);

# Bootstrap as required (services, event dispatcher, propel etc)

# Create the underlying Slim application inside the Bootstrap instance and run the Slim application
$bootstrap->createApp()->run();
```

To dispatch the `Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest` event this last line of the file must be changed, as follows:

```php
<?php
# Don't do this.
# $bootstrap->createApp()->run();
# Instead..

# Create the underlying Slim application
$bootstrap->createApp();
# Execute the `run` method of the Bootstrap instance
$bootstrap->run();
```

This is the minimum requirements to dispatch the `Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest` event.

The following two steps are optional but highly desirable as they ensure the the `SwsHttpRequest` event is dispatched with the correctly mutated `Psr\Http\Message\ServerRequestInterface` instance.

### Use the `Bootstrap::addRouteGroup` method to add route groups

In the concrete child instance of the `Serato\SwsApp\Slim\App\Bootstrap`, instead of using the `group()` method of the underlying Slim application object to add route groups, the `Bootstrap::addRouteGroup` method should be used instead.

Using this method ensures that routes specified via route groups are able to provide the correct `ServerRequestInterface` instance to the dispatched `SwsHttpRequest` event.

The method signatures are identical, so `Bootstrap::addRouteGroup` returns a `Slim\Interfaces\RouteGroupInterface` instance.

```php
# Don't do this.
# $this->getApp()->group('<route pattern>', function () {/** Route mapping logic */});
# Instead..

$this->addRouteGroup('<route pattern>', function () {/** Route mapping logic */});
```

### Pass the container instance to the error handlers

The `Serato\SwsApp\Slim\Handlers\Error` and `Serato\SwsApp\Slim\Handlers\PhpError` error handlers now have an optional 4th argument: the Slim container instance.

Providing this argument insures that error handlers can provide the correct `ServerRequestInterface` instance to the dispatched `SwsHttpRequest` event.

## Currently there are no listeners or subscribers

Need to define how to Bootstrap these. (see License service)
