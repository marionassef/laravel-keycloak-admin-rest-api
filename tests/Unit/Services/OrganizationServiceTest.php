<?php

namespace laravelKeycloakAdminRestApi\Tests\Unit\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use laravelKeycloakAdminRestApi\Services\Organization;
use laravelKeycloakAdminRestApi\Tests\TestCase;
use Mockery;

class OrganizationServiceTest extends TestCase
{
    protected ClientAuthService $mockAuth;
    protected ClientInterface $mockHttp;
    protected Organization $organizationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAuth = Mockery::mock(ClientAuthService::class);
        $this->mockAuth->shouldReceive('getToken')
            ->andReturn('test-token');

        $this->mockHttp = Mockery::mock(ClientInterface::class);

        $this->organizationService = new Organization($this->mockAuth, $this->mockHttp);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_all_organizations(): void
    {
        $organizations = [
            ['id' => 'org-1', 'name' => 'Acme Corp', 'alias' => 'acme'],
            ['id' => 'org-2', 'name' => 'Tech Inc', 'alias' => 'tech']
        ];

        $response = new Response(200, [], json_encode($organizations));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/organizations$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->all([]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Acme Corp', $result[0]['name']);
    }

    public function test_get_organization_by_id(): void
    {
        $organization = [
            'id' => 'org-123',
            'name' => 'Acme Corp',
            'alias' => 'acme',
            'enabled' => true,
            'domains' => ['acme.com']
        ];
        $response = new Response(200, [], json_encode($organization));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/organizations\/org-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->get(['id' => 'org-123']);

        $this->assertEquals('Acme Corp', $result['name']);
        $this->assertEquals('acme', $result['alias']);
        $this->assertTrue($result['enabled']);
    }

    public function test_create_organization_returns_created_organization(): void
    {
        $orgId = 'new-org-uuid';
        $locationHeader = "http://localhost:8080/auth/admin/realms/master/organizations/{$orgId}";
        
        // Create response with location header
        $createResponse = new Response(201, ['location' => $locationHeader], '');
        
        // Get response returns the organization
        $organization = ['id' => $orgId, 'name' => 'New Org', 'alias' => 'new-org'];
        $getResponse = new Response(200, [], json_encode($organization));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/organizations$/'), Mockery::type('array'))
            ->andReturn($createResponse);

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern("/\/organizations\/{$orgId}$/"), Mockery::type('array'))
            ->andReturn($getResponse);

        $result = $this->organizationService->create([
            'body' => ['name' => 'New Org', 'alias' => 'new-org', 'enabled' => true]
        ]);

        $this->assertEquals($orgId, $result['id']);
        $this->assertEquals('New Org', $result['name']);
    }

    public function test_update_organization(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/organizations\/org-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->update([
            'id' => 'org-123',
            'body' => ['name' => 'Updated Org Name']
        ]);

        $this->assertTrue($result);
    }

    public function test_delete_organization(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/organizations\/org-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->delete(['id' => 'org-123']);

        $this->assertTrue($result);
    }

    public function test_get_organization_members(): void
    {
        $members = [
            ['id' => 'user-1', 'username' => 'john'],
            ['id' => 'user-2', 'username' => 'jane']
        ];
        $response = new Response(200, [], json_encode($members));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/organizations\/org-123\/members$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->getMembers(['id' => 'org-123']);

        $this->assertCount(2, $result);
        $this->assertEquals('john', $result[0]['username']);
    }

    public function test_get_members_throws_exception_when_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: id');

