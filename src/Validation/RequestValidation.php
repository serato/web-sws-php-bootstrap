<?php

namespace Serato\SwsApp\Validation;

use Serato\SwsApp\Exception\MissingRequiredParametersException;
use Serato\SwsApp\Exception\InvalidRequestParametersException;
use Serato\SwsApp\Exception\BadRequestContainHTMLTagsException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rakit\Validation\Validator;
use Rakit\Validation\Rules\Regex;

/**
 * Class RequestValidation
 * @package App\Validation\RequestValidation
 */
class RequestValidation implements RequestValidationInterface
{
    /**
      * Validation rule name for params without HTML tags.
      * @var string
    */
    public const NO_HTML_TAG_RULE = 'no_html_tag';

    /**
      * Regex validation rule for params without HTML tags.
      * @var string
    */
    public const NO_HTML_TAG_REGEX = '/^(?:(?!<[^>]*$)[^<])*$/';

    /**
     * @param Request $request
     * @param array $validationRules
     * @param array $customRules
     * @param array $exceptions
     */
    public function validateRequestData(
        Request $request,
        array $validationRules,
        array $customRules = [],
        array $exceptions = []
    ): array {
        $requestBody = $request->getParsedBody() ?? [];
        $validator   = new Validator();

        // Add a custom validation rule and exceptions when the `no_html_tag` validation rule is specified for a param.
        // to prevent the need to include the Rakit Regex class in other services.
        if (in_array(self::NO_HTML_TAG_RULE, $validationRules)) {
            $noHtmlTagRule = new Regex();
            $noHtmlTagRule->setParameter('regex', RequestValidation::NO_HTML_TAG_REGEX);
            $customRules[self::NO_HTML_TAG_RULE] = $noHtmlTagRule;
            $exceptions[self::NO_HTML_TAG_RULE] = BadRequestContainHTMLTagsException::class;
        }

        // Add custom validation rules
        if (!empty($customRules)) {
            foreach ($customRules as $key => $customRule) {
                $validator->addValidator($key, $customRule);
            }
        }

        $validation  = $validator->make($requestBody, $validationRules);

        // set aliases
        foreach ($validationRules as $ruleKey => $ruleVal) {
            $validation->setAlias($ruleKey, '`' . $ruleKey . '`');
        }

        $validation->validate();
        if (!$validation->fails()) {
            return $validation->getValidatedData();
        }

        $required = [];
        $invalid  = [];
        $errors   = $validation->errors()->toArray();
        foreach ($errors as $key => $error) {
            if (!empty($error['required'])) {
                $required[] = $key;
                continue;
            }

            foreach ($exceptions as $exceptionKey => $exception) {
                if (!empty($error[$exceptionKey])) {
                    throw new $exception($error[$exceptionKey], $request);
                }
            }

            $invalid[] = implode('. ', $error);
        }

        if (!empty($required)) {
            throw new MissingRequiredParametersException('', $request, $required);
        }
        if (!empty($invalid)) {
            $errors = implode('. ', $invalid);
            throw new InvalidRequestParametersException($errors, $request);
        }
    }
}
