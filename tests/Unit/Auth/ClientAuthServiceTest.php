<?php

namespace laravelKeycloakAdminRestApi\Tests\Unit\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use laravelKeycloakAdminRestApi\Tests\TestCase;
use Mockery;

class ClientAuthServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_token_returns_access_token(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $tokenResponse = [
            'access_token' => 'test-access-token',
            'expires_in' => 300,
            'token_type' => 'Bearer'
        ];

        $response = new Response(200, [], json_encode($tokenResponse));
        $mockClient->shouldReceive('post')
            ->once()
            ->andReturn($response);

        $authService = new ClientAuthService();
        
        // Use reflection to inject mock client
        $reflection = new \ReflectionClass($authService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($authService, $mockClient);

        // New cache key not found
        Cache::shouldReceive('has')
            ->once()
            ->with('keycloak-admin-credentials')
            ->andReturn(false);

        // Legacy cache key not found either
        Cache::shouldReceive('has')
            ->once()
            ->with('keycload-admin-credentials')
            ->andReturn(false);

        Cache::shouldReceive('put')
            ->once()
            ->with('keycloak-admin-credentials', $tokenResponse, 300);

        $token = $authService->getToken();

        $this->assertEquals('test-access-token', $token);
    }

    public function test_get_token_uses_cached_credentials(): void
    {
        $cachedCredentials = [
            'access_token' => 'cached-access-token',
            'expires_in' => 300,
            'token_type' => 'Bearer'
        ];

        Cache::shouldReceive('has')
            ->once()
            ->with('keycloak-admin-credentials')
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->with('keycloak-admin-credentials')
            ->andReturn($cachedCredentials);

        $authService = new ClientAuthService();
        $token = $authService->getToken();

        $this->assertEquals('cached-access-token', $token);
    }

    public function test_get_token_migrates_legacy_cache_key(): void
    {
        $cachedCredentials = [
            'access_token' => 'legacy-cached-token',
            'expires_in' => 300,
            'token_type' => 'Bearer'
        ];

        // New key not found
        Cache::shouldReceive('has')
            ->once()
            ->with('keycloak-admin-credentials')
            ->andReturn(false);

        // Legacy key found
        Cache::shouldReceive('has')
            ->once()
            ->with('keycload-admin-credentials')
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->with('keycload-admin-credentials')
            ->andReturn($cachedCredentials);

        // Migrate to new key
        Cache::shouldReceive('put')
            ->once()
            ->with('keycloak-admin-credentials', $cachedCredentials, 300);

        // Remove legacy key
        Cache::shouldReceive('forget')
            ->once()
            ->with('keycload-admin-credentials');

        $authService = new ClientAuthService();
        $token = $authService->getToken();

        $this->assertEquals('legacy-cached-token', $token);
    }

    public function test_get_authorization_token_returns_full_credentials(): void
    {
        $mockClient = Mockery::mock(Client::class);
        $tokenResponse = [
            'access_token' => 'test-access-token',
            'expires_in' => 300,
            'token_type' => 'Bearer',
            'refresh_token' => 'test-refresh-token'
        ];

        $response = new Response(200, [], json_encode($tokenResponse));
        $mockClient->shouldReceive('post')
            ->once()
            ->andReturn($response);

        $authService = new ClientAuthService();
        
        $reflection = new \ReflectionClass($authService);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($authService, $mockClient);

        // New cache key not found
        Cache::shouldReceive('has')
            ->once()
            ->with('keycloak-admin-credentials')
            ->andReturn(false);

        // Legacy cache key not found either
        Cache::shouldReceive('has')
            ->once()
            ->with('keycload-admin-credentials')
            ->andReturn(false);

        Cache::shouldReceive('put')
            ->once()
            ->with('keycloak-admin-credentials', $tokenResponse, 300);

        $credentials = $authService->getAuthorizationToken();

        $this->assertIsArray($credentials);
        $this->assertArrayHasKey('access_token', $credentials);
        $this->assertArrayHasKey('expires_in', $credentials);
        $this->assertEquals('test-access-token', $credentials['access_token']);
    }

    public function test_get_options_returns_correct_structure(): void
    {
        $authService = new ClientAuthService();
        $options = $authService->getOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);
        $this->assertArrayHasKey('form_params', $options);
        $this->assertEquals('application/x-www-form-urlencoded', $options['headers']['Content-Type']);
        $this->assertEquals('client_credentials', $options['form_params']['grant_type']);
        $this->assertArrayHasKey('client_id', $options['form_params']);
        $this->assertArrayHasKey('client_secret', $options['form_params']);
    }
}

