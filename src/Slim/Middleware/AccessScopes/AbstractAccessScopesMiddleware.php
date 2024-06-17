<?php

namespace Serato\SwsApp\Slim\Middleware\AccessScopes;

use Serato\SwsApp\Slim\Middleware\AbstractRequestWithAttributeMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class AbstractAccessScopesMiddleware extends AbstractRequestWithAttributeMiddleware
{
    /**
     * Set `request` attributes pertaining to scopes of access
     *
     * @param Request   $request    PSR-7 request interface
     * @param string    $appId      Client application ID
     * @param string    $appName    Client application name
     * @param array     $scopes     Permitted scopes of access
     *
     * @return Request
     */
    protected function setClientAppRequestAttributes(
        Request $request,
        string $appId,
        string $appName,
        array $scopes
    ): Request {
        return $request
            ->withAttribute(self::APP_ID, $appId)
            ->withAttribute(self::APP_NAME, $appName)
            ->withAttribute(self::SCOPES, $scopes);
    }
}
