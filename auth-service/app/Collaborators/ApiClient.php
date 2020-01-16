<?php

namespace App\Collaborators;

use Illuminate\Http\Response;
use App\Collaborators\Contracts\iClient;
use App\Exceptions\MethodNotAllowedException;

class ApiClient
{
    private $client;
    private $allowed_methods = ['POST', 'GET', 'PUT', 'PATCH'];

    public function __construct(iClient $client)
    {
        $this->client = $client;
    }

    public function request($method, $uri = '', array $options = [])
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

    public function getContents()
    {
        return json_decode($this->client->getContents());
    }
}
