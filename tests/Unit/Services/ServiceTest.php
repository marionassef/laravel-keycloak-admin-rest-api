<?php

namespace laravelKeycloakAdminRestApi\Tests\Unit\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use laravelKeycloakAdminRestApi\Services\Service;
use laravelKeycloakAdminRestApi\Tests\TestCase;
use Mockery;

class ServiceTest extends TestCase
{
    protected ClientAuthService $mockAuth;
    protected ClientInterface $mockHttp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAuth = Mockery::mock(ClientAuthService::class);
        $this->mockAuth->shouldReceive('getToken')
            ->andReturn('test-token');

        $this->mockHttp = Mockery::mock(ClientInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_options_includes_authorization_header(): void
    {
        $service = $this->createConcreteService();
        $options = $service->createOptions([]);

        $this->assertArrayHasKey('headers', $options);
        $this->assertEquals('Bearer test-token', $options['headers']['Authorization']);
        $this->assertEquals('application/json', $options['headers']['Content-Type']);
    }

    public function test_create_options_includes_json_body_when_provided(): void
    {
        $service = $this->createConcreteService();
        $body = ['username' => 'testuser', 'email' => 'test@example.com'];
        $options = $service->createOptions(['body' => $body]);

        $this->assertArrayHasKey('json', $options);
        $this->assertEquals($body, $options['json']);
    }

    public function test_create_options_includes_search_query_param(): void
    {
        $service = $this->createConcreteService();
        $options = $service->createOptions(['search' => 'test-search']);

        $this->assertArrayHasKey('query', $options);
        $this->assertEquals(['search' => 'test-search'], $options['query']);
    }

    public function test_create_options_includes_custom_query_params(): void
    {
        $service = $this->createConcreteService();
        $queryParams = ['first' => 0, 'max' => 10, 'search' => 'john'];
        $options = $service->createOptions(['query' => $queryParams]);

        $this->assertArrayHasKey('query', $options);
        $this->assertEquals($queryParams, $options['query']);
    }

    public function test_init_api_replaces_url_placeholders(): void
    {
        $service = $this->createConcreteService([
            'get' => [
                'api' => 'http://localhost/users/{id}',
                'method' => 'get'
            ]
        ]);

        [$url, $method] = $service->initApi('get', ['id' => 'user-123']);

        $this->assertEquals('http://localhost/users/user-123', $url);
        $this->assertEquals('get', $method);
    }

    public function test_init_api_replaces_multiple_placeholders(): void
    {
        $service = $this->createConcreteService([
            'addRole' => [
                'api' => 'http://localhost/users/{userId}/roles/{roleId}',
                'method' => 'put'
            ]
        ]);

        [$url, $method] = $service->initApi('addRole', [
            'userId' => 'user-123',
            'roleId' => 'role-456'
        ]);

        $this->assertEquals('http://localhost/users/user-123/roles/role-456', $url);
        $this->assertEquals('put', $method);
    }

    public function test_get_api_delegates_to_init_api(): void
    {
        $service = $this->createConcreteService([
            'get' => [
                'api' => 'http://localhost/users/{id}',
                'method' => 'get'
            ]
        ]);

        $result = $service->getApi('get', ['id' => 'user-123']);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('http://localhost/users/user-123', $result[0]);
        $this->assertEquals('get', $result[1]);
    }

    public function test_magic_call_makes_http_request(): void
    {
        $service = $this->createConcreteService([
            'all' => [
                'api' => 'http://localhost/users',
                'method' => 'get'
            ]
        ]);

        $responseData = [
            ['id' => '1', 'username' => 'user1'],
            ['id' => '2', 'username' => 'user2']
        ];
        $response = new Response(200, [], json_encode($responseData));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', 'http://localhost/users', Mockery::type('array'))
            ->andReturn($response);

        $result = $service->all([]);

        $this->assertEquals($responseData, $result);
    }

    public function test_magic_call_with_parameters(): void
    {
        $service = $this->createConcreteService([
            'get' => [
                'api' => 'http://localhost/users/{id}',
                'method' => 'get'
            ]
        ]);

        $responseData = ['id' => 'user-123', 'username' => 'testuser'];
        $response = new Response(200, [], json_encode($responseData));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', 'http://localhost/users/user-123', Mockery::type('array'))
            ->andReturn($response);

        $result = $service->get(['id' => 'user-123']);

        $this->assertEquals($responseData, $result);
    }

    protected function createConcreteService(array $api = []): Service
    {
        $mockAuth = $this->mockAuth;
        $mockHttp = $this->mockHttp;

        return new class($mockAuth, $mockHttp, $api) extends Service {
            public function __construct(ClientAuthService $auth, ClientInterface $http, array $api)
            {
                parent::__construct($auth, $http);
                $this->api = $api;
            }

            public function response($response)
            {
                return json_decode($response->getBody()->getContents(), true) ?: true;
            }
        };
    }
}

