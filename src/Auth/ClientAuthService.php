<?php

namespace laravelKeycloakAdminRestApi\Auth;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class ClientAuthService
{
    /**
     * Cache key for storing admin credentials
     */
    protected const CACHE_KEY = 'keycloak-admin-credentials';

    /**
     * Legacy cache key (for backward compatibility)
     * @deprecated Will be removed in v3.0
     */
    protected const LEGACY_CACHE_KEY = 'keycload-admin-credentials';

    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->getAuthorizationToken()['access_token'];
    }

    /**
     * @return array
     */
    public function getAuthorizationToken() : array
    {
        $api = config('keycloakAdmin.api.client.token');

        // Check new cache key first, then legacy key for backward compatibility
        if (Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }

        // Backward compatibility: check legacy cache key and migrate if found
        if (Cache::has(self::LEGACY_CACHE_KEY)) {
            $credentials = Cache::get(self::LEGACY_CACHE_KEY);
            // Migrate to new cache key
            Cache::put(self::CACHE_KEY, $credentials, $credentials['expires_in'] ?? 300);
            Cache::forget(self::LEGACY_CACHE_KEY);
            return $credentials;
        }

        $response = $this->client->post($api, $this->getOptions());

        $credentials = json_decode($response->getBody()->getContents(),true);

        return tap($credentials, function ($credentials) {
            Cache::put(self::CACHE_KEY, $credentials, $credentials['expires_in']);
        });
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $options = [

            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => config('keycloakAdmin.client.id'),
                'client_secret' => config('keycloakAdmin.client.secret') ,
            ]
        ];
    }
}
