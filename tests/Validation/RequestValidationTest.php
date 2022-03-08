<?php

namespace Serato\SwsApp\Test\Validation;

use Psr\Http\Message\ServerRequestInterface as Request;
use Serato\SwsApp\Exception\MissingRequiredParametersException;
use Serato\SwsApp\Exception\InvalidRequestParametersException;
use Rakit\Validation\RuleNotFoundException;
use Serato\SwsApp\Validation\RequestValidation;
use Serato\SwsApp\Test\TestCase;
use Rakit\Validation\Rules\Numeric;
use Serato\SwsApp\Http\Rest\Exception\UnsupportedContentTypeException;
use Mockery;

class RequestValidationTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param array $requestBody
     * @param array $rules
     * @param string|null $errorExpected
     *
     * @group validation
     */
    public function testRest(
        array $requestBody,
        array $rules,
        ?string $errorExpected = null,
        array $customRules = [],
        array $exceptions = []
    ): void {
        if (!is_null($errorExpected)) {
            $this->expectException($errorExpected);
        }

        $validation  = new RequestValidation();
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('getParsedBody')
            ->andReturn($requestBody);

        $validation->validateRequestData(
            $requestMock,
            $rules,
            $customRules,
            $exceptions
        );

        // phpunit >= 6.4 complain This test did not perform any assertions
        // put this to avoid the error.
        $this->assertTrue(true);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            // no errors
            [
                'body' => [
                    'paramName' => 'paramValue'
                ],
                'rules' => [
                    'paramName' => 'required'
                ],
            ],
            // missing required param
            [
                'body' => [
                    'paramName' => ''
                ],
                'rules' => [
                    'paramName' => 'required'
                ],
                'errorExpected' => MissingRequiredParametersException::class,
            ],
            // invalid param
            [
                'body' => [
                    'paramName' => 'too long request param'
                ],
                'rules' => [
                    'paramName' => 'max:5'
                ],
                'errorExpected' => InvalidRequestParametersException::class,
            ],
            // invalid rule
            [
                'body' => [
                    'paramName' => 'param value'
                ],
                'rules' => [
                    'paramName' => 'invalid'
                ],
                'errorExpected' => RuleNotFoundException::class,
            ],
            // custom rule
            [
                'body' => [
                    'paramName' => '1'
                ],
                'rules' => [
                    'paramName' => 'required|is_numberic'
                ],
                'errorExpected' => null,
                'customRules' => [
                    'is_numberic' => new Numeric()
                ]
                ],
            // custom exception
            [
                'body' => [
                    'paramName' => 'invalid-number'
                ],
                'rules' => [
                    'paramName' => 'required|is_numberic'
                ],
                'errorExpected' => UnsupportedContentTypeException::class,
                'customRules' => [
                    'is_numberic' => new Numeric()
                ],
                'customException' => [
                    'is_numberic' => UnsupportedContentTypeException::class
                ]
            ]
        ];
    }
}
