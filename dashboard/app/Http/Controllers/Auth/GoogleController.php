<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\OAuth2\Client\Provider\Google;

class GoogleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Google Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application though Google.
    | In particular, it gets the code that is then sent to the API for validation
    |
    */

    /**
     * This method makes the oauth call to Google to get the authorization code
     * 
     * @param \Illuminate\Http\Request $request
     * 
     */
    public function login(Request $request)
    {
        $provider = new Google([
            'clientId'     => env("GOOGLE_KEY"),
            'clientSecret' => env("GOOGLE_SECRET"),
            'redirectUri'  => env("GOOGLE_REDIRECT_URI"),
            'scopes'       => explode(env("GOOGLE_SCOPE"), " "),
            'access_type'  => "offline",
            'hostedDomain' => 'soapboxhq.com',
        ]);

        if ($request->error) {
            return redirect('/?error=' . $request->error);
        } elseif (!$request->code) {
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl([
                'state' => time() * 1000
            ]);
            session([
                'oauth2state' => $provider->getState()
            ]);
            return redirect($authUrl);
        }
    }
}
