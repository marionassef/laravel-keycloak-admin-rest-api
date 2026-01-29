<?php

namespace laravelKeycloakAdminRestApi\Tests\Unit\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use laravelKeycloakAdminRestApi\Services\Role;
use laravelKeycloakAdminRestApi\Tests\TestCase;
use Mockery;

class RoleServiceTest extends TestCase
{
    protected ClientAuthService $mockAuth;
    protected ClientInterface $mockHttp;
    protected Role $roleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAuth = Mockery::mock(ClientAuthService::class);
        $this->mockAuth->shouldReceive('getToken')
            ->andReturn('test-token');

        $this->mockHttp = Mockery::mock(ClientInterface::class);

        $this->roleService = new Role($this->mockAuth, $this->mockHttp);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_all_roles(): void
    {
        $roles = [
            ['id' => 'role-1', 'name' => 'admin'],
            ['id' => 'role-2', 'name' => 'user']
        ];

        $response = new Response(200, [], json_encode($roles));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/roles$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->roleService->all([]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('admin', $result[0]['name']);
    }

    public function test_get_role_by_id(): void
    {
        $role = ['id' => 'role-123', 'name' => 'admin', 'description' => 'Administrator role'];
        $response = new Response(200, [], json_encode($role));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/roles-by-id\/role-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->roleService->get(['id' => 'role-123']);

        $this->assertEquals('admin', $result['name']);
        $this->assertEquals('Administrator role', $result['description']);
    }

    public function test_get_role_by_name(): void
    {
        $role = ['id' => 'role-123', 'name' => 'admin'];
        $response = new Response(200, [], json_encode($role));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/roles\/admin$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->roleService->getByName(['role' => 'admin']);

        $this->assertEquals('admin', $result['name']);
    }

    public function test_create_role_returns_created_role(): void
    {
        $roleName = 'new-role';
        $locationHeader = "http://localhost:8080/auth/admin/realms/master/roles/{$roleName}";
        
        // Create response with location header
        $createResponse = new Response(201, ['location' => $locationHeader], '');
        
        // Get response returns the role
        $role = ['id' => 'role-uuid', 'name' => $roleName];
        $getResponse = new Response(200, [], json_encode($role));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/roles$/'), Mockery::type('array'))
            ->andReturn($createResponse);

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern("/\/roles\/{$roleName}$/"), Mockery::type('array'))
            ->andReturn($getResponse);

        $result = $this->roleService->create([
            'body' => ['name' => $roleName, 'description' => 'A new role']
        ]);

        $this->assertEquals($roleName, $result['name']);
    }

    public function test_update_role_by_id(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/roles-by-id\/role-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->roleService->update([
            'id' => 'role-123',
            'body' => ['description' => 'Updated description']
        ]);

        $this->assertTrue($result);
    }

    public function test_update_role_by_name(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/roles\/admin$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->roleService->updateByName([
            'role' => 'admin',
            'body' => ['description' => 'Updated description']
        ]);

        $this->assertTrue($result);
    }

    public function test_delete_role_by_id(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/roles-by-id\/role-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->roleService->delete(['id' => 'role-123']);

        $this->assertTrue($result);
    }

    public function test_delete_role_by_name(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/roles\/admin$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->roleService->deleteByName(['role' => 'admin']);

        $this->assertTrue($result);
    }

    public function test_response_handles_json_response(): void
    {
        $roleData = ['id' => 'role-123', 'name' => 'admin'];
        $response = new Response(200, [], json_encode($roleData));

        $result = $this->roleService->response($response);

        $this->assertEquals($roleData, $result);
    }

    public function test_response_handles_empty_response(): void
    {
        $response = new Response(204, [], '');

        $result = $this->roleService->response($response);

        $this->assertTrue($result);
    }
}

