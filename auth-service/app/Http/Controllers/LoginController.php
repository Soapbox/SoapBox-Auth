<?php

namespace App\Http\Controllers;

use Socialite;
use Illuminate\Http\Request;
use \Firebase\JWT\JWT;

class LoginController extends Controller
{
    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $code = $request->input('oauth_code');
        try{
            $user = Socialite::driver('google')->userFromToken($code);
        }
        catch (\Exception $ex) {
            return response($ex->getMessage(), 401);
        }

        $key = env('JWT_KEY');
        $exp = strtotime('+1 '.env('JWT_EXP'));
        $token = array(
            "iss" => "http://auth-server.test",
            "aud" => "http://api-gateway.test",
            "iat" => time(),
            "exp" => $exp,
            "name" => $user->name,
            "email" => $user->email,
            "avatar" => $user->avatar
        );

        $jwt = JWT::encode($token, $key, 'HS256');

        app('redis')->sAdd(env('REDIS_KEY'), $jwt);

        return response()->json(compact('token', 'jwt'),201);
    }

    public function logout(Request $request) {
        $jwt = $request->input('jwt');
        $code = 401;

        if(app('redis')->sIsMember(env('REDIS_KEY'), $jwt)){
            app('redis')->sRem(env('REDIS_KEY'), $jwt);
            $code = 200;
        }
        
        return response(null, $code);
    }
}
