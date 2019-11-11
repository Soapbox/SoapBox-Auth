<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
	public function logout(Request $request)
	{
		$this->validate($request, [
			'jwt' => 'required'
		]);

		if (Cache::has($request->jwt)){
			Cache::forget($request->jwt);
			$code = Response::HTTP_OK;
		} else {
			$code = Response::HTTP_UNAUTHORIZED;
		}

		return response(null, $code);
	}
}
