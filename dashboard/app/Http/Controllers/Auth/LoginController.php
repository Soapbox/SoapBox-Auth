<?php

namespace App\Http\Controllers\Auth;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function login(Request $request, Client $client)
    {
        $client = $client ? $client : new Client();

        $params = $request->all();

        try {
            $response = $client->request(
                'POST',
                env('API_URL') . '/auth/login',
                [
                    'form_params' => $params
                ]
            );
            $contents = json_decode($response->getBody()->getContents());

            if (isset($contents->token)) {
                $token = $contents->token;
                session(["jwt" => $token]);
                return redirect('app');
            } else {
                return redirect('/')->with('message', "A problem happened during login");
            }
        } catch (ClientException $e) {
            return $this->handleException($e);
        } catch (ConnectException $e) {
            return $this->handleException($e);
        }
    }
}
