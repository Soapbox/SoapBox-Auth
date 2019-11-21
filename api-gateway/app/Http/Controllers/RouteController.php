<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Services\RoutesMapService;
use Symfony\Component\HttpFoundation\Response;

class RouteController extends Controller
{
    /**
     * The final URL of the underlyng service referenced in this request
     * @var String
     */
    protected $url;
    /**
     * The return code after evaluation of the request
     * @var integer
     */
    protected $code;

    /**
     * An instance of the routes service
     *
     * @var App\Services\RoutesMapService
     */
    protected $routesService;

    /**
     * Create a new controller instance,
     * initialise guzzle client if needed,
     * get route definition from routemap
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GuzzleHttp\Client $client
     * @return void
     */
    public function __construct(Request $request, Client $client = null)
    {
        $client = $client ? $client : new Client();
        // fetch routes map
        $this->routesService = new RoutesMapService($client);
        $route = $this->routesService->getRoute($request);

        $this->url = $route->url;
        $this->code = $route->code;
    }

    /**
     * Controller method for all get requests
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(Request $request): Response
    {
        if (!isset($this->url)) {
            return response(null, $this->code);
        }

        return $this->routesService->handler($request, 'query', $this->url);
    }

    /**
     * Controller method for all post requests
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function post(Request $request): Response
    {
        if (!isset($this->url)) {
            return response(null, $this->code);
        }

        return $this->routesService->handler($request, 'json', $this->url);
    }

    /**
     * Controller method for all put requests
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function put(Request $request): Response
    {
        if (!isset($this->url)) {
            return response(null, $this->code);
        }

        return $this->routesService->handler($request, 'json', $this->url);
    }

    /**
     * Controller method for all delete requests
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request): Response
    {
        if (!isset($this->url)) {
            return response(null, $this->code);
        }

        return $this->routesService->handler($request, 'json', $this->url);
    }
}
