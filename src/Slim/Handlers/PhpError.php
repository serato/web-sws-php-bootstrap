<?php

namespace Serato\SwsApp\Slim\Handlers;

use Slim\Handlers\PhpError as SlimPhpError;
use Psr\Log\LoggerInterface as Logger;

/**
 * Application PhpError Handler
 *
 * Extends the base Slim PhpError handler by using a provided logger interface to
 * write error messages to.
 */
class PhpError extends SlimPhpError
{
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

    /**
     * Construct the error handler
     *
     * @param string    $applicationName        Human readable name of application
     * @param bool      $displayErrorDetails    Display full error message including stack trace
     * @param Logger    $logger                 PSR-3 logger interface
     */
    public function __construct(string $applicationName, bool $displayErrorDetails, Logger $logger)
    {
        parent::__construct($displayErrorDetails);
        $this->logger = $logger;
        $this->applicationName = $applicationName;
    }

    /**
     * Render HTML error page
     *
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(\Throwable $error)
    {
        return str_replace(
            'Slim Application Error',
            $this->applicationName . ' - Application Error',
            (string) parent::renderHtmlErrorMessage($error)
        );
    }

    /**
     * Render JSON error
     *
     *
     * @return string
     */
    protected function renderJsonErrorMessage(\Throwable $error)
    {
        $json = json_decode((string) parent::renderJsonErrorMessage($error), true);
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
     */
    private function renderThrowableAsArray($throwable): array
    {
        $error = [];
        $error['type'] = $throwable::class;
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
