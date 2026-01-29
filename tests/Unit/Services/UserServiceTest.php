<?php

namespace laravelKeycloakAdminRestApi\Tests\Unit\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use laravelKeycloakAdminRestApi\Services\User;
use laravelKeycloakAdminRestApi\Tests\TestCase;
use Mockery;

class UserServiceTest extends TestCase
{
    protected ClientAuthService $mockAuth;
    protected ClientInterface $mockHttp;
    protected User $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAuth = Mockery::mock(ClientAuthService::class);
        $this->mockAuth->shouldReceive('getToken')
            ->andReturn('test-token');

        $this->mockHttp = Mockery::mock(ClientInterface::class);

        $this->userService = new User($this->mockAuth, $this->mockHttp);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_all_users(): void
    {
        $users = [
            ['id' => 'user-1', 'username' => 'john'],
            ['id' => 'user-2', 'username' => 'jane']
        ];

        $response = new Response(200, [], json_encode($users));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/users$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->all([]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('john', $result[0]['username']);
    }

    public function test_get_user_by_id(): void
    {
        $user = ['id' => 'user-123', 'username' => 'testuser', 'email' => 'test@example.com'];
        $response = new Response(200, [], json_encode($user));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/users\/user-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->get(['id' => 'user-123']);

        $this->assertEquals('testuser', $result['username']);
        $this->assertEquals('test@example.com', $result['email']);
    }

    public function test_create_user_returns_created_user(): void
    {
        $userId = 'new-user-uuid';
        $locationHeader = "http://localhost:8080/auth/admin/realms/master/users/{$userId}";
        
        // First response - create returns location header
        $createResponse = new Response(201, ['location' => $locationHeader], '');
        
        // Second response - get returns the user
        $user = ['id' => $userId, 'username' => 'newuser'];
        $getResponse = new Response(200, [], json_encode($user));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/users$/'), Mockery::type('array'))
            ->andReturn($createResponse);

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern("/\/users\/{$userId}$/"), Mockery::type('array'))
            ->andReturn($getResponse);

        $result = $this->userService->create([
            'body' => ['username' => 'newuser', 'enabled' => true]
        ]);

        $this->assertEquals($userId, $result['id']);
        $this->assertEquals('newuser', $result['username']);
    }

    public function test_update_user(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/users\/user-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->update([
            'id' => 'user-123',
            'body' => ['firstName' => 'Updated']
        ]);

        $this->assertTrue($result);
    }

    public function test_delete_user(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/users\/user-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->delete(['id' => 'user-123']);

        $this->assertTrue($result);
    }

    public function test_get_user_groups(): void
    {
        $groups = [
            ['id' => 'group-1', 'name' => 'admins'],
            ['id' => 'group-2', 'name' => 'users']
        ];
        $response = new Response(200, [], json_encode($groups));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/users\/user-123\/groups$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->groups(['id' => 'user-123']);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function test_add_user_to_group(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/users\/user-123\/groups\/group-456$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->addToGroup([
            'id' => 'user-123',
            'groupId' => 'group-456'
        ]);

        $this->assertTrue($result);
    }

    public function test_remove_user_from_group(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/users\/user-123\/groups\/group-456$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->deleteFromGroup([
            'id' => 'user-123',
            'groupId' => 'group-456'
        ]);

        $this->assertTrue($result);
    }

    public function test_get_role_mappings(): void
    {
        $mappings = [
            'realmMappings' => [['id' => 'role-1', 'name' => 'admin']],
            'clientMappings' => []
        ];
        $response = new Response(200, [], json_encode($mappings));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/users\/user-123\/role-mappings$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->roleMappings(['id' => 'user-123']);

        $this->assertArrayHasKey('realmMappings', $result);
    }

    public function test_add_realm_roles(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/users\/user-123\/role-mappings\/realm$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->addRealmRoles([
            'id' => 'user-123',
            'body' => [['id' => 'role-id', 'name' => 'admin']]
        ]);

        $this->assertTrue($result);
    }

    public function test_get_realm_roles(): void
    {
        $roles = [
            ['id' => 'role-1', 'name' => 'admin'],
            ['id' => 'role-2', 'name' => 'user']
        ];
        $response = new Response(200, [], json_encode($roles));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/users\/user-123\/role-mappings\/realm$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->getRealmRoles(['id' => 'user-123']);

        $this->assertCount(2, $result);
    }

    public function test_delete_realm_roles(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/users\/user-123\/role-mappings\/realm$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->deleteRealmRoles([
            'id' => 'user-123',
            'body' => [['id' => 'role-id', 'name' => 'admin']]
        ]);

        $this->assertTrue($result);
    }

    public function test_set_temporary_password(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/users\/user-123\/reset-password$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->setTemporaryPassword([
            'id' => 'user-123',
            'body' => [
                'type' => 'password',
                'value' => 'temp123',
                'temporary' => true
            ]
        ]);

        $this->assertTrue($result);
    }

    public function test_add_client_role(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/users\/user-123\/role-mappings\/clients\/client-456$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->userService->addClientRole([
            'id' => 'user-123',
            'client_id' => 'client-456',
            'body' => [['id' => 'role-id', 'name' => 'client-role']]
        ]);

        $this->assertTrue($result);
    }

    public function test_response_handles_json_response(): void
    {
        $userData = ['id' => 'user-123', 'username' => 'testuser'];
        $response = new Response(200, [], json_encode($userData));

        $result = $this->userService->response($response);

        $this->assertEquals($userData, $result);
    }

    public function test_response_handles_empty_response(): void
    {
        $response = new Response(204, [], '');

        $result = $this->userService->response($response);

        $this->assertTrue($result);
    }
}

