<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Laravel\Socialite\Facades\Socialite;
use App\Exceptions\UserNotFoundException;

class TokenGeneratorService
{
	protected $key;
	protected $exp;
	protected $user;
	protected $code; //refers to oauth_code from provider
	protected $token;
	protected $payload;
	protected $provider; //ex google, slack, microsoft

	const ISS = "http://auth-server.test";
	const AUD = "http://api-gateway.test";

	public function __construct()
	{
		$this->key = env('JWT_KEY');
		$this->exp = env('JWT_EXP');
	}

	/**
	 * Generate jwt token
	 * @throws UserNotFoundException
	 */
	public function generateToken()
	{
		$socialProviderUser = Socialite::driver($this->provider)->userFromToken($this->code);

		$userExist = true;

		if ($userExist) {
			$this->user = $socialProviderUser;
			$this->generatePayload();
			$this->token = JWT::encode($this->payload, $this->key);

			return $this->token;
		} else {
			throw new UserNotFoundException('User not found.: ' . $socialProviderUser->getEmail());
		}
	}

	/**
	 * Generate payload
	 */
	protected function generatePayload(): void
	{
		$this->payload = array(
			"iss" => self::ISS,
			"aud" => self::AUD,
			"iat" => time(),
			"exp" => strtotime('+1 '. $this->exp),
			"name" => $this->user->getName(),
			"email" => $this->user->getEmail()
		);
	}

	public function setProvider($provider)
	{
		if ($provider) {
			$this->provider = $provider;
		}
	}

	public function setCode($code)
	{
		if ($code) {
			$this->code = $code;
		}
	}
}
