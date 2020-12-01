<?php
namespace Serato\SwsApp\Slim\Handlers;

use Slim\Handlers\PhpError as SlimPhpError;
use Slim\Container;
use Psr\Log\LoggerInterface as Logger;
use Serato\SwsApp\RequestToContainerTrait;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Application PhpError Handler
 *
 * Extends the base Slim PhpError handler by using a provided logger interface to
 * write error messages to.
 */
class PhpError extends SlimPhpError
{
    use RequestToContainerTrait;

    /**
     * Application name
     *
     * @var string
     */
    protected $applicationName;

     /**
     * PSR-3 logger interface
     *
     * @var Logger
     */
    protected $logger;

    /** @var Container */
    private $container;

    /**
     * Construct the error handler
     *
     * @param string            $applicationName        Human readable name of application
     * @param bool              $displayErrorDetails    Display full error message including stack trace
     * @param Logger            $logger                 PSR-3 logger interface
     * @param Container|null    $container              Slim container instance
     */
    public function __construct(
        string $applicationName,
        bool $displayErrorDetails,
        Logger $logger,
        ?Container $container = null
    ) {
        parent::__construct($displayErrorDetails);
        $this->logger = $logger;
        $this->applicationName = $applicationName;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(Request $request, Response $response, \Throwable $error)
    {
        # Set the request object to the container
        # (the setRequestToContainer() method is part of `Serato\SwsApp\RequestToContainerTrait`)
        $this->setRequestToContainer($request, $this->container);

        parent::__invoke($request, $response, $error);
    }

    /**
     * Render HTML error page
     *
     * @param \Throwable $error
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(\Throwable $error)
    {
        return str_replace(
            'Slim Application Error',
            $this->applicationName . ' - Application Error',
            parent::renderHtmlErrorMessage($error)
        );
    }

    /**
     * Render JSON error
     *
     * @param \Throwable $error
     *
     * @return string
     */
    protected function renderJsonErrorMessage(\Throwable $error)
    {
        $json = json_decode(parent::renderJsonErrorMessage($error), true);
        $json['message'] = $this->applicationName . ' - Application Error';
        return json_encode($json, JSON_PRETTY_PRINT);
    }

    /**
     * Write to the error log
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param \Exception|\Throwable $throwable
     *
     * @return void
     */
    protected function writeToErrorLog($throwable)
    {
        $error = $this->renderThrowableAsArray($throwable);
        while ($throwable = $throwable->getPrevious()) {
            if (isset($error['previous'])) {
                $error['previous'] = [];
            }
            $error['previous'][] = $this->renderThrowableAsArray($throwable);
        }
        $this->logger->critical('Slim Application Unhandled Throwable', $error);
    }

    /**
     * @param \Exception|\Throwable $throwable
     *
     * @return array
     */
    private function renderThrowableAsArray($throwable): array
    {
        $error = [];
        $error['type'] = get_class($throwable);
        if ($code = $throwable->getCode()) {
            $error['code'] = $code;
        }
        if ($message = $throwable->getMessage()) {
            $error['message'] = htmlentities($message);
        }
        if ($file = $throwable->getFile()) {
            $error['file'] = $file;
        }
        if ($line = $throwable->getLine()) {
            $error['line'] = $line;
        }
        if ($trace = $throwable->getTraceAsString()) {
            $error['trace'] = explode("\n", $throwable->getTraceAsString());
        }
        return $error;
    }
}
