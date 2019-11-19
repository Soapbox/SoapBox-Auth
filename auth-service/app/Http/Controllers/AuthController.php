<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
	public function logout(Request $request)
	{
		$token = $request->bearerToken();
		$code = Response::HTTP_UNAUTHORIZED;

		if (Cache::has($token)){
			Cache::forget($token);
			$code = Response::HTTP_OK;
		}

		return response(null, $code);
	}
}
