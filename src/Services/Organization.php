<?php

namespace laravelKeycloakAdminRestApi\Services;

use InvalidArgumentException;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use GuzzleHttp\ClientInterface;

class Organization extends Service
{
    /**
     * Organization constructor.
     * @param ClientAuthService $auth
     * @param ClientInterface $http
     */
    public function __construct(ClientAuthService $auth, ClientInterface $http)
    {
        parent::__construct($auth, $http);
        $this->api = config('keycloakAdmin.api.organization');
    }

    /**
     * Handle response and return parsed JSON or boolean
     *
     * @param $response
     * @return array|bool
     */
    public function response($response)
    {
        if (!empty($location = $response->getHeader('location'))) {
            $url = current($location);

            return $this->get([
                'id' => substr($url, strrpos($url, '/') + 1)
            ]);
        }

        $body = $response->getBody()->getContents();

        return json_decode($body, true) ?: true;
    }

    /**
     * Add a member to an organization
     *
     * @param array $options ['id' => organizationId, 'userId' => userId]
     * @return array|bool
     */
    public function addMember(array $options)
    {
        $this->validate($options, ['id', 'userId']);
        return $this->__call('addMember', [$options]);
    }

    /**
     * Remove a member from an organization
     *
     * @param array $options ['id' => organizationId, 'userId' => userId]
     * @return array|bool
     */
    public function removeMember(array $options)
    {
        $this->validate($options, ['id', 'userId']);
        return $this->__call('removeMember', [$options]);
    }

    /**
     * Get members of an organization
     *
     * @param array $options ['id' => organizationId]
     * @return array|bool
     */
    public function getMembers(array $options)
    {
        $this->validate($options, ['id']);
        return $this->__call('getMembers', [$options]);
    }

    /**
     * Search organizations by name or other criteria
     *
     * @param array $options ['search' => searchString] or ['query' => [...]]
     * @return array|bool
     */
    public function search(array $options)
    {
        if (isset($options['search'])) {
            return $this->__call('all', [
                'query' => ['search' => $options['search']]
            ]);
        }

        return $this->__call('all', [$options]);
    }

    /**
     * Invite a user to an organization
     *
     * @param array $options ['id' => organizationId, 'body' => invitation data]
     * @return array|bool
     */
    public function inviteUser(array $options)
    {
        $this->validate($options, ['id']);
        return $this->__call('inviteUser', [$options]);
    }

    /**
     * Get organization identity providers
     *
     * @param array $options ['id' => organizationId]
     * @return array|bool
     */
    public function getIdentityProviders(array $options)
    {
        $this->validate($options, ['id']);
        return $this->__call('getIdentityProviders', [$options]);
    }

    /**
     * Link an identity provider to an organization
     *
     * @param array $options ['id' => organizationId, 'alias' => idp alias]
     * @return array|bool
     */
    public function linkIdentityProvider(array $options)
    {
        $this->validate($options, ['id', 'alias']);
        return $this->__call('linkIdentityProvider', [$options]);
    }

    /**
     * Unlink an identity provider from an organization
     *
     * @param array $options ['id' => organizationId, 'alias' => idp alias]
     * @return array|bool
     */
    public function unlinkIdentityProvider(array $options)
    {
        $this->validate($options, ['id', 'alias']);
        return $this->__call('unlinkIdentityProvider', [$options]);
    }

    /**
     * Get member organizations for a user
     *
     * @param array $options ['userId' => userId]
     * @return array|bool
     */
    public function getUserOrganizations(array $options)
    {
        $this->validate($options, ['userId']);
        return $this->__call('getUserOrganizations', [$options]);
    }

    /**
     * Validate required options
     *
     * @param array $data
     * @param array $requiredKeys
     * @throws InvalidArgumentException
     */
    private function validate(array $data, array $requiredKeys): void
    {
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException("Missing required option: {$key}");
            }
        }
    }
}

