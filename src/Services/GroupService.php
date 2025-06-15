<?php

namespace laravelKeycloakAdminRestApi\Services;

use InvalidArgumentException;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use GuzzleHttp\ClientInterface;

class GroupService extends Service
{
    /**
     * Group constructor.
     * @param ClientAuthService $auth
     * @param ClientInterface $http
     */
    public function __construct(ClientAuthService $auth, ClientInterface $http)
    {
        parent::__construct($auth, $http);
        $this->api = config('keycloakAdmin.api.group'); // Define this in config file
    }

    /**
     * Handle response and return parsed JSON or boolean
     *
     * @param $response
     * @return array|bool
     */
    public function response($response)
    {
        $body = $response->getBody()->getContents();

        return json_decode($body, true) ?: true;
    }

    public function addUser(array $options)
    {
        $this->validate($options, ['user_id', 'group_id']);
        return $this->__call('addUser', $options);
    }

    public function removeUser(array $options)
    {
        $this->validate($options, ['user_id', 'group_id']);
        return $this->__call('removeUser', $options);
    }

    public function search(array $options)
    {
        $this->validate($options, ['name']);

        return $this->__call('search', [
            'query' => ['search' => $options['name']]
        ]);
    }

    private function validate(array $data, array $requiredKeys): void
    {
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException("Missing required option: {$key}");
            }
        }
    }
}
