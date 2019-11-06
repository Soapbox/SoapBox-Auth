<?php

namespace App\Http\Controllers;

use App\Libraries\iJWTLibrary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Services\TokenGeneratorService;

class AuthController extends Controller
{
	protected $token_service;

	public function __construct(iJWTLibrary $library)
	{
		$this->token_service = new TokenGeneratorService($library);
	}

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 * @throws \Illuminate\Validation\ValidationException
	 */
    public function login(Request $request)
	{
		$this->validate($request, [
			'oauth_code' 	=> 'required|string',
			'provider' 		=> 'required|in:'. implode(',', config('keys.supported_providers'))
		]);

		$this->token_service->setProvider($request->provider);
		$this->token_service->setCode($request->oauth_code);

		try {
			$token = $this->token_service->generateToken();

			if ($token) {
				app('redis')->sAdd(env('REDIS_KEY'), $token);
			}

			return response(
				[
					"token" => $token,
					"message" => "Success."
				], Response::HTTP_OK
			);

		} catch (\Exception $e) {
			Log::info('There was an error.', [
				'error' => $e->getMessage()
			]);

			return response(
				[
					"token" => null,
					"message" => $e->getMessage()
				], $e->getCode()
			);
		}
	}
}
