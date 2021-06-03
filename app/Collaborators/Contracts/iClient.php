<?php

namespace App\Collaborators\Contracts;

interface iClient
{
    public function request($method, $uri, $params);

    public function getBody();

    public function getContents();
}
