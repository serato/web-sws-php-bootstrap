<?php
namespace Serato\SwsApp\Test\Slim\Controller;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Controller\Scopes;

/**
 * Unit tests for Serato\SwsApp\Slim\Controller\Scopes
 */
class ScopesTest extends TestCase
{
    public function testSmokeTest()
    {
        $items = ['scope1', 'scope2', 'scope3'];

        $scopes = Scopes::create();

        foreach ($items as $item) {
            $scopes->addScope($item);
        }

        $this->assertEquals($items, $scopes->getScopes());

        $scopes->addScopes(['scope4', 'scope5']);

        $this->assertEquals(
            ['scope1', 'scope2', 'scope3', 'scope4', 'scope5'],
            $scopes->getScopes()
        );
    }
}
