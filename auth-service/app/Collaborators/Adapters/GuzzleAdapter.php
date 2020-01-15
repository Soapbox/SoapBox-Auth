<?php


namespace App\Collaborators\Adapters;

use GuzzleHttp\Client;
use App\Collaborators\Contracts\iClient;

final class GuzzleAdapter implements iClient
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request($method, $uri, $params)
    {
        return $this->client->request($method, $uri, $params);
    }

    public function getBody()
    {
        return $this->client->getBody();
    }

    public function getContents()
    {
        return $this->client->getBody()->getContents();
    }
}
