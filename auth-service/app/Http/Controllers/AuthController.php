<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialProviderUser;

class AuthController extends Controller
{

	protected $supported_providers = ['google', 'slack', 'microsoft'];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    public function login(Request $request)
	{
		$this->validate($request, [
			'oauth_code' => 'required|string',
			'provider' => 'required|string'
		]);

		if (!in_array($request->provider, $this->supported_providers)) {
			return response(
				[
					"token" => null,
					"message" => 'Provider not supported at this time.'
				], 404
			);
		}

		$response = $this->generateJWTToken($request);

		if ($response["status"] === 200) {
			app('redis')->sAdd(env('REDIS_KEY'), $response["jwt"]);
		}

		return response(
			[
				"token" => $response["jwt"],
				"message" => $response["message"]
			], $response["status"]
		);
	}

	protected function generateJWTToken(Request $request)
	{
		$response = [];

		try {
			$socialProviderUser = Socialite::driver($request->provider)->userFromToken($request->oauth_code);

			if ($this->userExist($socialProviderUser)) {
				$key = env('JWT_KEY');
				$exp = strtotime('+1 '. env('JWT_EXP'));

				$token = array(
					"iss" => "http://auth-server.test",
					"aud" => "http://api-gateway.test",
					"iat" => time(),
					"exp" => $exp,
					"name" => $socialProviderUser->getName(),
					"email" => $socialProviderUser->getEmail()
				);

				$response["jwt"] = JWT::encode($token, $key);
				$response["status"] = 200;
				$response["message"] = "Success";
			} else {
				$response["jwt"] = null;
				$response["status"] = 404;
				$response["message"] = "User not found.";
			}
		} catch (\Exception $e) {
			$response["jwt"] = null;
			$response["status"] = $e->getCode();
			$response["message"] =  $e->getMessage();
		}

		return $response;
	}

	protected function userExist(SocialProviderUser $user)
	{
    	//TODO: confirm that $user->email record actually exist in the users table
		//return 404 if $user does not exist
    	return true;
    }

	public function logout(){}
}
