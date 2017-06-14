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
final class PhpError extends SlimPhpError
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
        $message = 'Slim Application Error:' . PHP_EOL;
        $message .= $this->renderThrowableAsText($throwable);
        while ($throwable = $throwable->getPrevious()) {
            $message .= PHP_EOL . 'Previous error:' . PHP_EOL;
            $message .= $this->renderThrowableAsText($throwable);
        }
        $this->logError($message);
    }

    /**
     * Wraps the error_log function so that this can be easily tested
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param $message
     */
    protected function logError($message)
    {
        $this->logger->critical($message);
    }
}
