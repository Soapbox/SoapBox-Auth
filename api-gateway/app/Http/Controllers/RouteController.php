<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RouteController extends Controller
{
    var $url;
    var $code;
    var $client;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->client = new \GuzzleHttp\Client(['verify' => false]);
        // fetch routes map
        $routes = json_decode(stripslashes(file_get_contents(__DIR__ . "/../../../routes.map.json")), true);
        
        $key = array_search($request->input('service'), array_column($routes, 'service'));
        $route = $routes[$key];

        $this->url = null;

        // fetch rule
        if(isset($route["endpoints"][$request->method()][$request->input("path")])) {
            $rule = $route["endpoints"][$request->method()][$request->input("path")];

            // check if auth is required (auth would have already been done in middleware)
            if($rule["auth"]){
                if(!$request->headers->has('Authorization')) {
                    $this->code = 401;
                    return;
                }
            }
            else {
                // remove jwt payload from request if the underlying request doesn't require authentication
                unset($request['payload']);
            }

            // clean up the service and path from the request 
            // before it gets forwarded to underlying service
            unset($request['service']);
            unset($request['path']);
            
            $this->url = 'http://' . $route["base-url"] . '/' . $rule["url"];
        }
        else{
            $this->code = 404;
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function get(Request $request)
    {   
        if(!isset($this->url))
            return response(null, $this->code);

        $options = [];

        if($request->headers->has('Authorization'))
            $options['headers'] = [
                'Authorization' => $request->header('Authorization')
            ];
        
        // forward parameters
        $options['query'] = $request->all();

        // make request
        try {
            $response = $this->client->request('GET', $this->url, $options);

            return response()->json($response, 200);
        }
        catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }

    //
    public function post(Request $request)
    {
        if(!isset($this->url))
            return response(null, $this->code);

        $options = [];

        if($request->headers->has('Authorization'))
            $options['headers'] = [
                'Authorization' => $request->header('Authorization')
            ];
        
        // forward parameters
        $options['form_params'] = $request->all();

        // make request
        try {
            $response = $this->client->request('POST', $this->url, $options);

            return response()->json($response, 200);
        }
        catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }
}
