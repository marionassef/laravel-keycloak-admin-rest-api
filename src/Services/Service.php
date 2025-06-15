<?php


namespace laravelKeycloakAdminRestApi\Services;


use GuzzleHttp\ClientInterface;
use Illuminate\Support\Arr;
use laravelKeycloakAdminRestApi\Auth\ClientAuthService;

abstract class Service
{
    /*
    * Api uri's
    */
    protected $api = [];

    /*
     * Http client
     */
    protected $http;

    /*
     * Client authorization service
     */
    protected $auth;

    public function __construct(ClientAuthService $auth , ClientInterface $http)
    {
        $this->auth = $auth;
        $this->http = $http;
    }

    public function __call($api, $args)
    {
        $args = Arr::collapse($args);

        [$url , $method] = $this->getApi($api, $args);

        $response = $this
            ->http
            ->request($method, $url, $this->createOptions($args));

        return $this->response($response);
    }

    /**
     * Creates guzzle http clinet options
     * @param array|null $params
     * @return array
     */
    public function createOptions(array $params = null) : array
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->auth->getToken(),
            ],
        ];

        // Optional body for POST/PUT
        if (!empty($params['body'])) {
            $options['json'] = $params['body'];
        }

        if (isset($params['search'])) {
            $options['query'] = ['search' => $params['search']];
        }

        // Or: if already passed 'query' key, honor it
        if (isset($params['query']) && is_array($params['query'])) {
            $options['query'] = $params['query'];
        }

        return $options;
    }

    public function getApi($apiName, $values)
    {
        return $this->initApi($apiName, $values) ;
    }

    public function initApi($apiName, $values)
    {
        $api = $this->api[$apiName]['api'];

        foreach($values as $name => $value) {
            if (is_string($value)) {
                $api = str_replace('{'.$name.'}', $value, $api);
            }
        }

        if (isset($values['query'])){
            $api = $api . '?' . http_build_query($values['query']);
        }

        return [$api ,$this->api[$apiName]['method']];
    }
}
