<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use \Firebase\JWT\JWT;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

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
        if($request->headers->has('Authorization')) {
            // validate jwt
            $jwt = explode(" ", $request->header('Authorization'))[1];

            if(!app('redis')->sIsMember(env('REDIS_KEY'), $jwt)){
                return response('Unauthorized.', 401);
            }
            else{
                // decode JWT and add to request
                $decoded = JWT::decode($jwt, env('JWT_KEY'), array('HS256'));

                $request->merge([
                    "payload" => $decoded
                ]);
            }
        }

        // add details to request for controller to work it's magic
        $path = explode("/", $request->path());

        $request->merge([
            "service" =>$path[0],
            "path" =>$path[1]
        ]);

        return $next($request);
    }
}
