<?php

namespace App\Http\Controllers;

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
			$user = Socialite::driver($request->provider)->userFromToken($request->oauth_code);
			$statusCode = 200;
		} catch (\Exception $e) {
			$user = null;
			$statusCode = 404;
			$msg = $e->getMessage();
		}


		return response(
			[
				"token" => $user,
				'status' => $user ? 'success' : $msg
			], $statusCode ?? 200
		);





//		try{
//			$user = Socialite::driver('google')->userFromToken($code);
//		}
//		catch (\Exception $ex) {
//			return response($ex->getMessage(), 401);
//		}
//
//		$key = env('JWT_KEY');
//		$exp = strtotime('+1 '.env('JWT_EXP'));
//		$token = array(
//			"iss" => "http://auth-server.test",
//			"aud" => "http://api-gateway.test",
//			"iat" => time(),
//			"exp" => $exp,
//			"name" => $user->name,
//			"email" => $user->email,
//			"avatar" => $user->avatar
//		);
//
//		$jwt = JWT::encode($token, $key, 'HS256');
//
//		app('redis')->sAdd(env('REDIS_KEY'), $jwt);
//
//		return response()->json(compact('token', 'jwt'),201);
	}

	public function logout(){}
}
