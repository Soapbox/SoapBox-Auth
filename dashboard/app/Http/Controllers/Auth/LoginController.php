<?php

namespace App\Http\Controllers\Auth;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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

    /**
     * This method logs the user out by removing the jwt from the session and redirecting to the home page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logout(): SymfonyResponse
    {
        session()->forget('jwt');

        return redirect('/');
    }

    /**
     * This method calls the auth server for login with the oauth_code and redirect uri
     *
     * @param \Illuminate\Http\Request $request
     * @param \GuzzleHttp\Client $client
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(Request $request, Client $client): SymfonyResponse
    {
        // validations. To be migrated...
        $status = $request->validate([
            "oauth_code" => "required",
            "provider" => "required",
            "redirectUri" => "required|url",
            "soapbox-slug" => "required"
        ]);

        $client = $client ? $client : new Client();

        try {
            $params = $request->all();

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
                return $this->handleErrorRedirect(
                    "A problem happened during login"
                );
            }
        } catch (ClientException $e) {
            return $this->handleErrorRedirect($e->getMessage());
        }
    }

    /**
     * this method handles exceptions received when request forwarding is attempted
     *
     * @param \Exception  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function handleErrorRedirect($message): SymfonyResponse
    {
        return redirect('/')->with('message', $message);
    }
}
