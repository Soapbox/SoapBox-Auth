<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class RouteController extends Controller
{
    protected $url;
    protected $code;
    protected $client;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request, Client $client = null)
    {
        $this->client = $client ? $client : new Client;
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

    //
    public function get(Request $request)
    {   
        if(!isset($this->url))
            return response(null, $this->code);

        return $this->handler($request, 'query');
    }

    //
    public function post(Request $request)
    {
        if(!isset($this->url))
            return response(null, $this->code);
        
        return $this->handler($request, 'json');
    }

    //
    public function put(Request $request)
    {
        if(!isset($this->url))
            return response(null, $this->code);

        return $this->handler($request, 'json');
    }

    //
    public function delete(Request $request)
    {
        if(!isset($this->url))
            return response(null, $this->code);

        return $this->handler($request, 'json');
    }

    /**
     * this method forwards the requests to the appropriate service
     */
    public function handler(Request $request, $option)
    {
        $options = [];

        if($request->headers->has('Authorization'))
            $options['headers'] = [
                'Authorization' => $request->header('Authorization')
            ];
        
        // forward parameters
        $options[$option] = $request->all();

        // disable ssl validation
        $options['verify'] = false;

        // make request
        try {
            $response = $this->client->request($request->method(), $this->url, $options);

            return response()->json($response, 200);
        }
        catch (\Exception $e) {
            if ($e->hasResponse()) 
                return response($e->getResponse()->getReasonPhrase(), 
                    $e->getResponse()->getStatusCode());
            else
                return response($e->getMessage(), 500);
        }
    }
}
