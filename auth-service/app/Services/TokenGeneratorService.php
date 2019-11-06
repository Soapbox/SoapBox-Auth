<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use App\Exceptions\UserNotFoundException;
use Laravel\Socialite\Two\User as SocialProviderUser;

class TokenGeneratorService
{
	protected $key;
	protected $exp;
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
		if ($this->provider && $this->code && in_array($this->provider, ['google', 'slack', 'microsoft'])) {
			$socialProviderUser = Socialite::driver($this->provider)->userFromToken($this->code);

			$userExist = true; //assumption

			if ($userExist) {
				$this->generatePayload($socialProviderUser);
				$this->token = JWT::encode($this->payload, $this->key);

				return $this->token;
			} else {
				throw new UserNotFoundException('User not found.: ' . $socialProviderUser->getEmail(), Response::HTTP_NOT_FOUND);
			}
		} else {
			throw new \InvalidArgumentException('Provider and code must be set', Response::HTTP_BAD_REQUEST);
		}
	}

	/**
	 * Generate payload
	 * @param SocialProviderUser $user
	 */
	protected function generatePayload(SocialProviderUser $user): void
	{
		$this->payload = array(
			"iss" => self::ISS,
			"aud" => self::AUD,
			"iat" => time(),
			"exp" => strtotime('+1 '. $this->exp),
			"name" => $user->getName(),
			"email" => $user->getEmail()
		);
	}

	public function setProvider($provider)
	{
		$this->provider = $provider;
	}

	public function setCode($code)
	{
		$this->code = $code;
	}
}
