<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractPermissionException;

/**
 * Client User Grants Exception
 *
 * The client is attempting to perform an operation for which it has
 * insufficient permissions granted.
 */
class ClientUserGrantsException extends AbstractPermissionException
{
    protected $code = 2000;
    protected $message = 'Access denied. Invalid grants.';
}
