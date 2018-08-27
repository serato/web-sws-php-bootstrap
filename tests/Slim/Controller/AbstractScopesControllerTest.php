<?php
namespace Serato\SwsApp\Test\Slim\Controller\Status;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Controller\AbstractScopesController;
use Serato\SwsApp\Slim\Controller\Scopes;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;
use Serato\SwsApp\Slim\Middleware\AbstractRequestWithAttributeMiddleware as RequestMiddleware;

/**
 * Unit tests for Serato\SwsApp\Slim\Controller\AbstractController
 */
class AbstractScopesControllerTest extends TestCase
{
    /**
     * * @dataProvider scopesProvider
     */
    public function testScopes(array $requestScopes, array $controllerScopes, bool $isValid)
    {
        $logger = $this->getDebugLogger();

        $controller = $this->getMockForAbstractClass(AbstractScopesController::class, [$logger]);

        $controller
            ->expects($this->once())
            ->method('getControllerScopes')
            ->willReturn(
                Scopes::create()->addScopes($controllerScopes)
            );

        $controller
            ->expects($this->exactly($isValid ? 0 : 1))
            ->method('handleInvalidControllerScopes')
            ->with(
                $this->callback(function ($arg) {
                    return is_a($arg, '\Serato\Slimulator\Request');
                }),
                $this->callback(function ($arg) {
                    return is_a($arg, '\Slim\Http\Response');
                }),
                $this->callback(function ($arg) {
                    return is_a($arg, '\Serato\SwsApp\Slim\Controller\Scopes');
                })
            );

        $controller(
            Request::createFromEnvironmentBuilder(
                EnvironmentBuilder::create()
            )->withAttributes([RequestMiddleware::SCOPES => $requestScopes]),
            new Response(),
            []
        );
    }

    public function scopesProvider()
    {
        return [
            [['scope1'], ['scope1'], true],
            [['scope1', 'scope2'], ['scope1'], true],
            [['scope1'], ['scope1', 'scope2'], true],
            [['scope1', 'scope2'], ['scope1', 'scope2'], true],
            [['scope2', 'scope3'], ['scope1', 'scope2'], true],
            [['scope3'], ['scope1', 'scope2'], false],
            [['scope3', 'scope4'], ['scope1', 'scope2'], false]
        ];
    }
}
