<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Exceptions\UserNotFoundException;
use Laravel\Socialite\Two\User as SocialProviderUser;

class TokenGeneratorService
{
	protected static $token;
	protected static $code;
	protected static $message;
	protected static $payload;
	protected static $user;
	protected static $key;

	/**
	 * Get the User from Provider using the oauth_code provided in the request
	 * Generate jwt token using the user->name and user->email as part of the payload
	 *
	 * @param Request $request
	 * @return TokenGeneratorService
	 */
	public static function generateToken(Request $request)
	{
		self::$key = env('JWT_KEY');

		try {
			$socialProviderUser = Socialite::driver($request->provider)->userFromToken($request->oauth_code);

			self::$user = self::findOrFail($socialProviderUser);

			if (self::$user) {
				self::generatePayload();

				self::$token = JWT::encode(self::$payload, self::$key);
				self::$code = Response::HTTP_OK;
				self::$message = "Success";
			}
		} catch (UserNotFoundException $e) {
			Log::info('There was an error.', [
				'error' => $e->getMessage()
			]);
		}

		return new self();
	}

	public static function getToken()
	{
		return self::$token;
	}

	public static function getCode()
	{
		return self::$code;
	}

	public static function getMessage()
	{
		return self::$message;
	}

	protected static function generatePayload()
	{
		$payload = array(
			"iss" => "http://auth-server.test",
			"aud" => "http://api-gateway.test",
			"iat" => time(),
			"exp" => strtotime('+1 '. env('JWT_EXP')),
			"name" => self::$user->getName(),
			"email" => self::$user->getEmail()
		);

		self::$payload = $payload;
	}

	protected static function findOrFail(SocialProviderUser $user)
	{
		//TODO: confirm that $user->getEmail() record actually exist in the users table
		//test does not cover this yet mainly because we have agreed to assume that this
		//returns true always for now

		$userExist = true;

		if (!$userExist) {
			throw new UserNotFoundException('User not found.: ' . $user->getEmail());
		}

		return $user;
	}
}
