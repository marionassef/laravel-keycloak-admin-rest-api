<?php

namespace laravelKeycloakAdminRestApi\Tests\Unit;

use laravelKeycloakAdminRestApi\AdminService;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use laravelKeycloakAdminRestApi\Services\Client;
use laravelKeycloakAdminRestApi\Services\ClientRole;
use laravelKeycloakAdminRestApi\Services\GroupService;
use laravelKeycloakAdminRestApi\Services\Organization;
use laravelKeycloakAdminRestApi\Services\Role;
use laravelKeycloakAdminRestApi\Services\User;
use laravelKeycloakAdminRestApi\Tests\TestCase;
use Mockery;

class AdminServiceTest extends TestCase
{
    protected ClientAuthService $mockAuth;
    protected AdminService $adminService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAuth = Mockery::mock(ClientAuthService::class);
        $this->mockAuth->shouldReceive('getToken')
            ->andReturn('test-token');

        $this->adminService = new AdminService($this->mockAuth);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_user_returns_user_service(): void
    {
        $service = $this->adminService->user();

        $this->assertInstanceOf(User::class, $service);
    }

    public function test_role_returns_role_service(): void
    {
        $service = $this->adminService->role();

        $this->assertInstanceOf(Role::class, $service);
    }

    public function test_client_returns_client_service(): void
    {
        $service = $this->adminService->client();

        $this->assertInstanceOf(Client::class, $service);
    }

    public function test_client_role_returns_client_role_service(): void
    {
        $service = $this->adminService->clientRole();

        $this->assertInstanceOf(ClientRole::class, $service);
    }

    public function test_group_returns_group_service(): void
    {
        $service = $this->adminService->group();

        $this->assertInstanceOf(GroupService::class, $service);
    }

    public function test_organization_returns_organization_service(): void
    {
        $service = $this->adminService->organization();

        $this->assertInstanceOf(Organization::class, $service);
    }

    public function test_get_service_returns_correct_service_type(): void
    {
        $userService = $this->adminService->getService('User');
        $roleService = $this->adminService->getService('Role');
        $clientService = $this->adminService->getService('Client');
        $clientRoleService = $this->adminService->getService('ClientRole');
        $groupService = $this->adminService->getService('Group');
        $organizationService = $this->adminService->getService('Organization');

        $this->assertInstanceOf(User::class, $userService);
        $this->assertInstanceOf(Role::class, $roleService);
        $this->assertInstanceOf(Client::class, $clientService);
        $this->assertInstanceOf(ClientRole::class, $clientRoleService);
        $this->assertInstanceOf(GroupService::class, $groupService);
        $this->assertInstanceOf(Organization::class, $organizationService);
    }

    public function test_register_services_registers_all_services(): void
    {
        // Use reflection to check the container
        $reflection = new \ReflectionClass($this->adminService);
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);
        $container = $property->getValue($this->adminService);

        $this->assertArrayHasKey('User', $container);
        $this->assertArrayHasKey('Role', $container);
        $this->assertArrayHasKey('Client', $container);
        $this->assertArrayHasKey('ClientRole', $container);
        $this->assertArrayHasKey('Group', $container);
        $this->assertArrayHasKey('Organization', $container);

        $this->assertEquals(User::class, $container['User']);
        $this->assertEquals(Role::class, $container['Role']);
        $this->assertEquals(Client::class, $container['Client']);
        $this->assertEquals(ClientRole::class, $container['ClientRole']);
        $this->assertEquals(GroupService::class, $container['Group']);
        $this->assertEquals(Organization::class, $container['Organization']);
    }

    public function test_each_call_creates_new_service_instance(): void
    {
        $userService1 = $this->adminService->user();
        $userService2 = $this->adminService->user();

        // They should be different instances
        $this->assertNotSame($userService1, $userService2);
    }

    public function test_services_receive_auth_service(): void
    {
        $userService = $this->adminService->user();

        // Use reflection to verify auth is injected
        $reflection = new \ReflectionClass($userService);
        $property = $reflection->getProperty('auth');
        $property->setAccessible(true);
        $auth = $property->getValue($userService);

        $this->assertSame($this->mockAuth, $auth);
    }
}

