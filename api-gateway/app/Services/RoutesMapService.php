<?php

namespace App\Services;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
     * @return \stdClass
     */
    public function getRoute(Request $request): \stdClass
    {
        $key = array_search(
            $request->input('service'),
            array_column($this->routes, 'service')
        );

        $url = null;
        $code = null;
        $route = null;

        if ($key !== false) {
            $route = $this->routes[$key];
        }

        if (
            isset($route) &&
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
                    return (object) [
                        "url" => $url,
                        "code" => Response::HTTP_UNAUTHORIZED
                    ];
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
            $code = Response::HTTP_NOT_FOUND;
        }

        return (object) ["url" => $url, "code" => $code];
    }

    /**
     * this method forwards the requests to the appropriate service
     *
     * @param \Illuminate\Http\Request  $request
     * @param string $option
     * @param string $url
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handler(Request $request, $option, $url): SymfonyResponse
    {
        $options = [];

        // forward parameters
        $options[$option] = $request->all();

        // forward headers an unset host (it's different this time)
        $options['headers'] = $request->headers->all();
        unset($options['headers']['host']);
        unset($options['headers']['content-type']);
        unset($options['headers']['content-length']);

        // disable ssl validation
        $options['verify'] = false;

        // make request
        try {
            $response = $this->client->request(
                $request->method(),
                $url,
                $options
            );

            $response = $this->getResponseBody($response);

            return response()->json($response, Response::HTTP_OK);
        } catch (ClientException $e) {
            return $this->handleException($e);
        } catch (ConnectException $e) {
            return $this->handleException($e);
        }
    }

    /**
     * This method returns a jsondecoded representation of the response if it is valid json
     * otherwise it returns a raw string
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return mixed
     */
    public function getResponseBody($response)
    {
        $response = (string) $response->getBody();

        return json_decode($response) === null
            ? $response
            : json_decode($response, true);
    }

    /**
     * this method handles exceptions received when request forwarding is attempted
     *
     * @param \Exception  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function handleException($e): SymfonyResponse
    {
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
