<?php
namespace Serato\SwsApp\EventDispatcher\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Serato\SwsApp\EventDispatcher\Event\SwsHttpResponse;

/**
 * EventStreamSubscriber
 *
 * An event subscriber subscribes to all events
 */
class EventStreamSubscriber implements EventSubscriberInterface
{
    /** @var string */
    private $appName;
    /** @var string */
    private $env;
    /** @var int */
    private $stackNumber;
    /**
     * Constructs the object
     *
     * @param string $appName
     * @param string $env
     * @param integer $stackNumber
     */
    public function __construct(string $appName, string $env, int $stackNumber)
    {
        $this->appName = $appName;
        $this->env = $env;
        $this->stackNumber = $stackNumber;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SwsHttpResponse::getEventName() => 'onSwsHttpResponse'
        ];
    }

    /**
     * Handles an `Serato\SwsApp\EventDispatcher\Event\SwsHttpResponse` event
     *
     * @param SwsHttpResponse $event
     * @return void
     */
    public function onSwsHttpResponse(SwsHttpResponse $event): void
    {
        // echo "\n\nRECEIVED SwsHttpResponse\n\n";
    }
}
