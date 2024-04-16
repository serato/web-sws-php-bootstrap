<?php

namespace Serato\SwsApp\Slim\Middleware;

use Slim\Handlers\AbstractHandler;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Ramsey\Uuid\Uuid;

class CspHeaders extends AbstractHandler
{
    /* @var boolean */
    protected $addScriptNonce = true;
    /* @var boolean */
    protected $addStyleNonce = true;

    /**
     * Invoke the middleware
     *
     * @param Request $request The most recent Request object
     * @param Response $response The most recent Response object
     *
     * @return callable
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $response = $next($request, $response);

        $responseBody = (string)$response->getBody();

        $cspSettings = $this->getBaseCspSettings();

        $uuid = Uuid::uuid4();
        $scriptNonce = $uuid->toString();

        $uuid = Uuid::uuid4();
        $styleNonce = $uuid->toString();

        if ($this->addScriptNonce) {
            # Prepend a nonce to the `script-src` directive...
            array_unshift($cspSettings['script-src'], "'nonce-" . $scriptNonce . "'", "'strict-dynamic'");
            # ...and add the nonce to <script> tags.
            $responseBody = str_replace('<script', '<script nonce="' . $scriptNonce . '"', $responseBody);
        }

        if ($this->addStyleNonce) {
            # Prepend a nonce to the `style-src` directive...
            array_unshift($cspSettings['style-src'], "'nonce-" . $styleNonce . "'", "'strict-dynamic'");
            # ...and add the nonce to <style> tags...
            $responseBody = str_replace('<style', '<style nonce="' . $styleNonce . '"', $responseBody);

            # and also add `style` nonce to <link rel="stylesheet"> tags.
            # Note: this won't work if the <link> tag contains line breaks
            preg_match_all('/<link.+?rel="stylesheet".*?>/', $responseBody, $matches);
            if (is_array($matches) && isset($matches[0]) && is_array($matches[0])) {
                foreach ($matches[0] as $linkTag) {
                    $responseBody = str_replace(
                        $linkTag,
                        str_replace('<link', '<link nonce="' . $styleNonce . '"', (string) $linkTag),
                        $responseBody
                    );
                }
            }
        }

        $response->getBody()->rewind();
        $response->getBody()->write($responseBody);

        // Add CSP heaser
        return $response->withHeader('Content-Security-Policy', $this->makeCspHeaderString($cspSettings));
    }

    /**
     * Defines the base CSP settings.
     *
     * The key of each array item corresponds to a CSP directive name and the value of each
     * item is an array of values for the directive.
     */
    protected function getBaseCspSettings(): array
    {
        return [
            'default-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'img-src' => [
                'https://static.serato.com',
                'https://m.cdn.sera.to',
                'https://u.cdn.sera.to',
                'https://www.google-analytics.com',
                'data:',
                'https://www.facebook.com',
                'https://stats.g.doubleclick.net',
                'https://optimize.google.com',
                'https://www.googletagmanager.com'
            ],
            'font-src' => [
                'https://static.serato.com',
                'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/',
                'https://fonts.gstatic.com'
            ],
            // Note for `script-src` and `style-src`: these values are appended after a nonce.
            'script-src' => [
                "'self'",
                'https://static.serato.com',
                'https://www.google.com',
                'https://www.gstatic.com',
                'https://www.googletagmanager.com',
                'https://www.google-analytics.com',
                'https://optimize.google.com',
                'https://connect.facebook.net',
                'https://prism.app-us1.com',
                'https://bat.bing.com'
            ],
            'style-src' => [
                "'self'",
                'https://static.serato.com',
                'https://www.gstatic.com',
                'https://maxcdn.bootstrapcdn.com/font-awesome/',
                'https://optimize.google.com',
                'https://fonts.googleapis.com'
            ],
            'frame-src' => [
                'https://www.google.com/recaptcha/',
                'https://vars.hotjar.com/',
                'https://optimize.google.com'
            ],
            'connect-src' => [
                'https://www.google-analytics.com'
            ]
        ];
    }

    /**
     * Flattens an array of CSP settings into a string suitable for use as the value
     * of a `Content-Security-Policy` HTTP header
     *
     * @param array $cspSettings    Array of CSP settings
     */
    private function makeCspHeaderString(array $cspSettings): string
    {
        $directives = [];
        foreach ($cspSettings as $name => $value) {
            $directives[] = $name . ' ' . implode(' ', $value);
        }
        return implode('; ', $directives);
    }
}
