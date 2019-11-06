<?php

namespace App\Libraries;

use Firebase\JWT\JWT;

class FirebaseJWTLibrary implements iJWTLibrary
{
	protected $key;
	protected $exp;

	public function __construct()
	{
		$this->key = config('keys.firebase_jwt.key');
		$this->exp = config('keys.firebase_jwt.exp');
	}

	public function encode($payload)
	{
		return JWT::encode($payload, $this->key);
	}
}