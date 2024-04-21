<?php

namespace Serato\SwsApp\Validation;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Interface RequestValidationInterface
 * @package App\Service\RequestValidation
 */
interface RequestValidationInterface
{
    public function validateRequestData(
        Request $request,
        array $validationRules,
        array $customRules = [],
        array $exceptions = []
    ): array;
}
