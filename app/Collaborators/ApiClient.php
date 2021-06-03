<?php

namespace App\Collaborators;

use App\Collaborators\Contracts\iClient;
use App\Exceptions\MethodNotAllowedException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response;
use stdClass;

class ApiClient
{
    /**
     * @var App\Collaborators\Contracts\iClient
     */
    private $client;

    /**
     * @var string
     */
    private $base_url;

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
        $this->base_url = config('env.dev.login_url');
    }

    /**
     * Internal (private) method to make requests to Goodtalk API
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    private function request(
        string $method,
        string $uri = '',
        array $options = []
    ): GuzzleResponse {
        if (! in_array($method, $this->allowed_methods)) {
            throw new MethodNotAllowedException(
                'Method not allowed.',
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        }

        $response = $this->client->request($method, $uri, $options);

        return $response;
    }

    /**
     * Helper method to make post requests
     *
     * @param string $provider
     * @param array $options
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function post(string $path, array $options): GuzzleResponse
    {
        $uri = $this->base_url."/$path";

        $options = [
            'json' => $options,
        ];

        return $this->request('POST', $uri, $options);
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
