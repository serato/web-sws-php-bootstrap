<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractPermissionException;

/**
 * Invalid Access Token Exception
 *
 * The client has failed to provide valid access token data.
 */
class InvalidAccessTokenException extends AbstractPermissionException
{
    protected $code = 2001;
    protected $message = 'Forbidden. Unable to create a valid access token from the authorization data provided.';

    // Do we need more granular access token exceptions?
    // ie. Map more of the JWT exceptions into specific error codes??
}
