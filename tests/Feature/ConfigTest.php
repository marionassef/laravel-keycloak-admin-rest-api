<?php

namespace laravelKeycloakAdminRestApi\Tests\Feature;

use laravelKeycloakAdminRestApi\Tests\TestCase;

class ConfigTest extends TestCase
{
    public function test_client_api_endpoints_are_configured(): void
    {
        $clientApi = config('keycloakAdmin.api.client');

        $this->assertArrayHasKey('token', $clientApi);
        $this->assertArrayHasKey('create', $clientApi);
        $this->assertArrayHasKey('all', $clientApi);
        $this->assertArrayHasKey('get', $clientApi);
        $this->assertArrayHasKey('update', $clientApi);
        $this->assertArrayHasKey('delete', $clientApi);

        $this->assertEquals('post', $clientApi['create']['method']);
        $this->assertEquals('get', $clientApi['all']['method']);
        $this->assertEquals('get', $clientApi['get']['method']);
        $this->assertEquals('put', $clientApi['update']['method']);
        $this->assertEquals('delete', $clientApi['delete']['method']);
    }

    public function test_client_roles_api_endpoints_are_configured(): void
    {
        $clientRolesApi = config('keycloakAdmin.api.client_roles');

        $this->assertArrayHasKey('create', $clientRolesApi);
        $this->assertArrayHasKey('all', $clientRolesApi);
        $this->assertArrayHasKey('getByName', $clientRolesApi);
        $this->assertArrayHasKey('composites', $clientRolesApi);
        $this->assertArrayHasKey('update', $clientRolesApi);
        $this->assertArrayHasKey('delete', $clientRolesApi);

        // Verify the bug fix - 'api' key should exist (not 'update' key)
        $this->assertArrayHasKey('api', $clientRolesApi['update']);
        $this->assertArrayHasKey('api', $clientRolesApi['delete']);
        $this->assertEquals('put', $clientRolesApi['update']['method']);
        $this->assertEquals('delete', $clientRolesApi['delete']['method']);
    }

    public function test_user_api_endpoints_are_configured(): void
    {
        $userApi = config('keycloakAdmin.api.user');

        $expectedEndpoints = [
            'create', 'all', 'get', 'update', 'delete',
            'groups', 'addToGroup', 'deleteFromGroup',
            'removeTOTP', 'setTemporaryPassword', 'verifyByEmail',
            'roleMappings', 'addRealmRoles', 'getRealmRoles',
            'deleteRealmRoles', 'getAvailableRealmRoles',
            'getEffectiveRealmRoles', 'addClientRole'
        ];

        foreach ($expectedEndpoints as $endpoint) {
            $this->assertArrayHasKey($endpoint, $userApi, "Missing endpoint: {$endpoint}");
            $this->assertArrayHasKey('api', $userApi[$endpoint], "Missing 'api' key for endpoint: {$endpoint}");
            $this->assertArrayHasKey('method', $userApi[$endpoint], "Missing 'method' key for endpoint: {$endpoint}");
        }
    }

    public function test_role_api_endpoints_are_configured(): void
    {
        $roleApi = config('keycloakAdmin.api.role');

        $expectedEndpoints = [
            'create', 'all', 'get', 'getByName',
            'update', 'updateByName', 'delete', 'deleteByName'
        ];

        foreach ($expectedEndpoints as $endpoint) {
            $this->assertArrayHasKey($endpoint, $roleApi, "Missing endpoint: {$endpoint}");
        }
    }

    public function test_group_api_endpoints_are_configured(): void
    {
        $groupApi = config('keycloakAdmin.api.group');

        $expectedEndpoints = [
            'create', 'list', 'get', 'getByName',
            'update', 'updateByName', 'getMembers',
            'delete', 'deleteByName', 'addUser', 'removeUser', 'search'
        ];

        foreach ($expectedEndpoints as $endpoint) {
            $this->assertArrayHasKey($endpoint, $groupApi, "Missing endpoint: {$endpoint}");
        }
    }

    public function test_organization_api_endpoints_are_configured(): void
    {
        $orgApi = config('keycloakAdmin.api.organization');

        $expectedEndpoints = [
            'create', 'all', 'get', 'update', 'delete',
            'getMembers', 'addMember', 'removeMember', 'inviteUser',
            'getIdentityProviders', 'linkIdentityProvider',
            'unlinkIdentityProvider', 'getUserOrganizations'
        ];

        foreach ($expectedEndpoints as $endpoint) {
            $this->assertArrayHasKey($endpoint, $orgApi, "Missing endpoint: {$endpoint}");
            $this->assertArrayHasKey('api', $orgApi[$endpoint], "Missing 'api' key for endpoint: {$endpoint}");
            $this->assertArrayHasKey('method', $orgApi[$endpoint], "Missing 'method' key for endpoint: {$endpoint}");
        }
    }

    public function test_api_urls_contain_proper_placeholders(): void
    {
        $userApi = config('keycloakAdmin.api.user');
        
        // Check that URLs with placeholders are properly formatted
        $this->assertStringContainsString('{id}', $userApi['get']['api']);
        $this->assertStringContainsString('{id}', $userApi['update']['api']);
        $this->assertStringContainsString('{id}', $userApi['delete']['api']);
        $this->assertStringContainsString('{id}', $userApi['addToGroup']['api']);
        $this->assertStringContainsString('{groupId}', $userApi['addToGroup']['api']);
    }

    public function test_organization_api_urls_contain_proper_placeholders(): void
    {
        $orgApi = config('keycloakAdmin.api.organization');
        
        $this->assertStringContainsString('{id}', $orgApi['get']['api']);
        $this->assertStringContainsString('{id}', $orgApi['addMember']['api']);
        $this->assertStringContainsString('{userId}', $orgApi['addMember']['api']);
        $this->assertStringContainsString('{id}', $orgApi['linkIdentityProvider']['api']);
        $this->assertStringContainsString('{alias}', $orgApi['linkIdentityProvider']['api']);
        $this->assertStringContainsString('{userId}', $orgApi['getUserOrganizations']['api']);
    }
}

