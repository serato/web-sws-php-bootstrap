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
     * @param array $expectedResult - expected result after processing the request.
     * @group validation
     */
    public function testValidateRequestData(
        array $requestBody,
        array $rules,
        ?string $errorExpected = null,
        array $customRules = [],
        array $exceptions = [],
        ?array $expectedResult = null
    ): void {
        $this->requestMock->shouldReceive('getParsedBody')
            ->andReturn($requestBody);

        if (!is_null($errorExpected)) {
            $this->expectException($errorExpected);
        } elseif (is_null($expectedResult)) {
            $this->expectNotToPerformAssertions();
        }

        $preprocessedRequest = $this->validation->validateRequestData(
            $this->requestMock,
            $rules,
            $customRules,
            $exceptions
        );

        if (!is_null($expectedResult)) {
            $this->assertEqualsCanonicalizing($expectedResult, $preprocessedRequest);
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
            //preprocess data with default values
            [
                'body' => [
                    'paramName' => null,
                ],
                'rules' => [
                    'paramName' => 'default:value1|required|in:value1,value2,value3'
                ],
                'errorExpected' => null,
                'customRules' => [],
                'customException' => [],
                'expectedResult' => ['paramName' => 'value1']
            ]
        ];
    }
}
