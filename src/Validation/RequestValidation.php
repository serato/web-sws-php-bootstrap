<?php

namespace Serato\SwsApp\Validation;

use App\Exception\MissingRequiredParametersException;
use App\Exception\RequestValidation\InvalidRequestParametersException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rakit\Validation\Validator;

/**
 * Class RequestValidation
 * @package App\Validation\RequestValidation
 */
class RequestValidation
{
    /**
     * @param Request $request
     * @param array $validationRules
     */
    public function validateRequestData(Request $request, array $validationRules): void
    {
        $requestBody = $request->getParsedBody();
        $validator   = new Validator();
        $validation  = $validator->make($requestBody, $validationRules);

        // set aliases
        foreach ($validationRules as $ruleKey => $ruleVal) {
            $validation->setAlias($ruleKey, '`' . $ruleKey . '`');
        }

        $validation->validate();
        if (!$validation->fails()) {
            return;
        }

        $required = [];
        $invalid  = [];
        $errors   = $validation->errors()->toArray();
        foreach ($errors as $key => $error) {
            if (!empty($error['required'])) {
                $required[] = $key;
                continue;
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
