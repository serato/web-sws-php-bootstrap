<?php

namespace Serato\SwsApp\Test\Validation;

use Mockery;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rakit\Validation\RuleNotFoundException;
use Rakit\Validation\Rules\Numeric;
use Serato\SwsApp\Exception\InvalidRequestParametersException;
use Serato\SwsApp\Exception\MissingRequiredParametersException;
use Serato\SwsApp\Http\Rest\Exception\UnsupportedContentTypeException;
use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Validation\RequestValidation;

class RequestValidationTest extends TestCase
{

    /**
     * @var RequestValidation
     */
    protected $validation;

    /**
     * @var Request
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->validation = new RequestValidation();
        $this->requestMock = Mockery::mock(Request::class);
    }
    /**
     * @dataProvider dataProvider
     *
     * @param array $requestBody
     * @param array $rules
     * @param string|null $errorExpected
     * @param array $customRules
     * @param array $exceptions
     * @param array|null $expectedRequest
     * @group validation
     */
    public function testValidateRequestData(
        array $requestBody,
        array $rules,
        ?string $errorExpected = null,
        array $customRules = [],
        array $exceptions = []
    ): void
    {
        $this->requestMock->shouldReceive('getParsedBody')
            ->andReturn($requestBody);

        if (!is_null($errorExpected)) {
            $this->expectException($errorExpected);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->validation->validateRequestData(
            $this->requestMock,
            $rules,
            $customRules,
            $exceptions
        );


    }

    /**
     * @dataProvider defaultDataProvider
     *
     * @param array $requestBody
     * @param array $rules
     * @param array|null $expectedRequest
     * @group validation
     */
    public function testRestDefaultDataPopulation(
        array $requestBody,
        array $rules,
        ?array $expectedRequest = null
    )
    {
        $this->requestMock->shouldReceive('getParsedBody')
            ->andReturn($requestBody);
        $preprocessedRequest = $this->validation->validateRequestData(
            $this->requestMock,
            $rules
        );
        if (!is_null($expectedRequest)) {
            $this->assertEqualsCanonicalizing($expectedRequest, $preprocessedRequest);
        }
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
            ],

        ];
    }

    /**
     * @return array
     */
    public function defaultDataProvider(): array
    {
        return [
            //preprocess data with default values
            [
                'body' => [
                    'paramName' => null,
                ],
                'rules' => [
                    'paramName' => 'default:value1|required|in:value1,value2,value3'
                ],
                'expectedRequest' => ['paramName' => 'value1']

            ]
        ];
    }
}