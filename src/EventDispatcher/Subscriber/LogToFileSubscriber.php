<?php
namespace Serato\SwsApp\EventDispatcher\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest;
use Serato\SwsApp\EventDispatcher\Normalizer\PsrMessageNormalizer;

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
    private $appName;

    /** @var string */
    private $env;

    /** @var int */
    private $stackNumber;

    /** @var string */
    private $logDirPath;

    /**
     * Constructs the object
     *
     * @param string $appName
     * @param string $env
     * @param integer $stackNumber
     * @param string $logDirPath
     */
    public function __construct(string $appName, string $env, int $stackNumber, string $logDirPath)
    {
        $this->appName = $appName;
        $this->env = $env;
        $this->stackNumber = $stackNumber;
        $this->logDirPath = $logDirPath;
        @mkdir($this->logDirPath);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SwsHttpRequest::getEventName() => 'onSwsHttpRequest'
        ];
    }

    /**
     * Handles an `Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest` event
     *
     * @param SwsHttpRequest $event
     * @return void
     */
    public function onSwsHttpRequest(SwsHttpRequest $event): void
    {
        $prettyJson = function (string $json): string {
            $data = json_decode($json, true);
            return json_encode($data, JSON_PRETTY_PRINT);
        };

        $prettyJsonFromArray = function (array $data): string {
            return json_encode($data, JSON_PRETTY_PRINT);
        };

        $requestFile = fopen($this->logDirPath . date('Y-m-dTH:i:s') . '-request.json', 'a');
        $responseFileName = fopen($this->logDirPath . date('Y-m-dTH:i:s') . '.-response.json', 'a');

        $normalizer = new PsrMessageNormalizer;

        fwrite(
            $requestFile,
            $prettyJsonFromArray($normalizer->normalizePsrServerRequestInterface($event['request']))
        );
        fwrite(
            $responseFileName,
            $prettyJsonFromArray($normalizer->normalizePsrServerResponseInterface($event['response']))
        );

        fclose($requestFile);
        fclose($responseFileName);
    }
}
