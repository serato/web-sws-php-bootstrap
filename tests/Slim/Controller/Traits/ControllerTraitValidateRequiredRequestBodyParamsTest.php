<?php

namespace Serato\SwsApp\Test\Slim\Controller\Traits;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Controller\Traits\ControllerTraitValidateRequiredRequestBodyParams;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\RequestBody\UrlEncoded;
use Serato\Slimulator\Request;
use Slim\Http\Response;
use ReflectionClass;

/**
 * Unit tests for Serato\SwsApp\Slim\Controller\Traits\ControllerTraitValidateRequiredRequestBodyParams
 */
class ControllerTraitValidateRequiredRequestBodyParamsTest extends TestCase
{
    /**
     * * @dataProvider validRequestBodyParamsProvider
     */
    public function testHandlerValidRequestBodyParams(
        array $requiredRequestBodyParams,
        array $requestParams,
        array $missingRequiredParams
    ) {
        $request = Request::createFromEnvironmentBuilder(
            EnvironmentBuilder::create()->setRequestBody(UrlEncoded::create($requestParams))
        );
        $response = new Response();

        $mock = $this->getMockForTrait(ControllerTraitValidateRequiredRequestBodyParams::class);

        $reflection = new ReflectionClass($mock);

        $propRequiredRequestBodyParams = $reflection->getProperty('requiredRequestBodyParams');
        $propRequiredRequestBodyParams->setAccessible(true);
        $propRequiredRequestBodyParams->setValue($mock, $requiredRequestBodyParams);

        $methodValidateRequestBodyParams = $reflection->getMethod('validateRequestBodyParams');
        $methodValidateRequestBodyParams->setAccessible(true);

        $mock
            ->expects($this->once())
            ->method('handleMissingRequestBodyParams')
            ->with(
                $this->callback(fn($arg) => is_a($arg, '\Serato\Slimulator\Request')),
                $this->callback(fn($arg) => is_a($arg, '\Slim\Http\Response')),
                $this->callback(fn($arg) => is_array($arg))
            )
            ->willReturn($missingRequiredParams);

        $methodValidateRequestBodyParams->invokeArgs($mock, [$request, $response]);
    }

    public function validRequestBodyParamsProvider()
    {
        return [
            [[], [], []],
            [[], ['nonRequiredParam' => 'value'], []],
            [['param1'], ['param1' => 'value'], []],
            [['param1', 'param2'], ['param1' => 'value'], ['param2']],
            [['param1', 'param2'], ['param1' => ''], ['param1', 'param2']],
            [['param1', 'param2'], ['param1' => 'value', 'param2' => 'value'], []],
            [
                ['param1', 'param2', 'param3'],
                ['param1' => 'value', 'param2' => 'value', 'param3' => 'value'],
                []
            ],
            [
                ['param1', 'param2', 'param3'],
                ['param2' => 'value', 'param3' => 'value'],
                ['param1']
            ],
            [
                ['param1', 'param2', 'param3'],
                ['param2' => 'value'],
                ['param1', 'param3']
            ],
            [
                ['param1', 'param2', 'param3'],
                ['param2' => ''],
                ['param1', 'param2', 'param3']
            ]
        ];
    }
}
