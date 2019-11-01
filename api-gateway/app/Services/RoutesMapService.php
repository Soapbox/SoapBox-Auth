<?php

namespace App\Services;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use GuzzleHttp\Exception\ClientException;

/**
 * This service handles the heavy lifting
 */
class RoutesMapService
{
    /**
     * The routes from the routesmapfile
     * @var array
     */
    protected $routes;

    /**
     * An instance of guzzle http client
     *
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * Create a new service instance
     * Set guzzle client instance
     * get route definition from routemap
     *
     * @param  \GuzzleHttp\Client $client
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->routes = json_decode(
            stripslashes(Storage::get('routes.map.json')),
            true
        );
    }

    /**
     * Helper method to resolve final route from a given request
     *
     * @param \Illuminate\Http\Request  $request
     * @return Object
     */
    public function getRoute(Request $request)
    {
        $key = array_search(
            $request->input('service'),
            array_column($this->routes, 'service')
        );
        $route = $this->routes[$key];

        $url = null;
        $code = null;

        if (
            isset(
                $route["endpoints"][$request->method()][$request->input("path")]
            )
        ) {
            $rule =
                $route["endpoints"][$request->method()][
                    $request->input("path")
                ];

            // check if auth is required (auth would have already been done in middleware)
            if ($rule["auth"]) {
                if (!$request->headers->has('Authorization')) {
                    $code = 401;
                    return (object) ["url" => $url, "code" => $code];
                }
            } else {
                // remove jwt payload from request if the underlying request doesn't require authentication
                unset($request['payload']);
            }

            // clean up the service and path from the request
            // before it gets forwarded to underlying service
            unset($request['service']);
            unset($request['path']);

            $url =
                $route["protocol"] .
                '://' .
                $route["base-url"] .
                '/' .
                $rule["url"];
        } else {
            $code = 404;
        }

        return (object) ["url" => $url, "code" => $code];
    }

    /**
     * this method forwards the requests to the appropriate service
     *
     * @param \Illuminate\Http\Request  $request
     * @param string $option
     * @param string $url
     * @return Illuminate\Http\Response
     */
    public function handler(Request $request, $option, $url)
    {
        $options = [];

        if ($request->headers->has('Authorization')) {
            $options['headers'] = [
                'Authorization' => $request->header('Authorization')
            ];
        }

        // forward parameters
        $options[$option] = $request->all();

        // disable ssl validation
        $options['verify'] = false;

        // make request
        try {
            $response = $this->client->request(
                $request->method(),
                $url,
                $options
            );

            return response()->json($response, Response::HTTP_OK);
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                return response(
                    $e->getResponse()->getReasonPhrase(),
                    $e->getResponse()->getStatusCode()
                );
            } else {
                return response($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
        }
    }
}
