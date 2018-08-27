<?php
namespace Serato\SwsApp\Test\Slim\Middleware;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Slim\Middleware\AccessScopes\ClientAppBasicAuth;
use Serato\SwsApp\Slim\Middleware\EmptyWare;
use Serato\Slimulator\EnvironmentBuilder;
use Serato\Slimulator\Authorization\BasicAuthorization;
use Serato\Slimulator\Request;
use Slim\Http\Response;
use Serato\SwsApp\Slim\Middleware\AbstractRequestWithAttributeMiddleware as RequestMiddleware;

/**
 * Unit tests for Serato\SwsApp\Slim\Middleware\AccessScopes\ClientAppBasicAuth
 */
class ClientAppBasicAuthTest extends TestCase
{
    /**
     * @dataProvider clientAppBasicAuthProvider
     */
    public function testClientAppBasicAuth(
        string $basicAuthUser,
        string $basicAuthPass,
        string $webServiceName,
        array $clientAppList,
        array $requestScopes,
        string $assertMessage
    ) {
        $middleware = new ClientAppBasicAuth($webServiceName, $clientAppList);
        $nextMiddleware = new EmptyWare;
        
        $envBuilder = EnvironmentBuilder::create();
        // Add Basic auth header if user and password are provided
        if ($basicAuthUser !== '' && $basicAuthPass !== '') {
            $envBuilder = $envBuilder->setAuthorization(
                BasicAuthorization::create($basicAuthUser, $basicAuthPass)
            );
        }

        $response = $middleware(
            Request::createFromEnvironmentBuilder($envBuilder),
            new Response,
            $nextMiddleware
        );

        $this->assertEquals(200, $response->getStatusCode(), $assertMessage);
        $this->assertEquals(
            $requestScopes,
            $nextMiddleware->getRequestInterface()->getAttribute(RequestMiddleware::SCOPES, []),
            $assertMessage
        );
    }

    public function clientAppBasicAuthProvider()
    {
        return [
            ['', '', 'my_web_service', [], [], 'No Basic auth user'],
            [
                'app_xx',
                'app_xx_password',
                'my_web_service_1',
                $this->getClientAppData(),
                [],
                'No matching app in client list'
            ],
            [
                'app_2',
                'app_2_password',
                'my_web_service_1',
                $this->getClientAppData(),
                [],
                'No `name` key for app in client list'
            ],
            [
                'app_3',
                'app_3_password',
                'my_web_service_1',
                $this->getClientAppData(),
                [],
                'No `scopes` key for app in client list'
            ],
            [
                'app_1',
                'app_1_password',
                'my_web_service_XX',
                $this->getClientAppData(),
                [],
                'No scopes found for given web service name'
            ],
            [
                'app_1',
                'app_1_password_wrong',
                'my_web_service_1',
                $this->getClientAppData(),
                [],
                'Invalid basic auth password'
            ],
            [
                'app_1',
                'app_1_password',
                'my_web_service_1',
                $this->getClientAppData(),
                ['test-scope1'],
                'Valid auth user, check scopes web_service_1'
            ],
            [
                'app_1',
                'app_1_password',
                'my_web_service_2',
                $this->getClientAppData(),
                ['test-scope2', 'test-scope3'],
                'Valid auth user, check scopes web_service_2'
            ]
        ];
    }

    protected function getClientAppData()
    {
        return [
            // Valid app data
            'app_1' => [
                'id' => 'app_1',
                'name' => 'Test App 1',
                'password_hash' => password_hash('app_1_password', PASSWORD_DEFAULT),
                'scopes' => [
                    'my_web_service_1' => ['test-scope1'],
                    'my_web_service_2' => ['test-scope2', 'test-scope3']
                ]
            ],
            // Missing `name` key in array
            'app_2' => [
                'id' => 'app_2',
                'password_hash' => password_hash('app_2_password', PASSWORD_DEFAULT),
                'scopes' => [
                    'my_web_service_1' => ['test-scope1'],
                    'my_web_service_2' => ['test-scope2', 'test-scope3']
                ]
            ],
            // Missing `scopes` key in array
            'app_3' => [
                'id' => 'app_3',
                'name' => 'Test App 3',
                'password_hash' => password_hash('app_3_password', PASSWORD_DEFAULT)
            ],
        ];
    }
}