        $this->organizationService->getMembers([]);
    }

    public function test_add_member_to_organization(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/organizations\/org-123\/members\/user-456$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->addMember([
            'id' => 'org-123',
            'userId' => 'user-456'
        ]);

        $this->assertTrue($result);
    }

    public function test_add_member_throws_exception_when_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: id');

        $this->organizationService->addMember(['userId' => 'user-456']);
    }

    public function test_add_member_throws_exception_when_user_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: userId');

        $this->organizationService->addMember(['id' => 'org-123']);
    }

    public function test_remove_member_from_organization(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/organizations\/org-123\/members\/user-456$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->removeMember([
            'id' => 'org-123',
            'userId' => 'user-456'
        ]);

        $this->assertTrue($result);
    }

    public function test_remove_member_throws_exception_when_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: id');

        $this->organizationService->removeMember(['userId' => 'user-456']);
    }

    public function test_invite_user_to_organization(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/organizations\/org-123\/members\/invite-user$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->inviteUser([
            'id' => 'org-123',
            'body' => [
                'email' => 'newuser@example.com',
                'firstName' => 'New',
                'lastName' => 'User'
            ]
        ]);

        $this->assertTrue($result);
    }

    public function test_invite_user_throws_exception_when_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: id');

        $this->organizationService->inviteUser([
            'body' => ['email' => 'test@example.com']
        ]);
    }

    public function test_get_identity_providers(): void
    {
        $idps = [
            ['alias' => 'google', 'displayName' => 'Google'],
            ['alias' => 'github', 'displayName' => 'GitHub']
        ];
        $response = new Response(200, [], json_encode($idps));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/organizations\/org-123\/identity-providers$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->getIdentityProviders(['id' => 'org-123']);

        $this->assertCount(2, $result);
        $this->assertEquals('google', $result[0]['alias']);
    }

    public function test_get_identity_providers_throws_exception_when_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: id');

        $this->organizationService->getIdentityProviders([]);
    }

    public function test_link_identity_provider(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/organizations\/org-123\/identity-providers\/google$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->linkIdentityProvider([
            'id' => 'org-123',
            'alias' => 'google'
        ]);

        $this->assertTrue($result);
    }

    public function test_link_identity_provider_throws_exception_when_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: id');

        $this->organizationService->linkIdentityProvider(['alias' => 'google']);
    }

    public function test_link_identity_provider_throws_exception_when_alias_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: alias');

        $this->organizationService->linkIdentityProvider(['id' => 'org-123']);
    }

    public function test_unlink_identity_provider(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/organizations\/org-123\/identity-providers\/google$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->unlinkIdentityProvider([
            'id' => 'org-123',
            'alias' => 'google'
        ]);

        $this->assertTrue($result);
    }

    public function test_unlink_identity_provider_throws_exception_when_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: id');

        $this->organizationService->unlinkIdentityProvider(['alias' => 'google']);
    }

    public function test_get_user_organizations(): void
    {
        $organizations = [
            ['id' => 'org-1', 'name' => 'Acme Corp'],
            ['id' => 'org-2', 'name' => 'Tech Inc']
        ];
        $response = new Response(200, [], json_encode($organizations));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/users\/user-123\/organizations$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->getUserOrganizations(['userId' => 'user-123']);

        $this->assertCount(2, $result);
        $this->assertEquals('Acme Corp', $result[0]['name']);
    }

    public function test_get_user_organizations_throws_exception_when_user_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: userId');

        $this->organizationService->getUserOrganizations([]);
    }

    public function test_search_organizations(): void
    {
        $organizations = [
            ['id' => 'org-1', 'name' => 'Acme Corp']
        ];
        $response = new Response(200, [], json_encode($organizations));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/organizations$/'), Mockery::on(function ($options) {
                return isset($options['query']['search']) && $options['query']['search'] === 'acme';
            }))
            ->andReturn($response);

        $result = $this->organizationService->search(['search' => 'acme']);

        $this->assertCount(1, $result);
        $this->assertEquals('Acme Corp', $result[0]['name']);
    }

    public function test_search_organizations_with_query_options(): void
    {
        $organizations = [
            ['id' => 'org-1', 'name' => 'Acme Corp'],
            ['id' => 'org-2', 'name' => 'Tech Inc']
        ];
        $response = new Response(200, [], json_encode($organizations));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/organizations$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->organizationService->search(['query' => ['first' => 0, 'max' => 10]]);

        $this->assertCount(2, $result);
    }

    public function test_response_handles_json_response(): void
    {
        $orgData = ['id' => 'org-123', 'name' => 'Acme Corp'];
        $response = new Response(200, [], json_encode($orgData));

        $result = $this->organizationService->response($response);

        $this->assertEquals($orgData, $result);
    }

    public function test_response_handles_empty_response(): void
    {
        $response = new Response(204, [], '');

        $result = $this->organizationService->response($response);

        $this->assertTrue($result);
    }

    public function test_response_handles_location_header(): void
    {
        $orgId = 'new-org-uuid';
        $locationHeader = "http://localhost:8080/auth/admin/realms/master/organizations/{$orgId}";
        
        $createResponse = new Response(201, ['location' => $locationHeader], '');
        
        $organization = ['id' => $orgId, 'name' => 'New Org'];
        $getResponse = new Response(200, [], json_encode($organization));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern("/\/organizations\/{$orgId}$/"), Mockery::type('array'))
            ->andReturn($getResponse);

        $result = $this->organizationService->response($createResponse);

        $this->assertEquals($orgId, $result['id']);
        $this->assertEquals('New Org', $result['name']);
    }
}

