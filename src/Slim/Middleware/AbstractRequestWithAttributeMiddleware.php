<?php

namespace Serato\SwsApp\Slim\Middleware;

/**
 * *Abstract RequestWithAttribute Middleware*
 *
 * An abstract class which defines constants whose values are used to extend
 * the data passed through an application via a `RequestInterface` instance.
 *
 * The `Psr\Http\Message\RequestInterface` provides a `withAttribute` method
 * that allows for arbitrary data to be injected into a `RequestInterface`
 * instance and made available throughout an application.
 *
 * Middleware is typically used to extend the `RequestInterface` instance in
 * this way.
 *
 * This abstract class simply defines names for request attributes added by
 * middleware.
 */
abstract class AbstractRequestWithAttributeMiddleware
{
    public const APP_ID              = 'app_id';
    public const APP_NAME            = 'app_name';
    public const SCOPES              = 'scopes';
    public const USER_ID             = 'uid';
    public const USER_EMAIL          = 'email';
    public const USER_EMAIL_VERIFIED = 'email_verified';
    public const REFRESH_TOKEN_ID    = 'rtid';
}
