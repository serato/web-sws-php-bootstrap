<?php

namespace Serato\SwsApp\Test\Validation;

use Mockery;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rakit\Validation\RuleNotFoundException;
use Rakit\Validation\Rules\Numeric;
use Rakit\Validation\Rules\Regex;
use Serato\SwsApp\Exception\InvalidRequestParametersException;
use Serato\SwsApp\Exception\InvalidTagRequestParametersException;
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
     * @param array|null $requestBody
     * @param array $rules
     * @param string|null $errorExpected
     * @param array $customRules
     * @param array $exceptions
     * @param array $expectedResult - expected result after processing the request.
     * @group validation
     */
    public function testValidateRequestData(
        ?array $requestBody,
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
            // invalid params contains html tags throw InvalidTagRequestParametersException
            [
                'body' => [
                    'paramName' => '<br>'
                ],
                'rules' => [
                    'paramName' => RequestValidation::NO_HTML_TAG_RULE
                ],
                'errorExpected' => InvalidTagRequestParametersException::class
            ],
            // invalid params contains invalid format throws InvalidRequestParametersException
            [
                'body' => [
                    'paramName' => '<br>'
                ],
                'rules' => [
                    'paramName' => 'regex:/^a/'
                ],
                'errorExpected' => InvalidRequestParametersException::class,
                'customRules' => [
                    'regex:/^a/' => new Regex()
                ],
                'customException' => [
                    'regex' => InvalidRequestParametersException::class
                ]
            ],
            // invalid params contains invalid format and html tags throws InvalidRequestParametersException
            [
                'body' => [
                    'paramName' => '<br>',
                    'paramName2' => '<a>'
                ],
                'rules' => [
                    'paramName' => 'regex:/^a/', // any string start with `a`
                    'paramName2' => RequestValidation::NO_HTML_TAG_RULE
                ],
                'errorExpected' => InvalidRequestParametersException::class,
                'customRules' => [
                    'regex:/^a/' => new Regex()
                ],
                'customException' => [
                    'regex' => InvalidRequestParametersException::class
                ]
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
            // custom rule and invalid params contains html tags not excepting errors
            [
                'body' => [
                    'paramName' => '1',
                    'paramNam2' => '<a>'
                ],
                'rules' => [
                    'paramName' => 'required|is_numberic',
                    'paramName2' => RequestValidation::NO_HTML_TAG_RULE
                ],
                'errorExpected' => null,
                'customRules' => [
                    'is_numberic' => new Numeric()
                ],
                'customException' => [
                    'is_numberic' => UnsupportedContentTypeException::class
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
            // custom exception and invalid params contains html tags throws UnsupportedContentTypeException
            [
                'body' => [
                    'paramName' => 'invalid-number',
                    'paramName2' => '<br>'
                ],
                'rules' => [
                    'paramName' => 'required|is_numberic',
                    'paramName2' => RequestValidation::NO_HTML_TAG_RULE
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
                    'paramName' => 'default:value1|in:value1,value2,value3'
                ],
                'errorExpected' => null,
                'customRules' => [],
                'customException' => [],
                'expectedResult' => ['paramName' => 'value1']
            ],
            //preprocess data with empty body
            [
                'body' => [],
                'rules' => [
                    'paramName' => 'default:value1|in:value1,value2,value3'
                ],
                'errorExpected' => null,
                'customRules' => [],
                'customException' => [],
                'expectedResult' => ['paramName' => 'value1']
            ],
            //preprocess data with garbage values
            [
                'body' => [
                    'paramName' => 'garbage value',
                ],
                'rules' => [
                    'paramName' => 'default:value1|in:value1,value2,value3'
                ],
                'errorExpected' => InvalidRequestParametersException::class,
                'customRules' => [],
                'customException' => [],
                'expectedResult' => null
            ],
            // null body, no rules
            [
                'body' => null,
                'rules' => [],
                'errorExpected' => null,
                'customRules' => [],
                'customException' => [],
                'expectedResult' => null
            ],
            // null body, required param
            [
                'body' => null,
                'rules' => [
                    'paramName' => 'required'
                ],
                'errorExpected' => MissingRequiredParametersException::class,
                'customRules' => [],
                'customException' => [],
                'expectedResult' => null
            ],
            // null body, default param
            [
                'body' => null,
                'rules' => [
                    'paramName' => 'default:value1|in:value1,value2,value3'
                ],
                'errorExpected' => null,
                'customRules' => [],
                'customException' => [],
                'expectedResult' => ['paramName' => 'value1']
            ]
        ];
    }
}
