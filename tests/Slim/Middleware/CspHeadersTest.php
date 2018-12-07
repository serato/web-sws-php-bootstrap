<?php
namespace Serato\SwsApp\Test\Slim\Middleware;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Middleware\CspHeaders;
use Serato\SwsApp\Slim\Middleware\EmptyWare;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Request;
use Slim\Http\Response;

/**
 * Unit tests for Serato\SwsApp\Slim\Middleware\CspHeaders
 */
class CspHeadersTest extends TestCase
{
    public function test()
    {
        $middleware = new CspHeaders;
        $emptyMiddleware = new EmptyWare;

        $response = new Response;
        $response->getBody()->write($this->getHtmlBody());

        $response = $middleware(
            Request::createFromEnvironmentBuilder(EnvironmentBuilder::create()),
            $response,
            $emptyMiddleware
        );

        $nonces = [];

        // Iterate over the "HTML" line by line and extract the "nonce" attribute from any
        // element to which it has been added
        $htmls = explode("\n", (string)$response->getBody());
        foreach ($htmls as $html) {
            preg_match('/nonce="(.*?)"/', $html, $matches);
            if (count($matches) > 0) {
                $nonces[] = $matches[1];
            }
        }

        // We should have 3 nonce values
        $this->assertEquals(3, count($nonces));

        // Make sure we actually have a `Content-Security-Policy` header in the response
        $headers = $response->getHeaders();
        $this->assertTrue(isset($headers['Content-Security-Policy']));
        $this->assertTrue(isset($headers['Content-Security-Policy'][0]));

        // Now make sure we have nonce values extracted from the HTML in the CSP header value
        $cspHeaderValue = $headers['Content-Security-Policy'][0];
        foreach ($nonces as $nonce) {
            $this->assertTrue(strpos($cspHeaderValue, 'nonce-' . $nonce) !== false);
        }
    }

    private function getHtmlBody(): string
    {
        // HTML to which 3 nonces should be added by CSP middleware
        return <<<EOT
<div>A div tag</div>
<script></script>       // This tag should have a `nonce` attribute added
<div>A div tag</div>
<link rel="stylesheet"> // This tag should have a `nonce` attribute added
<div>A div tag</div>
<h1>A heading</h1>
<style></style>         // This tag should have a `nonce` attribute added
<div>A div tag</div>
EOT;
    }
}
