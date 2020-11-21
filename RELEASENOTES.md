# Release notes

## Event dispatcher

The Symfony Event dispatcher is now available as a standard component of the library.

A `Symfony\Component\EventDispatcher\EventDispatcher` instance is added to the dependency container using the key `event-dispatcher`.
This is implemented in the `Serato\SwsApp\Slim\App\Bootstrap` constructor.

This `event-dispatcher` literal value is defined in a global PHP constant called `DISPATCHER`. This constant
should always be used when referencing the item in the dependency container.

Bootstrap::addEventListener
Bootstrap::addEventSubscriber

## Abstract event class

TODO

## Currently there are no listeners or subscribers

Need to define how to Bootstrap these.
