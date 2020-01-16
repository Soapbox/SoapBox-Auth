<?php

namespace App\Collaborators\Adapters;

use GuzzleHttp\Client;
use App\Collaborators\Contracts\iClient;

final class GuzzleAdapter implements iClient
{
    private $client;
    private $response;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request($method, $uri, $params)
    {
        $this->response = $this->client->request($method, $uri, $params);
        return $this->response;
    }

    public function getBody()
    {
        return $this->response->getBody();
    }

    public function getContents()
    {
        return $this->response->getBody()->getContents();
    }
}
