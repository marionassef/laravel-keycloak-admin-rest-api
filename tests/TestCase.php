<?php

namespace laravelKeycloakAdminRestApi\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use laravelKeycloakAdminRestApi\KeycloakAdminServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            KeycloakAdminServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'KeycloakAdmin' => \laravelKeycloakAdminRestApi\Facades\KeycloakAdmin::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('keycloakAdmin', [
            'client' => [
                'id' => 'test-client-id',
                'secret' => 'test-client-secret'
            ],
            'api' => $this->getApiConfig()
        ]);
    }

    protected function getApiConfig(): array
    {
        $baseUrl = 'http://localhost:8080/auth/admin/realms/master';

        return [
            'client' => [
                'token' => 'http://localhost:8080/realms/master/protocol/openid-connect/token',
                'create' => ['api' => "{$baseUrl}/clients", 'method' => 'post'],
                'all' => ['api' => "{$baseUrl}/clients", 'method' => 'get'],
                'get' => ['api' => "{$baseUrl}/clients/{id}", 'method' => 'get'],
                'update' => ['api' => "{$baseUrl}/clients/{id}", 'method' => 'put'],
                'delete' => ['api' => "{$baseUrl}/clients/{id}", 'method' => 'delete'],
            ],
            'client_roles' => [
                'create' => ['api' => "{$baseUrl}/clients/{id}/roles", 'method' => 'post'],
                'all' => ['api' => "{$baseUrl}/clients/{id}/roles", 'method' => 'get'],
                'getByName' => ['api' => "{$baseUrl}/clients/{id}/roles/{role}", 'method' => 'get'],
                'composites' => ['api' => "{$baseUrl}/clients/{id}/roles/{role}/composites", 'method' => 'post'],
                'update' => ['api' => "{$baseUrl}/clients/{id}/roles/{role}", 'method' => 'put'],
                'delete' => ['api' => "{$baseUrl}/clients/{id}/roles/{role}", 'method' => 'delete'],
            ],
            'user' => [
                'create' => ['method' => 'post', 'api' => "{$baseUrl}/users"],
                'all' => ['api' => "{$baseUrl}/users", 'method' => 'get'],
                'get' => ['api' => "{$baseUrl}/users/{id}", 'method' => 'get'],
                'update' => ['api' => "{$baseUrl}/users/{id}", 'method' => 'put'],
                'delete' => ['api' => "{$baseUrl}/users/{id}", 'method' => 'delete'],
                'groups' => ['api' => "{$baseUrl}/users/{id}/groups", 'method' => 'get'],
                'addToGroup' => ['api' => "{$baseUrl}/users/{id}/groups/{groupId}", 'method' => 'put'],
                'deleteFromGroup' => ['api' => "{$baseUrl}/users/{id}/groups/{groupId}", 'method' => 'delete'],
                'removeTOTP' => ['api' => "{$baseUrl}/users/{id}/remove-totp", 'method' => 'put'],
                'setTemporaryPassword' => ['api' => "{$baseUrl}/users/{id}/reset-password", 'method' => 'put'],
                'verifyByEmail' => ['api' => "{$baseUrl}/users/{id}/send-verify-email", 'method' => 'put'],
                'roleMappings' => ['api' => "{$baseUrl}/users/{id}/role-mappings", 'method' => 'get'],
                'addRealmRoles' => ['api' => "{$baseUrl}/users/{id}/role-mappings/realm", 'method' => 'post'],
                'getRealmRoles' => ['api' => "{$baseUrl}/users/{id}/role-mappings/realm", 'method' => 'get'],
                'deleteRealmRoles' => ['api' => "{$baseUrl}/users/{id}/role-mappings/realm", 'method' => 'delete'],
                'getAvailableRealmRoles' => ['api' => "{$baseUrl}/users/{id}/role-mappings/realm/available", 'method' => 'get'],
                'getEffectiveRealmRoles' => ['api' => "{$baseUrl}/users/{id}/role-mappings/realm/composite", 'method' => 'get'],
                'addClientRole' => ['api' => "{$baseUrl}/users/{id}/role-mappings/clients/{client_id}", 'method' => 'post'],
            ],
            'role' => [
                'create' => ['api' => "{$baseUrl}/roles", 'method' => 'post'],
                'all' => ['api' => "{$baseUrl}/roles", 'method' => 'get'],
                'get' => ['api' => "{$baseUrl}/roles-by-id/{id}", 'method' => 'get'],
                'getByName' => ['api' => "{$baseUrl}/roles/{role}", 'method' => 'get'],
                'update' => ['api' => "{$baseUrl}/roles-by-id/{id}", 'method' => 'put'],
                'updateByName' => ['api' => "{$baseUrl}/roles/{role}", 'method' => 'put'],
                'delete' => ['api' => "{$baseUrl}/roles-by-id/{id}", 'method' => 'delete'],
                'deleteByName' => ['api' => "{$baseUrl}/roles/{role}", 'method' => 'delete'],
            ],
            'group' => [
                'create' => ['api' => "{$baseUrl}/groups", 'method' => 'post'],
                'list' => ['api' => "{$baseUrl}/groups", 'method' => 'get'],
                'get' => ['api' => "{$baseUrl}/groups/{id}", 'method' => 'get'],
                'getByName' => ['api' => "{$baseUrl}/groups/{group}", 'method' => 'get'],
                'update' => ['api' => "{$baseUrl}/groups/{id}", 'method' => 'put'],
                'updateByName' => ['api' => "{$baseUrl}/groups/{group}", 'method' => 'put'],
                'getMembers' => ['api' => "{$baseUrl}/groups/{id}/members", 'method' => 'get'],
                'delete' => ['api' => "{$baseUrl}/groups/{id}", 'method' => 'delete'],
                'deleteByName' => ['api' => "{$baseUrl}/groups/{group}", 'method' => 'delete'],
                'addUser' => ['api' => "{$baseUrl}/users/{user_id}/groups/{group_id}", 'method' => 'PUT'],
                'removeUser' => ['api' => "{$baseUrl}/users/{user_id}/groups/{group_id}", 'method' => 'DELETE'],
                'search' => ['api' => "{$baseUrl}/groups", 'method' => 'GET'],
            ],
            'organization' => [
                'create' => ['api' => "{$baseUrl}/organizations", 'method' => 'post'],
                'all' => ['api' => "{$baseUrl}/organizations", 'method' => 'get'],
                'get' => ['api' => "{$baseUrl}/organizations/{id}", 'method' => 'get'],
                'update' => ['api' => "{$baseUrl}/organizations/{id}", 'method' => 'put'],
                'delete' => ['api' => "{$baseUrl}/organizations/{id}", 'method' => 'delete'],
                'getMembers' => ['api' => "{$baseUrl}/organizations/{id}/members", 'method' => 'get'],
                'addMember' => ['api' => "{$baseUrl}/organizations/{id}/members/{userId}", 'method' => 'put'],
                'removeMember' => ['api' => "{$baseUrl}/organizations/{id}/members/{userId}", 'method' => 'delete'],
                'inviteUser' => ['api' => "{$baseUrl}/organizations/{id}/members/invite-user", 'method' => 'post'],
                'getIdentityProviders' => ['api' => "{$baseUrl}/organizations/{id}/identity-providers", 'method' => 'get'],
                'linkIdentityProvider' => ['api' => "{$baseUrl}/organizations/{id}/identity-providers/{alias}", 'method' => 'post'],
                'unlinkIdentityProvider' => ['api' => "{$baseUrl}/organizations/{id}/identity-providers/{alias}", 'method' => 'delete'],
                'getUserOrganizations' => ['api' => "{$baseUrl}/users/{userId}/organizations", 'method' => 'get'],
            ],
        ];
    }
}

