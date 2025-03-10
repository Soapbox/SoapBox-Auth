<?php

namespace App\Collaborators\Adapters;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Response;
use App\Collaborators\Contracts\iClient;

final class GuzzleAdapter implements iClient
{
    /**
     * @var \GuzzleHttp\Psr7\Response
     */
    private $response;

    /**
     * Constructor sets the client
     *
     * @param \GuzzleHttp\Client
     */
    public function __construct(private Client $client)
    {
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
