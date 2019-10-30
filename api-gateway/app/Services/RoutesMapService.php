<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * This service handles the heavy lifting
 */
class RoutesMapService {
    protected $routes;

    public function __construct()
    {
        $this->routes = json_decode(stripslashes(file_get_contents(__DIR__ . "/../../routes.map.json")), true);
    }

    public function getRoute(Request $request)
    {
        $key = array_search($request->input('service'), array_column($this->routes, 'service'));
        $route = $this->routes[$key];

        $url = null;
        $code = null;

        // fetch rule
        if(isset($route["endpoints"][$request->method()][$request->input("path")])) {
            $rule = $route["endpoints"][$request->method()][$request->input("path")];

            // check if auth is required (auth would have already been done in middleware)
            if($rule["auth"]){
                if(!$request->headers->has('Authorization')) {
                    $code = 401;
                    return (object) ["url" => $url, "code" => $code];
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
            
            $url = $route["protocol"] . '://' . $route["base-url"] . '/' . $rule["url"];
        }
        else{
            $code = 404;
        }

        return (object) ["url" => $url, "code" => $code];
    }

}
