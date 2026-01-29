<?php

namespace laravelKeycloakAdminRestApi\Tests\Unit\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use laravelKeycloakAdminRestApi\Services\GroupService;
use laravelKeycloakAdminRestApi\Tests\TestCase;
use Mockery;

class GroupServiceTest extends TestCase
{
    protected ClientAuthService $mockAuth;
    protected ClientInterface $mockHttp;
    protected GroupService $groupService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockAuth = Mockery::mock(ClientAuthService::class);
        $this->mockAuth->shouldReceive('getToken')
            ->andReturn('test-token');

        $this->mockHttp = Mockery::mock(ClientInterface::class);

        $this->groupService = new GroupService($this->mockAuth, $this->mockHttp);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_all_groups(): void
    {
        $groups = [
            ['id' => 'group-1', 'name' => 'admins', 'path' => '/admins'],
            ['id' => 'group-2', 'name' => 'users', 'path' => '/users']
        ];

        $response = new Response(200, [], json_encode($groups));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/groups$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->groupService->list([]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('admins', $result[0]['name']);
    }

    public function test_get_group_by_id(): void
    {
        $group = ['id' => 'group-123', 'name' => 'admins', 'path' => '/admins'];
        $response = new Response(200, [], json_encode($group));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/groups\/group-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->groupService->get(['id' => 'group-123']);

        $this->assertEquals('admins', $result['name']);
        $this->assertEquals('/admins', $result['path']);
    }

    public function test_create_group(): void
    {
        $response = new Response(201, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('post', Mockery::pattern('/\/groups$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->groupService->create([
            'body' => ['name' => 'new-group']
        ]);

        $this->assertTrue($result);
    }

    public function test_update_group(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('put', Mockery::pattern('/\/groups\/group-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->groupService->update([
            'id' => 'group-123',
            'body' => ['name' => 'updated-group']
        ]);

        $this->assertTrue($result);
    }

    public function test_delete_group(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('delete', Mockery::pattern('/\/groups\/group-123$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->groupService->delete(['id' => 'group-123']);

        $this->assertTrue($result);
    }

    public function test_get_group_members(): void
    {
        $members = [
            ['id' => 'user-1', 'username' => 'john'],
            ['id' => 'user-2', 'username' => 'jane']
        ];
        $response = new Response(200, [], json_encode($members));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('get', Mockery::pattern('/\/groups\/group-123\/members$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->groupService->getMembers(['id' => 'group-123']);

        $this->assertCount(2, $result);
        $this->assertEquals('john', $result[0]['username']);
    }

    public function test_add_user_to_group(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('PUT', Mockery::pattern('/\/users\/user-123\/groups\/group-456$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->groupService->addUser([
            'user_id' => 'user-123',
            'group_id' => 'group-456'
        ]);

        $this->assertTrue($result);
    }

    public function test_add_user_throws_exception_when_user_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: user_id');

        $this->groupService->addUser(['group_id' => 'group-456']);
    }

    public function test_add_user_throws_exception_when_group_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: group_id');

        $this->groupService->addUser(['user_id' => 'user-123']);
    }

    public function test_remove_user_from_group(): void
    {
        $response = new Response(204, [], '');

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('DELETE', Mockery::pattern('/\/users\/user-123\/groups\/group-456$/'), Mockery::type('array'))
            ->andReturn($response);

        $result = $this->groupService->removeUser([
            'user_id' => 'user-123',
            'group_id' => 'group-456'
        ]);

        $this->assertTrue($result);
    }

    public function test_remove_user_throws_exception_when_user_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: user_id');

        $this->groupService->removeUser(['group_id' => 'group-456']);
    }

    public function test_search_groups_by_name(): void
    {
        $groups = [
            ['id' => 'group-1', 'name' => 'test-group', 'path' => '/test-group']
        ];
        $response = new Response(200, [], json_encode($groups));

        $this->mockHttp->shouldReceive('request')
            ->once()
            ->with('GET', Mockery::pattern('/\/groups$/'), Mockery::on(function ($options) {
                return isset($options['query']['search']) && $options['query']['search'] === 'test';
            }))
            ->andReturn($response);

        $result = $this->groupService->search(['name' => 'test']);

        $this->assertCount(1, $result);
        $this->assertEquals('test-group', $result[0]['name']);
    }

    public function test_search_throws_exception_when_name_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required option: name');

        $this->groupService->search([]);
    }

    public function test_response_handles_json_response(): void
    {
        $groupData = ['id' => 'group-123', 'name' => 'admins'];
        $response = new Response(200, [], json_encode($groupData));

        $result = $this->groupService->response($response);

        $this->assertEquals($groupData, $result);
    }

    public function test_response_handles_empty_response(): void
    {
        $response = new Response(204, [], '');

        $result = $this->groupService->response($response);

        $this->assertTrue($result);
    }
}

