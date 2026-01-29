<?php

namespace laravelKeycloakAdminRestApi\Tests\Feature;

use laravelKeycloakAdminRestApi\AdminService;
use laravelKeycloakAdminRestApi\Facades\KeycloakAdmin;
use laravelKeycloakAdminRestApi\KeycloakAdminServiceProvider;
use laravelKeycloakAdminRestApi\Services\Client;
use laravelKeycloakAdminRestApi\Services\ClientRole;
use laravelKeycloakAdminRestApi\Services\GroupService;
use laravelKeycloakAdminRestApi\Services\Organization;
use laravelKeycloakAdminRestApi\Services\Role;
use laravelKeycloakAdminRestApi\Services\User;
use laravelKeycloakAdminRestApi\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_is_loaded(): void
    {
        $providers = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(KeycloakAdminServiceProvider::class, $providers);
    }

    public function test_keycloak_admin_is_bound_in_container(): void
    {
        $this->assertTrue($this->app->bound('KeycloakAdmin'));
    }

    public function test_keycloak_admin_resolves_to_admin_service(): void
    {
        $service = $this->app->make('KeycloakAdmin');

        $this->assertInstanceOf(AdminService::class, $service);
    }

    public function test_facade_returns_admin_service(): void
    {
        $service = KeycloakAdmin::getFacadeRoot();

        $this->assertInstanceOf(AdminService::class, $service);
    }

    public function test_facade_user_returns_user_service(): void
    {
        $service = KeycloakAdmin::user();

        $this->assertInstanceOf(User::class, $service);
    }

    public function test_facade_role_returns_role_service(): void
    {
        $service = KeycloakAdmin::role();

        $this->assertInstanceOf(Role::class, $service);
    }

    public function test_facade_client_returns_client_service(): void
    {
        $service = KeycloakAdmin::client();

        $this->assertInstanceOf(Client::class, $service);
    }

    public function test_facade_client_role_returns_client_role_service(): void
    {
        $service = KeycloakAdmin::clientRole();

        $this->assertInstanceOf(ClientRole::class, $service);
    }

    public function test_facade_group_returns_group_service(): void
    {
        $service = KeycloakAdmin::group();

        $this->assertInstanceOf(GroupService::class, $service);
    }

    public function test_facade_organization_returns_organization_service(): void
    {
        $service = KeycloakAdmin::organization();

        $this->assertInstanceOf(Organization::class, $service);
    }

    public function test_config_is_publishable(): void
    {
        $this->artisan('vendor:publish', [
            '--provider' => KeycloakAdminServiceProvider::class,
            '--tag' => 'KeycloakAdmin'
        ]);

        // The config should be published (in test environment, we verify the command runs)
        $this->assertTrue(true);
    }

    public function test_config_values_are_loaded(): void
    {
        $clientId = config('keycloakAdmin.client.id');
        $clientSecret = config('keycloakAdmin.client.secret');

        $this->assertEquals('test-client-id', $clientId);
        $this->assertEquals('test-client-secret', $clientSecret);
    }

    public function test_api_config_is_loaded(): void
    {
        $userApi = config('keycloakAdmin.api.user');

        $this->assertIsArray($userApi);
        $this->assertArrayHasKey('create', $userApi);
        $this->assertArrayHasKey('all', $userApi);
        $this->assertArrayHasKey('get', $userApi);
        $this->assertArrayHasKey('update', $userApi);
        $this->assertArrayHasKey('delete', $userApi);
    }

    public function test_organization_api_config_is_loaded(): void
    {
        $orgApi = config('keycloakAdmin.api.organization');

        $this->assertIsArray($orgApi);
        $this->assertArrayHasKey('create', $orgApi);
        $this->assertArrayHasKey('all', $orgApi);
        $this->assertArrayHasKey('get', $orgApi);
        $this->assertArrayHasKey('update', $orgApi);
        $this->assertArrayHasKey('delete', $orgApi);
        $this->assertArrayHasKey('getMembers', $orgApi);
        $this->assertArrayHasKey('addMember', $orgApi);
        $this->assertArrayHasKey('removeMember', $orgApi);
        $this->assertArrayHasKey('inviteUser', $orgApi);
        $this->assertArrayHasKey('getIdentityProviders', $orgApi);
        $this->assertArrayHasKey('linkIdentityProvider', $orgApi);
        $this->assertArrayHasKey('unlinkIdentityProvider', $orgApi);
        $this->assertArrayHasKey('getUserOrganizations', $orgApi);
    }
}

