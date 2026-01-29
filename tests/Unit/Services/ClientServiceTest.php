<?php

namespace laravelKeycloakAdminRestApi\Tests\Unit\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use laravelKeycloakAdminRestApi\Services\Client;
use laravelKeycloakAdminRestApi\Tests\TestCase;
use Mockery;

class ClientServiceTest extends TestCase
{
    protected ClientAuthService $mockAuth;
    protected ClientInterface $mockHttp;
    protected Client $clientService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAuth = Mockery::mock(ClientAuthService::class);
        $this->mockAuth->shouldReceive('getToken')
            ->andReturn('test-token');

        $this->mockHttp = Mockery::mock(ClientInterface::class);

        $this->clientService = new Client($this->mockAuth, $this->mockHttp);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_all_clients(): void
    {
        $clients = [
            ['id' => 'client-1', 'clientId' => 'my-app'],
            ['id' => 'client-2', 'clientId' => 'another-app']
        ];

        $response = new Response(200, [], json_encode($clients));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/clients$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientService->all([]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('my-app', $result[0]['clientId']);
    }

    public function test_get_client_by_id(): void
    {
        $client = ['id' => 'client-123', 'clientId' => 'my-app', 'name' => 'My Application'];
        $response = new Response(200, [], json_encode($client));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/clients\/client-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientService->get(['id' => 'client-123']);

        $this->assertEquals('my-app', $result['clientId']);
        $this->assertEquals('My Application', $result['name']);
    }

    public function test_get_client_by_client_id(): void
    {
        $clients = [
            ['id' => 'client-1', 'clientId' => 'my-app'],
            ['id' => 'client-2', 'clientId' => 'another-app']
        ];

        $response = new Response(200, [], json_encode($clients));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/clients$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientService->getByClientId('my-app');

        $this->assertEquals('client-1', $result['id']);
        $this->assertEquals('my-app', $result['clientId']);
    }

    public function test_get_client_by_client_id_returns_null_when_not_found(): void
    {
        $clients = [
            ['id' => 'client-1', 'clientId' => 'my-app']
        ];

        $response = new Response(200, [], json_encode($clients));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/clients$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientService->getByClientId('non-existent');

        $this->assertNull($result);
    }

    public function test_create_client_returns_created_client(): void
    {
        $clientId = 'new-client-uuid';
        $locationHeader = "http://localhost:8080/auth/admin/realms/master/clients/{$clientId}";
        
        // Create response with location header
        $createResponse = new Response(201, ['location' => $locationHeader], '');
        
        // Get response returns the client
        $client = ['id' => $clientId, 'clientId' => 'new-app'];
        $getResponse = new Response(200, [], json_encode($client));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/clients$/'), Mockery::type('array'))
            ->andReturn($createResponse);

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern("/\/clients\/{$clientId}$/"), Mockery::type('array'))
            ->andReturn($getResponse);

        $result = $this->clientService->create([
            'body' => ['clientId' => 'new-app', 'enabled' => true]
        ]);

        $this->assertEquals($clientId, $result['id']);
        $this->assertEquals('new-app', $result['clientId']);
    }

    public function test_update_client(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/clients\/client-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientService->update([
            'id' => 'client-123',
            'body' => ['name' => 'Updated App Name']
        ]);

        $this->assertTrue($result);
    }

    public function test_delete_client(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/clients\/client-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientService->delete(['id' => 'client-123']);

        $this->assertTrue($result);
    }

    public function test_response_handles_json_response(): void
    {
        $clientData = ['id' => 'client-123', 'clientId' => 'my-app'];
        $response = new Response(200, [], json_encode($clientData));

        $result = $this->clientService->response($response);

        $this->assertEquals($clientData, $result);
    }

    public function test_response_handles_empty_response(): void
    {
        $response = new Response(204, [], '');

        $result = $this->clientService->response($response);

        $this->assertTrue($result);
    }
}

