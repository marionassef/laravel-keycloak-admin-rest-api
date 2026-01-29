# Upgrade Guide

## Upgrading from 2.0.x to 2.1.0

Version 2.1.0 includes several bug fixes and new features. This guide will help you upgrade smoothly.

### Requirements

- PHP 8.0 or higher
- Laravel 9.x, 10.x, or 11.x
- Guzzle 7.x

### Bug Fixes That May Affect Your Code

The following bugs were fixed. If your code was working around these bugs, you may need to update:

**Client Roles Configuration**

The `client_roles` config endpoints for `update` and `delete` were broken:
- They used `'update'` key instead of `'api'` key
- The `update` method used `'post'` instead of `'put'`

If you had custom workarounds for these broken endpoints, you can now remove them.

**GroupService Methods**

`addUser()`, `removeUser()`, and `search()` methods were not working correctly due to incorrect argument passing. They now work as documented:

```php
// Add user to group
KeycloakAdmin::group()->addUser([
    'user_id' => 'user-uuid',
    'group_id' => 'group-uuid'
]);

// Remove user from group
KeycloakAdmin::group()->removeUser([
    'user_id' => 'user-uuid',
    'group_id' => 'group-uuid'
]);

// Search groups
KeycloakAdmin::group()->search([
    'name' => 'search-term'
]);
```

### Automatic Migrations

#### Cache Key Migration

The cache key for admin credentials was corrected from `'keycload-admin-credentials'` to `'keycloak-admin-credentials'`. 

**No action required** - The package automatically:
1. Checks for credentials under the new key first
2. If not found, checks the legacy key
3. If found under the legacy key, migrates to the new key automatically
4. Removes the old cache entry

### New Features

#### Organization API

A new `organization()` service is available for Keycloak 24+ with Organizations feature enabled:

```php
// Create organization
KeycloakAdmin::organization()->create([
    'body' => [
        'name' => 'My Organization',
        'alias' => 'my-org',
        'enabled' => true
    ]
]);

// List all organizations
KeycloakAdmin::organization()->all();

// Get organization
KeycloakAdmin::organization()->get(['id' => 'org-uuid']);

// Add member
KeycloakAdmin::organization()->addMember([
    'id' => 'org-uuid',
    'userId' => 'user-uuid'
]);

// And more... see README.md for full documentation
```

### Configuration Update

After updating, republish the configuration to get the new organization endpoints:

```bash
php artisan vendor:publish --provider="laravelKeycloakAdminRestApi\KeycloakAdminServiceProvider" --force
```

Or manually add the organization config to your `config/keycloakAdmin.php`:

```php
'organization' => [
    'create' => [
        'api' => "{$baseUrl}/organizations",
        'method' => 'post'
    ],
    'all' => [
        'api' => "{$baseUrl}/organizations",
        'method' => 'get'
    ],
    // ... see full config in src/Config/keycloakAdmin.php
],
```

### Testing Your Upgrade

After upgrading, run your test suite to ensure everything works correctly. If you don't have tests, manually verify:

1. User authentication still works
2. User CRUD operations work
3. Role assignments work
4. Group operations work (especially `addUser`, `removeUser`, `search`)
5. Client operations work

### Version History

- **2.1.0** - Organization API, bug fixes, comprehensive tests
- **2.0.2** - Previous stable release

### Getting Help

If you encounter issues during upgrade:

1. Check the [CHANGELOG.md](CHANGELOG.md) for detailed changes
2. Open an issue on GitHub with:
   - Your Laravel version
   - Your PHP version
   - The error message
   - Steps to reproduce

