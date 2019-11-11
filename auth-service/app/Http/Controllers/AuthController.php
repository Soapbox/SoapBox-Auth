<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Libraries\iJWTLibrary;
use Illuminate\Support\Facades\Cache;
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
			'provider' 		=> 'required|in:'. implode(',', config('support.providers'))
		]);

		$this->token_service->setProvider($request->provider);
		$this->token_service->setCode($request->oauth_code);

		try {
			$token = $this->token_service->generateToken();

			if ($token) {
				Cache::add($token, '', 10);
			}

			return response(
				[
					"token" => $token,
					"message" => "Success."
				], Response::HTTP_OK
			);

		} catch (\Exception $e) {
			log_exception($e);

			return response(
				[
					"token" => null,
					"message" => $e->getMessage()
				], http_code_by_exception_type($e)
			);
		}
	}
}
