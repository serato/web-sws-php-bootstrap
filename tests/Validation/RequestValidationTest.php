<?php

namespace Serato\SwsApp\Test\Validation;

use Mockery;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rakit\Validation\RuleNotFoundException;
use Rakit\Validation\Rules\Numeric;
use Rakit\Validation\Rules\Regex;
use Serato\SwsApp\Validation\Rules\NoHtmlTag;
use Serato\SwsApp\Exception\InvalidRequestParametersException;
use Serato\SwsApp\Exception\BadRequestContainHTMLTagsException;
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

    #[\Override]
    protected function setUp()
    {
        $this->validation = new RequestValidation();
        $this->requestMock = Mockery::mock(Request::class);
    }

    /**
     * @dataProvider dataProvider
     *
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

    public function dataProvider(): array
    {
        $paramStartWithARule = new Regex();
        $paramStartWithARule->setParameter('regex', '/^a/');
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
            // valid params without html tags throw no error
            [
                'body' => [
                    'paramName' => 'br'
                ],
                'rules' => [
                    'paramName' =>  NoHtmlTag::NO_HTML_TAG_RULE
                ],
                'errorExpected' => null
            ],
            // invalid params with html tags but no `no_html_tag` specified not throw error
            [
              'body' => [
                  'paramName' => '<br>'
              ],
              'rules' => [
                  'paramName' => 'required'
              ],
              'errorExpected' => null
            ],
            // invalid params contains html tags throw BadRequestContainHTMLTagsException
            [
                'body' => [
                    'paramName' => '<br>'
                ],
                'rules' => [
                    'paramName' => NoHtmlTag::NO_HTML_TAG_RULE,
                ],
                'errorExpected' => BadRequestContainHTMLTagsException::class,
            ],
            // invalid params contains html tags throw BadRequestContainHTMLTagsException 2
            [
                'body' => [
                    'paramName' => '<a>test</a>'
                ],
                'rules' => [
                    'paramName' => NoHtmlTag::NO_HTML_TAG_RULE
                ],
                'errorExpected' => BadRequestContainHTMLTagsException::class
            ],
            // invalid params contains html tags throw BadRequestContainHTMLTagsException 3
            [
                'body' => [
                    'paramName' => '<fake></fake>'
                ],
                'rules' => [
                    'paramName' => NoHtmlTag::NO_HTML_TAG_RULE
                ],
                'errorExpected' => BadRequestContainHTMLTagsException::class
            ],
            // invalid params contains html tags throw BadRequestContainHTMLTagsException 4
            [
                'body' => [
                    'paramName' => 'test</a>'
                ],
                'rules' => [
                    'paramName' => NoHtmlTag::NO_HTML_TAG_RULE
                ],
                'errorExpected' => BadRequestContainHTMLTagsException::class
            ],
            // invalid params contains invalid format throws InvalidRequestParametersException
            [
                'body' => [
                    'paramName' => '<br>'
                ],
                'rules' => [
                    'paramName' => 'start_with_a'
                ],
                'errorExpected' => InvalidRequestParametersException::class,
                'customRules' => [
                    'start_with_a' => $paramStartWithARule
                ],
                'customException' => [
                    'start_with_a' => InvalidRequestParametersException::class
                ]
            ],
            // invalid params contains invalid format and html tags throws InvalidRequestParametersException
            [
                'body' => [
                    'paramName' => '<br>',
                    'paramName2' => '<a>'
                ],
                'rules' => [
                    'paramName' => 'start_with_a',
                    'paramName2' => NoHtmlTag::NO_HTML_TAG_RULE
                ],
                'errorExpected' => InvalidRequestParametersException::class,
                'customRules' => [
                  'start_with_a' => $paramStartWithARule
                ],
                'customException' => [
                    'start_with_a' => InvalidRequestParametersException::class
                ]
            ],
            // custom rule
            [
                'body' => [
                    'paramName' => '1'
                ],
                'rules' => [
                    'paramName' => 'required|is_numeric'
                ],
                'errorExpected' => null,
                'customRules' => [
                    'is_numeric' => new Numeric()
                ]
            ],
            // custom rule and invalid params contains html tags not excepting errors
            [
                'body' => [
                    'paramName' => '1',
                    'paramNam2' => '<a>'
                ],
                'rules' => [
                    'paramName' => 'required|is_numeric',
                    'paramName2' => NoHtmlTag::NO_HTML_TAG_RULE
                ],
                'errorExpected' => null,
                'customRules' => [
                    'is_numeric' => new Numeric()
                ],
                'customException' => [
                    'is_numeric' => UnsupportedContentTypeException::class
                ]
            ],
            // custom exception
            [
                'body' => [
                    'paramName' => 'invalid-number'
                ],
                'rules' => [
                    'paramName' => 'required|is_numeric'
                ],
                'errorExpected' => UnsupportedContentTypeException::class,
                'customRules' => [
                    'is_numeric' => new Numeric()
                ],
                'customException' => [
                    'is_numeric' => UnsupportedContentTypeException::class
                ]
            ],
            // custom exception and invalid params contains html tags throws UnsupportedContentTypeException
            [
                'body' => [
                    'paramName' => 'invalid-number',
                    'paramName2' => '<br>'
                ],
                'rules' => [
                    'paramName' => 'required|is_numeric',
                    'paramName2' => NoHtmlTag::NO_HTML_TAG_RULE
                ],
                'errorExpected' => UnsupportedContentTypeException::class,
                'customRules' => [
                    'is_numeric' => new Numeric()
                ],
                'customException' => [
                    'is_numeric' => UnsupportedContentTypeException::class,
                ]
            ],
            // custom exception and invalid params contains html tags throws BadRequestContainHTMLTagsException
            // (params order changed)
            [
                'body' => [
                    'paramName' => '<br>',
                    'paramName2' => 'invalid-number',
                ],
                'rules' => [
                    'paramName' => NoHtmlTag::NO_HTML_TAG_RULE,
                    'paramName2' => 'required|is_numeric',
                ],
                'errorExpected' => BadRequestContainHTMLTagsException::class,
                'customRules' => [
                    'is_numeric' => new Numeric()
                ],
                'customException' => [
                    'is_numeric' => UnsupportedContentTypeException::class
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
