<?php

namespace Serato\SwsApp\Validation;

use Serato\SwsApp\Exception\MissingRequiredParametersException;
use Serato\SwsApp\Exception\InvalidRequestParametersException;
use Serato\SwsApp\Exception\InvalidTagRequestParametersException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rakit\Validation\Validator;

/**
 * Class RequestValidation
 * @package App\Validation\RequestValidation
 */
class RequestValidation implements RequestValidationInterface
{
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
                    throw new $exception('', $request);
                }
            }

            $invalid[] = implode('. ', $error);
        }

        if (!empty($required)) {
            throw new MissingRequiredParametersException('', $request, $required);
        }

        if (!empty($invalid)) {
            $errors = implode('. ', $invalid);
            if (strpos($errors, 'not valid format') !== false) {
                throw new InvalidTagRequestParametersException($errors, $request);
            }
            throw new InvalidRequestParametersException($errors, $request);
        }
    }
}
