<?php

namespace App\Collaborators;

use Illuminate\Http\Response;
use App\Collaborators\Contracts\iClient;
use App\Exceptions\MethodNotAllowedException;
use stdClass;

class ApiClient
{
    /**
     * @var App\Collaborators\Contracts\iClient
     */
    private $client;

    /**
     * @var array
     */
    private $allowed_methods = ['POST', 'GET', 'PUT', 'PATCH'];

    /**
     * Constructor sets the client
     *
     * @param \App\Collaborators\Contracts\iClient
     */
    public function __construct(iClient $client)
    {
        $this->client = $client;
    }

    /**
     * Internal (private) method to make requests to Goodtalk API
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return \App\Collaborators\ApiClient
     */
    private function request($method, $uri = '', array $options = []): ApiClient
    {
        $params = [];

        if (!in_array($method, $this->allowed_methods)) {
            throw new MethodNotAllowedException(
                "Method not allowed.",
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        }

        if (array_key_exists('form_params', $options)) {
            $params["form_params"] = $options['form_params'];
        }

        $this->client->request($method, $uri, $params);

        return $this;
    }

    /**
     * Helper method to make post requests
     *
     * @param string $provider
     * @param array $options
     *
     * @return \App\Collaborators\ApiClient
     */
    public function post($provider, $options): ApiClient
    {
        $uri = config('env.dev.login_url') . "/$provider";

        return $this->request("POST", $uri, $options);
    }

    /**
     * Helper method to make get requests
     *
     * @param string $provider
     * @param array $options
     *
     * @return \App\Collaborators\ApiClient
     */
    public function get($provider, $options): ApiClient
    {
        $uri = config('env.dev.login_url') . "/$provider";

        return $this->request("GET", $uri, $options);
    }

    /**
     * Helper method to make put requests
     *
     * @param string $provider
     * @param array $options
     *
     * @return \App\Collaborators\ApiClient
     */
    public function put($provider, $options): ApiClient
    {
        $uri = config('env.dev.login_url') . "/$provider";

        return $this->request("PUT", $uri, $options);
    }

    /**
     * Helper method to make patch requests
     *
     * @param string $provider
     * @param array $options
     *
     * @return \App\Collaborators\ApiClient
     */
    public function patch($provider, $options): ApiClient
    {
        $uri = config('env.dev.login_url') . "/$provider";

        return $this->request("PATCH", $uri, $options);
    }

    /**
     * Helper method to get response from request
     *
     * @return stdClass
     */
    public function getContents(): stdClass
    {
        return json_decode($this->client->getContents());
    }
}
