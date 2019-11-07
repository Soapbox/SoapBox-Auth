<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Illuminate\Http\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($request->headers->has('Authorization')) {
            // validate jwt
            $jwt = explode(" ", $request->header('Authorization'))[1];

            if (!app('redis')->sIsMember(env('REDIS_KEY'), $jwt)) {
                return response('Unauthorized.', Response::HTTP_UNAUTHORIZED);
            } else {
                // decode JWT and add to request
                $decoded = JWT::decode($jwt, env('JWT_KEY'), array('HS256'));

                $request->merge([
                    "payload" => $decoded
                ]);
            }
        }

        // add details to request for controller to work it's magic
        $path = explode("/", $request->path());

        if (isset($path[0]) && isset($path[1])) {
            $request->merge([
                "service" => $path[0],
                "path" => $path[1]
            ]);
        } else {
            // happens when the url sent is not in the form 'service/endpoint{anything can follow}'
            return response(null, Response::HTTP_NOT_FOUND);
        }

        return $next($request);
    }
}
