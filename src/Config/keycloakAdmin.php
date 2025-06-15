<?php

$baseUrl = env(
    'KEYCLOAK_ADMIN_BASE_URL',
    'http://localhost:8080/auth/admin/realms/master'
);

return [

    'client' => [
        'id' => env('KEYCLOAK_ADMIN_CLIENT_ID'),
        'secret' => env('KEYCLOAK_ADMIN_CLIENT_SECRET')
    ],

    'api' => [

        'client' => [

            'token' => env('KEYCLOAK_BASE_URL', 'localhost:8080') . '/realms/' . env('KEYCLOAK_REALM', 'master') . '/protocol/openid-connect/token',

            'create' => [
                'api' => "{$baseUrl}/clients",
                'method' => 'post'
            ],
            'all' => [
                'api' => "{$baseUrl}/clients",
                'method' => 'get'
            ],
            'get' => [
                'api' => "{$baseUrl}/clients/{id}",
                'method' => 'get'
            ],
            'update' => [
                'api' => "{$baseUrl}/clients/{id}",
                'method' => 'put'
            ],
            'delete' => [
                'api' => "{$baseUrl}/clients/{id}",
                'method' => 'delete'
            ]

        ],

        'client_roles' => [

            'create' => [
                'api' => "{$baseUrl}/clients/{id}/roles",
                'method' => 'post'
            ],
            'all' => [
                'api' => "{$baseUrl}/clients/{id}/roles",
                'method' => 'get'
            ],
            'getByName' => [
                'api' => "{$baseUrl}/clients/{id}/roles/{role}",
                'method' => 'get'
            ],
            'composites' => [
                'api' => "{$baseUrl}/clients/{id}/roles/{role}/composites",
                'method' => 'post'
            ],
            'update' => [
                'update' => "{$baseUrl}/clients/{id}/roles/{role}",
                'method' => 'post'
            ],
            'delete' => [
                'update' => "{$baseUrl}/clients/{id}/roles/{role}",
                'method' => 'delete'
            ],

        ],

        'user' => [
            'create' => [
                'method' => 'post',
                'api' => "{$baseUrl}/users"
            ],
            'all' => [
                'api' => "{$baseUrl}/users",
                'method' => 'get'
            ],
            'get' => [
                'api' => "{$baseUrl}/users/{id}",
                'method' => 'get'
            ],
            'update' => [
                'api' => "{$baseUrl}/users/{id}",
                'method' => 'put'
            ],

            'delete' => [
                'api' => "{$baseUrl}/users/{id}",
                'method' => 'delete'
            ],

            'groups' => [
                'api' => "{$baseUrl}/users/{id}/groups",
                'method' => 'get'
            ],
            'addToGroup' => [
                'api' => "{$baseUrl}/users/{id}/groups/{groupId}",
                'method' => 'put'
            ],
            'deleteFromGroup' => [
                'api' => "{$baseUrl}/users/{id}/groups/{groupId}",
                'method' => 'delete'
            ],
            'removeTOTP' => [
                'api' => "{$baseUrl}/users/{id}/remove-totp",
                'method' => 'put'
            ],
            'setTemporaryPassword' => [
                'api' => "{$baseUrl}/users/{id}/reset-password",
                'method' => 'put'
            ],
            'verifyByEmail' => [
                'api' => "{$baseUrl}/users/{id}/send-verify-email",
                'method' => 'put'
            ],
            'roleMappings' => [
                'api' => "{$baseUrl}/users/{id}/role-mappings",
                'method' => 'get'
            ],
            'addRealmRoles' => [
                'api' => "{$baseUrl}/users/{id}/role-mappings/realm",
                'method' => 'post'
            ],
            'getRealmRoles' => [
                'api' => "{$baseUrl}/users/{id}/role-mappings/realm",
                'method' => 'get'
            ],
            'deleteRealmRoles' => [
                'api' => "{$baseUrl}/users/{id}/role-mappings/realm",
                'method' => 'delete'
            ],
            'getAvailableRealmRoles' => [
                'api' => "{$baseUrl}/users/{id}/role-mappings/realm/available",
                'method' => 'get'
            ],
            'getEffectiveRealmRoles' => [
                'api' => "{$baseUrl}/users/{id}/role-mappings/realm/composite",
                'method' => 'get'
            ],
            'addClientRole' => [
                'api' => "{$baseUrl}/users/{id}/role-mappings/clients/{client_id}",
                'method' => 'post'
            ],
        ],

        'role' => [
            'create' => [
                'api' => "{$baseUrl}/roles",
                'method' => 'post'
            ],
            'all' => [
                'api' => "{$baseUrl}/roles",
                'method' => 'get'
            ],
            'get' => [
                'api' => "{$baseUrl}/roles-by-id/{id}",
                'method' => 'get'
            ],
            'getByName' => [
                'api' => "{$baseUrl}/roles/{role}",
                'method' => 'get'
            ],
            'update' => [
                'api' => "{$baseUrl}/roles-by-id/{id}",
                'method' => 'put'
            ],
            'updateByName' => [
                'api' => "{$baseUrl}/roles/{role}",
                'method' => 'put'
            ],
            'delete' => [
                'api' => "{$baseUrl}/roles-by-id/{id}",
                'method' => 'delete'
            ],
            'deleteByName' => [
                'api' => "{$baseUrl}/roles/{role}",
                'method' => 'delete'
            ],
        ],

        'group' => [
            'create' => [
                'api' => "{$baseUrl}/groups",
                'method' => 'post'
            ],
            'list' => [
                'api' => "{$baseUrl}/groups",
                'method' => 'get'
            ],
            'get' => [
                'api' => "{$baseUrl}/groups-by-id/{id}",
                'method' => 'get'
            ],
            'getByName' => [
                'api' => "{$baseUrl}/groups/{group}",
                'method' => 'get'
            ],
            'update' => [
                'api' => "{$baseUrl}/groups-by-id/{id}",
                'method' => 'put'
            ],
            'updateByName' => [
                'api' => "{$baseUrl}/groups/{group}",
                'method' => 'put'
            ],
            'getMembers' => [
                'api' => "{$baseUrl}/groups/{id}/members",
                'method' => 'get'
            ],
            'delete' => [
                'api' => "{$baseUrl}/groups-by-id/{id}",
                'method' => 'delete'
            ],
            'deleteByName' => [
                'api' => "{$baseUrl}/groups/{group}",
                'method' => 'delete'
            ],
            'addUser' => [
                'api' => "{$baseUrl}/users/{user_id}/groups/{group_id}",
                'method' => 'PUT',
            ],
            'removeUser' => [
                'api' => "{$baseUrl}/users/{user_id}/groups/{group_id}",
                'method' => 'DELETE',
            ],
            'search' => [
                'api' => "{$baseUrl}/groups",
                'method' => 'GET',
            ],
        ]
    ]
];
