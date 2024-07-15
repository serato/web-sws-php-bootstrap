<?php

namespace Serato\SwsApp\EventDispatcher\Event;

use Symfony\Component\EventDispatcher\GenericEvent;
use Exception;

/**
 * AbstractEvent
 *
 * A base class from which all dispatchable events extends.
 *
 * Extends `Symfony\Component\EventDispatcher\GenericEvent` with the following:
 *
 * - Adds an abstract method `self::getArrayKeys` that returns a array of valid key
 *   names that can be set to the object. Any attempt to set a key name that is not
 *   defined in the array will result in an exception.
 *
 * - Adds a public static method `self::getEventName` that returns a string that should
 *   always be used as the name that an event instance is dispatched with.
 */
abstract class AbstractEvent extends GenericEvent
{
    /**
     * {@inheritDoc}
     */
    public function __construct($subject = null, array $arguments = [])
    {
        parent::__construct($subject, $arguments);
        # Reset $this->arguments and instead set the values self::offsetSet so that we
        # use the validation logic.
        $this->arguments = [];
        foreach ($arguments as $k => $v) {
            $this[$k] = $v;
        }
    }

    /**
     * An array of valid string key names that this event defines.
     *
     * Any attempt to set an array key to the object that does not exist in
     * this list will result in an exception.
     *
     * @return array
     */
    abstract protected function getArrayKeys(): array;

    /**
     * Returns the event name.
     *
     * This is the name that instances of this event should always be dispatched with.
     *
     * @return string
     */
    public static function getEventName(): string
    {
        return static::class;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($key, $value): void
    {
        if (!in_array($key, $this->getArrayKeys())) {
            throw new Exception(
                'Invalid array key `' . $key .
                '`. Valid keys are `' . implode('`, `', $this->getArrayKeys()) . '`'
            );
        }
        parent::setArgument($key, $value);
    }
}
