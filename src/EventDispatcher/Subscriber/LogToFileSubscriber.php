<?php

namespace Serato\SwsApp\EventDispatcher\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest;
use Serato\SwsApp\EventDispatcher\Normalizer\PsrMessageNormalizer;
use Ramsey\Uuid\Uuid;

/**
 * LogToFileSubscriber
 *
 * An event subscriber that logs event data to a file.
 *
 * Is (currently) intended to be only used for testing purposes.
 */
class LogToFileSubscriber implements EventSubscriberInterface
{
    /** @var string */
    private $id;

    /**
     * Constructs the object
     */
    public function __construct(private readonly string $appName, private readonly string $env, private readonly int $stackNumber, private readonly string $logDirPath)
    {
        @mkdir($this->logDirPath, 0777, true);

        # The `id` value should be included in the contents of all event data written to disk.
        # This allows us to correlate disparate event data as having originate from the same subscriber
        # instance and therefore, in the case of a web application, the same HTTP request.
        $this->id = Uuid::uuid4();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            SwsHttpRequest::getEventName() => 'onSwsHttpRequest'
        ];
    }

    /**
     * Handles an `Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest` event
     */
    public function onSwsHttpRequest(SwsHttpRequest $event): void
    {
        $prettyJsonFromArray = fn (array $data): string => json_encode($data, JSON_PRETTY_PRINT);

        $requestFile = fopen($this->logDirPath . date('Y-m-dTH:i:s') . '-request.json', 'a');
        $responseFileName = fopen($this->logDirPath . date('Y-m-dTH:i:s') . '.-response.json', 'a');

        $normalizer = new PsrMessageNormalizer();

        fwrite(
            $requestFile,
            $prettyJsonFromArray(array_merge(
                ['meta' => $this->getMetaData(SwsHttpRequest::getEventName())],
                $normalizer->normalizePsrServerRequestInterface($event['request'])
            ))
        );
        fwrite(
            $responseFileName,
            $prettyJsonFromArray(array_merge(
                ['meta' => $this->getMetaData(SwsHttpRequest::getEventName())],
                $normalizer->normalizePsrResponseInterface($event['response'])
            ))
        );

        fclose($requestFile);
        fclose($responseFileName);
    }

    private function getMetaData(string $eventName): array
    {
        return [
            'application_name' => $this->appName,
            'application_environment' => $this->env,
            'application_stack_number' => $this->stackNumber,
            'event_id' => $this->id,
            'event_name' => $eventName
        ];
    }
}
