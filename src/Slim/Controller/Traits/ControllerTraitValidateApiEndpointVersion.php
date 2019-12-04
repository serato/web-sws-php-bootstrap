<?php
namespace Serato\SwsApp\Slim\Controller\Traits;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Adds functionality to a `Serato\SwsApp\Slim\Controller\AbstractController` instance
 * to allow it to validate the version fragment of a URI against a list of accepted versions.
 *
 * Version fragments are typically used in REST APIs and identified by their placement
 * in the URI. eg.
 *
 *  Version 1 of the `myendpoint` endpoint:
 *  https://myapi.com/api/v1/myendpoint
 *
 *  Version 2 of the `myendpoint` endpoint:
 *  https://myapi.com/api/v2/myendpoint
 */
trait ControllerTraitValidateApiEndpointVersion
{
    /**
     * API endpoint versions supported by the controller
     *
     * @var array
     */
    protected $endpointVersions = [1];

    /**
     * Validate the provided `$version` against the list of acceptable versions
     * for this controller.
     *
     * @param Request     $request     Request interface
     * @param Response    $response    Response interface
     * @param int         $version     API version endpoint
     *
     * @return void
     */
    protected function validateEndpointVersion(Request $request, Response $response, int $version)
    {
        if (!in_array($version, $this->endpointVersions)) {
            $this->handleInvalidEndpointVersion($request, $response);
        }
    }

    /**
     * Handle an invalid endpoint version
     *
     * @param Request     $request     Request interface
     * @param Response    $response    Response interface
     *
     * @return void
     */
    abstract protected function handleInvalidEndpointVersion(Request $request, Response $response);
}
