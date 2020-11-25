<?php
namespace Serato\SwsApp\EventDispatcher\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Serato\SwsApp\EventDispatcher\Event\SwsHttpResponse;
use Serato\SwsApp\EventDispatcher\Normalizer\PsrMessageNormalizer;

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
        $path = '/srv/www/shared_license_serato_com/req_rep_dumps/';
        @mkdir($path);

        $prettyJson = function (string $json): string {
            $data = json_decode($json, true);
            return json_encode($data, JSON_PRETTY_PRINT);
        };

        $prettyJsonFromArray = function (array $data): string {
            return json_encode($data, JSON_PRETTY_PRINT);
        };

        $requestFile = fopen($path . date('Y-m-dTH:i:s') . '-request.json', 'a');
        $responseFileName = fopen($path . date('Y-m-dTH:i:s') . '.-response.json', 'a');

        $normalizer = new PsrMessageNormalizer;

        // fwrite($requestFile, $prettyJson($this->serializer->serialize($event['request'], 'json')));
        // fwrite($responseFileName, $prettyJson($this->serializer->serialize($event['response'], 'json')));
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

    /**
     * --------
     * Bootstrapping
     * --------
     * Make usable via error handlers
     * 
     * --------
     * Request
     * --------
     * - What to do with "cookieParams"
     * --------
     * Response
     * --------
     * - Encode request/response body somehow
     *      - Strip sensitive?
     *      - Limit size?
     * - Do we use Attributes??


Remove sensitive data
Testing
  Methods
    POST
    PUT
    OPTIONS
    DELETE
  Request payload encodings (check payload and Content-Type header)
    application/x-www-form-urlencoded
    application/json
  Authorization
    None
    Basic
    Bearer token
  Query params
    GET. Anything other methods.


     */
}
