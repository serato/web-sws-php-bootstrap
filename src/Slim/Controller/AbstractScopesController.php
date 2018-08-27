<?php
namespace Serato\SwsApp\Slim\Controller;

use Serato\SwsApp\Slim\Controller\AbstractController;
use Serato\SwsApp\Slim\Controller\Scopes;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Serato\SwsApp\Slim\Middleware\AbstractRequestWithAttributeMiddleware as RequestMiddleware;

/**
 * Abstract Scopes Controller
 *
 * A base controller class that provides functionality for defining access
 * controls via defined scopes of access.
 *
 * Scopes of access are defined at the outer most controller level. ie. They
 * provided all-or-nothing access to functionality contained within the controller.
 *
 * End-user scopes are provided into this controller via a `Psr\Http\Message\ServerRequestInterface`
 * instance and exposed with an attribute named `scopes`. ie.
 *
 *      $userScopes = $request->getAttribute('scopes');
 *
 * A child class defines required scopes of access by implementing the
 * `self::getControllerScopes` method returning a `Serato\SwsApp\Slim\Controller\Scopes` instance.
 *
 * Scopes are validated in an `any of` pattern. ie. An end user requires only one of the
 * Controller's defined scopes of access.
 */
abstract class AbstractScopesController extends AbstractController
{
    /**
     * Callable implementation. Controllers are registered to routes as
     * callables which dictates the method signature.
     *
     * @param  Request     $request            Request interface
     * @param  Response    $response           Response interface
     * @param  array       $args               Request args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->checkScopes($request, $response);

        return parent::__invoke($request, $response, $args);
    }

    /**
     * Returns the list of scopes requested by the client
     *
     * @return Scopes
     *
     */
    abstract protected function getControllerScopes(): Scopes;

    /**
     * Handle invalid controller scopes.
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param Request     $request     Request interface
     * @param Response    $response    Response interface
     * @param Scopes      $scopes      Controller scopes instance
     *
     * @return void
     */
    abstract protected function handleInvalidControllerScopes(
        Request $request,
        Response $response,
        Scopes $scopes
    );

    /**
     * This functions checks scopes against the request scopes
     *
     * @todo Specify void return type in PHP 7.1
     *
     * @param  Request     $request            Request interface
     * @param  Response    $response           Response interface
     *
     * @return void
     */
    protected function checkScopes(Request $request, Response $response)
    {
        $requestScopes      = $request->getAttribute(RequestMiddleware::SCOPES, []);
        $controllerScopes   = $this->getControllerScopes();

        if (count($controllerScopes->getScopes()) === 0) {
            return;
        }

        foreach ($controllerScopes->getScopes() as $scope) {
            if (in_array($scope, $requestScopes)) {
                return;
            }
        }

        return $this->handleInvalidControllerScopes(
            $request,
            $response,
            $controllerScopes
        );
    }
}
