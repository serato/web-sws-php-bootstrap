<?php

namespace Serato\SwsApp\Http\Rest\Exception;

use Serato\SwsApp\Http\Rest\Exception\AbstractPermissionException;

/**
 * Expired Access Token Exception
 *
 * The client has provided an expired access token.
 */
class ExpiredAccessTokenException extends AbstractPermissionException
{
    protected $http_response_code = 401;
    protected $code = 2002;
    protected $message = 'Unauthorized. This can happen if an access token has expired. ' .
                            'You should request a new  access token using the refresh ' .
                            'token, or re-authenticate the user.';
}
