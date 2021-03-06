<?php

namespace laravelKeycloakAdminRestApi\Services;

use laravelKeycloakAdminRestApi\Auth\ClientAuthService;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Arr;

class ClientRole extends Service
{

    /**
     * ClientRole constructor.
     * @param ClientAuthService $auth
     * @param ClientInterface $http
     */
    function __construct(ClientAuthService $auth , ClientInterface $http)
    {
        parent::__construct($auth, $http);

        $this->api = config('keycloakAdmin.api.client_roles');
    }

    /**
     * @param $response
     * @return bool
     */
    public function response($response)
    {
        if (!empty( $location = $response->getHeader('location') )){

            $url = current($location) ;

            return $this->getByName([
                'id' => substr( $url , strrpos( $url , 'clients/') + 8, 36 ),
                'role' => substr( $url , strrpos( $url , '/') + 1 )
            ]);
        }

        return json_decode($response->getBody()->getContents() , true) ?: true;
    }
}
