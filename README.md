### This is a forked repository from https://github.com/Mnikoei/laravel-keycloak-admin

Original work credited to Mnikoei.

### Installtion

```
composer require marionassef/laravel-keycloak-admin-rest-api
```

### Copy the package config to your local config with the publish command:

```shell
php artisan vendor:publish --provider="laravelKeycloakAdminRestApi\KeycloakAdminServiceProvider"
```

#### laravel-keycloak-admin



Add these environment variables to your .env :


```
KEYCLOAK_BASE_URL=http://keycloak-domain.example/auth

KEYCLOAK_REALM=                

KEYCLOAK_REALM_PUBLIC_KEY=     # realm settings -> keys 

KEYCLOAK_ADMIN_CLIENT_ID=  
          
KEYCLOAK_ADMIN_CLIENT_UUID=            

KEYCLOAK_ADMIN_CLIENT_SECRET=        # clients -> your_client -> credentials 

KEYCLOAK_ADMIN_BASE_URL=${KEYCLOAK_BASE_URL}/admin/realms/${KEYCLOAK_REALM} 
```



#### Enable realm managment

Go to ```clients -> your_client -> Service Account``` then select realm-managment

from Client Roles list and assign realm-admin to client.






#### Available methods : 


Package has provided services as below:

* user
* role
* client
* clientRole
* group
* organization




All api's are decleared in ```config\keycloakAdmin.php```
 
For every api just needs call api name as method on related service .



Example:
```php
KeycloakAdmin::serviceName()->apiName($parameters)



keycloakAdmin::user()->create([
      
     'body' => [  // https://www.keycloak.org/docs-api/7.0/rest-api/index.html#_userrepresentation
             
             'username' => 'foo'
              
       ]

]);



keycloakAdmin::user()->update([

     'id' => 'user_id',

     'body' => [  // https://www.keycloak.org/docs-api/7.0/rest-api/index.html#_userrepresentation
             
             'username' => 'foo'
              
       ]

]);



keycloakAdmin::role()->get([
      
     'id' => 'role_id'

]);
```

#### Organization API Examples:

```php
// Create an organization
KeycloakAdmin::organization()->create([
    'body' => [
        'name' => 'My Organization',
        'alias' => 'my-org',
        'enabled' => true,
        'domains' => ['example.com']
    ]
]);

// Get all organizations
KeycloakAdmin::organization()->all();

// Search organizations by name
KeycloakAdmin::organization()->search([
    'search' => 'org-name'
]);

// Get organization by ID
KeycloakAdmin::organization()->get([
    'id' => 'organization-uuid'
]);

// Update organization
KeycloakAdmin::organization()->update([
    'id' => 'organization-uuid',
    'body' => [
        'name' => 'Updated Organization Name'
    ]
]);

// Delete organization
KeycloakAdmin::organization()->delete([
    'id' => 'organization-uuid'
]);

// Get organization members
KeycloakAdmin::organization()->getMembers([
    'id' => 'organization-uuid'
]);

// Add member to organization
KeycloakAdmin::organization()->addMember([
    'id' => 'organization-uuid',
    'userId' => 'user-uuid'
]);

// Remove member from organization
KeycloakAdmin::organization()->removeMember([
    'id' => 'organization-uuid',
    'userId' => 'user-uuid'
]);

// Invite user to organization
KeycloakAdmin::organization()->inviteUser([
    'id' => 'organization-uuid',
    'body' => [
        'email' => 'user@example.com',
        'firstName' => 'John',
        'lastName' => 'Doe'
    ]
]);

// Get organization identity providers
KeycloakAdmin::organization()->getIdentityProviders([
    'id' => 'organization-uuid'
]);

// Link identity provider to organization
KeycloakAdmin::organization()->linkIdentityProvider([
    'id' => 'organization-uuid',
    'alias' => 'idp-alias'
]);

// Unlink identity provider from organization
KeycloakAdmin::organization()->unlinkIdentityProvider([
    'id' => 'organization-uuid',
    'alias' => 'idp-alias'
]);

// Get organizations for a user
KeycloakAdmin::organization()->getUserOrganizations([
    'userId' => 'user-uuid'
]);
```

> **Note:** Organization APIs require Keycloak 24+ with organizations feature enabled.

All other api calls are same as examples just needs to provide required parameters as described in https://www.keycloak.org/docs-api/7.0/rest-api/index.html
