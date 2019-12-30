<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Libraries\iJWTLibrary;
use Illuminate\Support\Facades\Cache;
use App\Services\TokenGeneratorService;

class AuthController extends Controller
{
    protected $client;
    protected $token_service;

	public function __construct(iJWTLibrary $library, Client $client)
	{
		$this->token_service = new TokenGeneratorService($library);
		$this->client = $client;
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

		try {
			$token = $this->token_service->generateToken([
				'provider' => $request->provider,
				'code' => $request->oauth_code
			]);

            if ($request->has('soapbox-slug')) {
                //http://api.soapboxdev.com/auth/google
                //http://api.soapboxdev.com/auth/slack

                $token = $this->client->request(
                    'POST',
                    'http://api.soapboxdev.com/auth/' . $request->provider,
                    [
                        'form_params' => [
                            'code' => $request->oauth_code,
                            'soapbox-slug' => $request->get('soapbox-slug')
                        ]
                    ]
                );
            }

			if ($token) {
				$ttl = Carbon::now()->addDays(91); //3months + 1 day
				Cache::add($token, '', $ttl);
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

	public function logout(Request $request)
	{
		$token = $request->bearerToken();

		if (Cache::has($token)){
			Cache::forget($token);
			return response(null, Response::HTTP_OK);
		}
	}
}
