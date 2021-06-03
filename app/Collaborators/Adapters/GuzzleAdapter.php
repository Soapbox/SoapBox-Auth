<?php

namespace App\Collaborators\Adapters;

use App\Collaborators\Contracts\iClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;

final class GuzzleAdapter implements iClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \GuzzleHttp\Psr7\Response
     */
    private $response;

    /**
     * Constructor sets the client
     *
     * @param \GuzzleHttp\Client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Method to make requests to external API
     *
     * @param string $method
     * @param string $uri
     * @param array $params
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function request($method, $uri, $params): Response
    {
        $this->response = $this->client->request($method, $uri, $params);

        return $this->response;
    }

    /**
     * Return the stream for the response
     *
     * @return \GuzzleHttp\Psr7\Stream;
     */
    public function getBody(): Stream
    {
        return $this->response->getBody();
    }

    /**
     *  @return string
     */
    public function getContents(): string
    {
        return $this->response->getBody()->getContents();
    }
}
