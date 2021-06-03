<?php

namespace App\Libraries;

use Firebase\JWT\JWT;

class FirebaseJWTLibrary implements iJWTLibrary
{
    protected $key;
    protected $exp;
    protected $algo;

    public function __construct()
    {
        $this->key = config('keys.firebase_jwt.key');
        $this->exp = config('keys.firebase_jwt.exp');
        $this->algo = config('keys.firebase_jwt.algo');
    }

    public function encode($payload)
    {
        return JWT::encode($payload, $this->key);
    }

    public function getExpiry()
    {
        return $this->exp;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function decode($token)
    {
        return JWT::decode($token, $this->key, [$this->algo]);
    }
}
