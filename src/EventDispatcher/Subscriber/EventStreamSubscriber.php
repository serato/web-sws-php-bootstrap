<?php
namespace Serato\SwsApp\EventDispatcher\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Serato\SwsApp\EventDispatcher\Event\SwsHttpRequest;
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
     * Request
     * --------
     * - What to do with "cookieParams"
     * - Attributes. Where are they?
     *      app id would be good
     *      refresh token ID maybe
     * --------
     * Response
     * --------
     * - Encode request/response body somehow
     *      - Strip sensitive?
     * - Do we use Attributes??

    
Implementation
    A middleware to be added to route groups
    A trait that allows the containter['requestPostMiddleware'] to be updated
        private $container = null;
        public funcion setContainer($container): void;
        protected function updateRequest(): void;

Better name than `requestPostMiddleware`

     Bootstrapping
        Need to add new 'set request object to container' middleware to all route groups
        What about routes that don't have groups? Add as app middleware?
            SOLVED. But needs more testing.
        Also need error handlers to set updated request to middleware.


Remove sensitive data
    Refresh tokens
        ID service
        Are they used anywhere else?
        Access tokens are OK.
    Basic auth creds in requests
        `Authorization` header value
        `Php-Auth-User` header value (maybe keep this)
        `Php-Auth-Pw` header value

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
  Etag requests and Not Modified responses

     */
}
