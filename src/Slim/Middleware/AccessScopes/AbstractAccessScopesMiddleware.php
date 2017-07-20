<?php
namespace Serato\SwsApp\Slim\Middleware\AccessScopes;

use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class AbstractAccessScopesMiddleware extends AbstractHandler
{
    const REQUEST_ATTRIBUTE_APP_ID = 'app_id';
    const REQUEST_ATTRIBUTE_APP_NAME = 'app_name';
    const REQUEST_ATTRIBUTE_SCOPES = 'scopes';

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
            ->withAttribute(self::REQUEST_ATTRIBUTE_APP_ID, $appId)
            ->withAttribute(self::REQUEST_ATTRIBUTE_APP_NAME, $appName)
            ->withAttribute(self::REQUEST_ATTRIBUTE_SCOPES, $scopes);
    }
}
