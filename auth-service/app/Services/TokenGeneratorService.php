<?php

namespace App\Services;

use App\Libraries\iJWTLibrary;
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
	protected $jwt_library;

	const ISS = "http://auth-server.test";
	const AUD = "http://api-gateway.test";

	public function __construct(iJWTLibrary $jwtLibrary)
	{
		$this->jwt_library = $jwtLibrary;
	}

	/**
	 * Generate jwt token
	 * @throws UserNotFoundException
	 */
	public function generateToken()
	{
		if ($this->provider && $this->code && in_array($this->provider, config('support.providers'))) {
			$socialProviderUser = Socialite::driver($this->provider)->userFromToken($this->code);

			$userExist = true; //assumption

			if ($userExist) {
				$this->payload = $this->generatePayload($socialProviderUser);
				$this->token = $this->jwt_library->encode($this->payload);
			} else {
				throw new UserNotFoundException('User not found.: ' . $socialProviderUser->getEmail(), Response::HTTP_NOT_FOUND);
			}
		} else {
			throw new \InvalidArgumentException('Provider and code must be set', Response::HTTP_FORBIDDEN);
		}

		return $this->token;
	}

	/**
	 * Generate payload
	 * @param SocialProviderUser $user
	 * @return array
	 */
	protected function generatePayload(SocialProviderUser $user): array
	{
		return array(
			"iss" => self::ISS,
			"aud" => self::AUD,
			"iat" => time(),
			"exp" => strtotime('+1 '. $this->jwt_library->getExpiry()),
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

	/**
	 * @return mixed
	 */
	public function getIat()
	{
		return $this->payload["iat"];
	}
}
