<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Services\RoutesMapService;

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
        $routesService = new RoutesMapService();
        $route = $routesService->getRoute($request);

        $this->url = $route->url;
        $this->code = $route->code;
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
