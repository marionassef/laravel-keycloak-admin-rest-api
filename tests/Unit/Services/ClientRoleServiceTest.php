<?php

namespace laravelKeycloakAdminRestApi\Tests\Unit\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use laravelKeycloakAdminRestApi\Services\ClientRole;
use laravelKeycloakAdminRestApi\Tests\TestCase;
use Mockery;

class ClientRoleServiceTest extends TestCase
{
    protected ClientAuthService $mockAuth;
    protected ClientInterface $mockHttp;
    protected ClientRole $clientRoleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAuth = Mockery::mock(ClientAuthService::class);
        $this->mockAuth->shouldReceive('getToken')
            ->andReturn('test-token');

        $this->mockHttp = Mockery::mock(ClientInterface::class);

        $this->clientRoleService = new ClientRole($this->mockAuth, $this->mockHttp);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_all_client_roles(): void
    {
        $roles = [
            ['id' => 'role-1', 'name' => 'view-profile'],
            ['id' => 'role-2', 'name' => 'manage-account']
        ];

        $response = new Response(200, [], json_encode($roles));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/clients\/client-123\/roles$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientRoleService->all(['id' => 'client-123']);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('view-profile', $result[0]['name']);
    }

    public function test_get_client_role_by_name(): void
    {
        $role = ['id' => 'role-123', 'name' => 'admin', 'clientRole' => true];
        $response = new Response(200, [], json_encode($role));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/clients\/client-123\/roles\/admin$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientRoleService->getByName([
            'id' => 'client-123',
            'role' => 'admin'
        ]);

        $this->assertEquals('admin', $result['name']);
        $this->assertTrue($result['clientRole']);
    }

    public function test_create_client_role_returns_created_role(): void
    {
        // UUID must be exactly 36 characters for the response handler's substr extraction
        $clientId = '12345678-1234-1234-1234-123456789012';
        $roleName = 'new-role';
        $locationHeader = "http://localhost:8080/auth/admin/realms/master/clients/{$clientId}/roles/{$roleName}";
        
        // Create response with location header
        $createResponse = new Response(201, ['location' => $locationHeader], '');
        
        // Get response returns the role
        $role = ['id' => 'role-uuid', 'name' => $roleName, 'clientRole' => true];
        $getResponse = new Response(200, [], json_encode($role));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern("/\/clients\/{$clientId}\/roles$/"), Mockery::type('array'))
            ->andReturn($createResponse);

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern("/\/clients\/{$clientId}\/roles\/{$roleName}$/"), Mockery::type('array'))
            ->andReturn($getResponse);

        $result = $this->clientRoleService->create([
            'id' => $clientId,
            'body' => ['name' => $roleName, 'description' => 'A new client role']
        ]);

        $this->assertEquals($roleName, $result['name']);
        $this->assertTrue($result['clientRole']);
    }

    public function test_update_client_role(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/clients\/client-123\/roles\/admin$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientRoleService->update([
            'id' => 'client-123',
            'role' => 'admin',
            'body' => ['description' => 'Updated description']
        ]);

        $this->assertTrue($result);
    }

    public function test_delete_client_role(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/clients\/client-123\/roles\/admin$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientRoleService->delete([
            'id' => 'client-123',
            'role' => 'admin'
        ]);

        $this->assertTrue($result);
    }

    public function test_add_composites_to_role(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/clients\/client-123\/roles\/admin\/composites$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->clientRoleService->composites([
            'id' => 'client-123',
            'role' => 'admin',
            'body' => [
                ['id' => 'role-1', 'name' => 'sub-role-1'],
                ['id' => 'role-2', 'name' => 'sub-role-2']
            ]
        ]);

        $this->assertTrue($result);
    }

    public function test_response_handles_json_response(): void
    {
        $roleData = ['id' => 'role-123', 'name' => 'admin', 'clientRole' => true];
        $response = new Response(200, [], json_encode($roleData));

        $result = $this->clientRoleService->response($response);

        $this->assertEquals($roleData, $result);
    }

    public function test_response_handles_empty_response(): void
    {
        $response = new Response(204, [], '');

        $result = $this->clientRoleService->response($response);

        $this->assertTrue($result);
    }

    public function test_response_extracts_client_id_and_role_from_location_header(): void
    {
        // UUID must be exactly 36 characters
        $clientId = '12345678-1234-1234-1234-123456789012';
        $roleName = 'test-role';
        $locationHeader = "http://localhost:8080/auth/admin/realms/master/clients/{$clientId}/roles/{$roleName}";
        
        $createResponse = new Response(201, ['location' => $locationHeader], '');
        
        $role = ['id' => 'role-uuid', 'name' => $roleName];
        $getResponse = new Response(200, [], json_encode($role));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern("/\/clients\/{$clientId}\/roles\/{$roleName}$/"), Mockery::type('array'))
            ->andReturn($getResponse);

        $result = $this->clientRoleService->response($createResponse);

        $this->assertEquals($roleName, $result['name']);
    }
}

