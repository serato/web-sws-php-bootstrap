<?php
namespace Serato\SwsApp\Test\Controller\Traits;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Controller\Traits\ControllerTraitValidateApiEndpointVersion;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;
use ReflectionClass;

/**
 * Unit tests for Serato\SwsApp\Controller\Traits\ControllerTraitValidateApiEndpointVersion
 */
class ControllerTraitValidateApiEndpointVersionTest extends TestCase
{
    /**
     * * @dataProvider validApiVersionProvider
     */
    public function testHandlerValidApiVersion(array $validEndpointVersions, int $endPointVersion, bool $isValid)
    {
        $request = Request::createFromEnvironmentBuilder(EnvironmentBuilder::create());
        $response = new Response;

        $mock = $this->getMockForTrait(ControllerTraitValidateApiEndpointVersion::class);

        $reflection = new ReflectionClass($mock);
        $propEndpointVersions = $reflection->getProperty('endpointVersions');
        $propEndpointVersions->setAccessible(true);
        $propEndpointVersions->setValue($mock, $validEndpointVersions);
        $validateEndpointVersionMethod = $reflection->getMethod('validateEndpointVersion');
        $validateEndpointVersionMethod->setAccessible(true);

        $mock
            ->expects($this->exactly($isValid ? 0 : 1))
            ->method('onInvalidEndpointVersion')
            ->with(
                $this->callback(function ($arg) {
                    return is_a($arg, '\Serato\Slimulator\Request');
                }),
                $this->callback(function ($arg) {
                    return is_a($arg, '\Slim\Http\Response');
                })
            );

        $validateEndpointVersionMethod->invokeArgs($mock, [$request, $response, $endPointVersion]);
    }

    public function validApiVersionProvider()
    {
        return [
            [[1], 1, true],
            [[1], 2, false],
            [[1, 2], 1, true],
            [[1, 2], 2, true],
            [[1, 2], 3, false]
        ];
    }
}
