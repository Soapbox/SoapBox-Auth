<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\TokenGeneratorService;

class AuthController extends Controller
{

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 * @throws \Illuminate\Validation\ValidationException
	 */
    public function login(Request $request)
	{
		$this->validate($request, [
			'oauth_code' 	=> 'required|string',
			'provider' 		=> 'required|in:'. 'google, slack, microsoft'
		]);

		$tgs = TokenGeneratorService::generateToken($request);

		if ($tgs::getCode() === Response::HTTP_OK) {
			app('redis')->sAdd(env('REDIS_KEY'), $tgs::getToken());
		}

		return response(
			[
				"token" => $tgs::getToken(),
				"message" => $tgs::getMessage()
			], $tgs::getCode()
		);
	}
}
