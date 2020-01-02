<?php

namespace App\Http\Controllers\Auth;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SlackController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Slack Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application though slack.
    | In particular, it exchanges the code for the access_token using slack's API
    |
    */

    /**
     * This method makes the oauth call to slack exchanging the code for an access token
     * 
     * @param \Illuminate\Http\Request $request
     * @param \GuzzleHttp\Client $client
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(Request $request, Client $client): SymfonyResponse
    {
        $client = $client ? $client : new Client();

        $query = [
            "scope" => env("SLACK_SCOPE"),
            "redirect_uri" => env("SLACK_REDIRECT_URL"),
            "client_secret" => env("SLACK_CLIENT_SECRET"),
            "client_id" => env("SLACK_CLIENT_ID"),
            "state" => (time() * 1000),
            "code" => $request->code
        ];

        $response = $client->request(
            'GET',
            "https://slack.com/api/oauth.access",
            [
                "query" => $query
            ]
        );

        $response = $this->getResponseBody($response);

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * This method returns a jsondecoded representation of the response if it is valid json
     * otherwise it returns a raw string
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return mixed
     */
    private function getResponseBody($response)
    {
        $response = (string) $response->getBody();

        return json_decode($response) === null
            ? $response
            : json_decode($response, true);
    }
}
