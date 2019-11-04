<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\TokenGeneratorService;

class AuthController extends Controller
{
	protected $supported_providers = ['google', 'slack', 'microsoft'];

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 * @throws \Illuminate\Validation\ValidationException
	 */
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
				], Response::HTTP_UNPROCESSABLE_ENTITY
			);
		}

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
