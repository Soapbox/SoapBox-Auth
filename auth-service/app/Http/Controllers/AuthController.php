<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
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

		try {
			$jwt = $this->generateJWTToken($request);
			$statusCode = 200;
			app('redis')->sAdd(env('REDIS_KEY'), $jwt);
		} catch (\Exception $e) {
			$jwt = null;
			$statusCode = $e->getCode();
			$msg = $e->getMessage();
		}

		return response(
			[
				"token" => $jwt,
				'status' => $jwt ? 'success' : $msg
			], $statusCode ?? 200
		);
	}

	protected function generateJWTToken(Request $request)
	{
		$user = Socialite::driver($request->provider)->stateless()->userFromToken($request->oauth_code);

		$key = env('JWT_KEY');
		$exp = strtotime('+1 '. env('JWT_EXP'));
		$token = array(
			"iss" => "http://auth-server.test",
			"aud" => "http://api-gateway.test",
			"iat" => time(),
			"exp" => $exp,
			"name" => $user->name,
			"email" => $user->email
		);

		return JWT::encode($token, $key, 'HS256');
	}

	public function logout(){}
}
