<?php
namespace Serato\SwsApp\Slim\Controller\Traits;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Adds functionality to a controller to allow it validate the presence of required
 * parameters in the message body of `PUT` and `POST` requests
 */
trait ControllerTraitValidateRequiredRequestBodyParams
{

    /**
     * Array of required named parameters for PUT and POST requests
     *
     * @var array
     */
    protected $requiredRequestBodyParams = [];

    /**
     * Validates that required request body params are present in the request.
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param Request     $request     Request interface
     * @param Response    $response    Response interface
     *
     * @return void
     */
    protected function validateRequestBodyParams(Request $request, Response $response)
    {
        $requestBody = $request->getParsedBody();
        $missing = [];
        foreach ($this->requiredRequestBodyParams as $param) {
            if (!isset($requestBody[$param]) || $requestBody[$param] == '') {
                $missing[] = $param;
            }
        }
        return $this->handleMissingRequestBodyParams($request, $response, $missing);
    }

    /**
     * Handle missing required request body parameters.
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param Request     $request     Request interface
     * @param Response    $response    Response interface
     * @param array       $missing     Missing required parameters
     *
     * @return void
     */
    abstract protected function handleMissingRequestBodyParams(
        Request $request,
        Response $response,
        array $missing
    );
}
