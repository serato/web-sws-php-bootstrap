<?php

namespace Serato\SwsApp\Validation;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Interface RequestValidationInterface
 * @package App\Service\RequestValidation
 */
interface RequestValidationInterface
{
    /**
     * @param Request $request
     * @param array $validationRules
     * @param array $customRules
     * @param array $exceptions
     *
     */
    public function validateRequestData(
        Request $request,
        array $validationRules,
        array $customRules = [],
        array $exceptions = []
    ): void;
}
