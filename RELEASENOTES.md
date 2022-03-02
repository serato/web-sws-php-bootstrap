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

Whilst there are a number of different ways we could provide listeners and subscribers to the event dispatcher it makes to be consistent
across all web applications. I suggest the following approach:

### 1. Create a file called `eventDispatcher.php` in the `<app name>/app/bootstrap` directory.

Create a new file in `<app name>/app/bootstrap` and name it `eventDispatcher.php`. This is where we can add any and all listeners and
subscribers on a per-application basis. The file will look something like this:

```php
<?php
use Serato\SwsApp\EventDispatcher\Subscriber\LogToFileSubscriber;

/**
 * Configures the Event Dispatcher for this application
 */

# Might need to use the container, so extract it here.
$container = $bootstrap->getContainer();

# Add a `Serato\SwsApp\EventDispatcher\Subscriber\LogToFileSubscriber` instance to the event dispatcher.
# Note the use of the (new) `Serato\SwsApp\Slim\App\Bootstrap::addEventSubscriber` method.
# The $boostrap instance is available globally in this file (obviously) - see next step.
$bootstrap->addEventSubscriber(
    new LogToFileSubscriber(
        $container['settings']['application_name'],
        $container['settings']['app_env'],
        $container['settings']['app_stack_number'],
        '/srv/www/shared_license_serato_com/req_rep_dumps/'
    )
);
```

### 2. Include `eventDispatcher.php` into 'index.php`

Add a new `require` statement in `<app name>/app/index.php`:

```php
# Add this line
require '../bootstrap/eventDispatcher.php';
```

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

#### Use the `Bootstrap::addRouteGroup` method to add route groups

In the concrete child instance of the `Serato\SwsApp\Slim\App\Bootstrap`, instead of using the `group()` method of the underlying Slim application object to add route groups, the `Bootstrap::addRouteGroup` method should be used instead.

Using this method ensures that routes specified via route groups are able to provide the correct `ServerRequestInterface` instance to the dispatched `SwsHttpRequest` event.

The method signatures are identical, so `Bootstrap::addRouteGroup` returns a `Slim\Interfaces\RouteGroupInterface` instance.

```php
# Don't do this.
# $this->getApp()->group('<route pattern>', function () {/** Route mapping logic */});
# Instead..

$this->addRouteGroup('<route pattern>', function () {/** Route mapping logic */});
```

#### Pass the container instance to the `Serato\SwsApp\Slim\Handlers\Error` error handler

The `Serato\SwsApp\Slim\Handlers\Error` error handler now has an optional 4th argument: the Slim container instance.

Providing this argument ensures that error handler can pass the correct `ServerRequestInterface` instance to the dispatched `SwsHttpRequest` event.

## Subscribers

The following subscribers are included in this update:

### LogToFileSubscriber

The `Serato\SwsApp\EventDispatcher\Subscriber\LogToFileSubscriber` is intended to be used as a debugging tool only. Consider it POC as
to how to implement an event subscriber.

It provides a callback called `onSwsHttpRequest` that subscribes to the `Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest` event
and JSON encodes the request and response instances into separate files.

More event handlers can be added as required.
