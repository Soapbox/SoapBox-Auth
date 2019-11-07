<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
	public function logout(Request $request)
	{
		$this->validate($request, [
			'jwt' => 'required'
		]);

		if (app('redis')->sIsMember(env('REDIS_KEY'), $request->jwt)){
			app('redis')->sRem(env('REDIS_KEY'), $request->jwt);
			$code = Response::HTTP_OK;
		} else {
			$code = Response::HTTP_UNAUTHORIZED;
		}

		return response(null, $code);
	}
}
