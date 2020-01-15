<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Libraries\iJWTLibrary;
use Illuminate\Support\Facades\Cache;
use App\Services\TokenGeneratorService;

class AuthController extends Controller
{
    private $apiClient;
    private $token_service;

    public function __construct(iJWTLibrary $library)
    {
        $this->token_service = new TokenGeneratorService($library);
        $this->apiClient = app()->make('api-client');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'oauth_code' => 'required|string',
            'provider' =>
                'required|in:' . implode(',', config('support.providers'))
        ]);

        try {
            if ($request->has('soapbox-slug')) {
                $response = $this->apiClient->request(
                    'POST',
                    config('env.dev.login_url') .
                        '/' .
                        $request->get('provider'),
                    [
                        'form_params' => [
                            'code' => $request->get('oauth_code'),
                            'soapbox-slug' => $request->get('soapbox-slug'),
                            'redirectUri' => $request->get('redirectUri')
                        ]
                    ]
                );

                $token = $response->getContents();
            } else {
                $token = $this->token_service->generateToken([
                    'provider' => $request->get('provider'),
                    'code' => $request->get('oauth_code')
                ]);
            }

            if ($token) {
                $ttl = Carbon::now()->addDays(91); //3months + 1 day
                Cache::add($token, '', $ttl);
            }

            return response(
                [
                    "token" => $token,
                    "message" => "Success."
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            log_exception($e);

            return response(
                [
                    "token" => null,
                    "message" => $e->getMessage()
                ],
                http_code_by_exception_type($e)
            );
        }
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        if (Cache::has($token)) {
            Cache::forget($token);
            return response(null, Response::HTTP_OK);
        }
    }
}
