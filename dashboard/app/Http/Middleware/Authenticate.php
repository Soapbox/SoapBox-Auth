<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class Authenticate
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $jwt = session("jwt");

        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, env('JWT_SECRET'), ['HS256']);
                if ($request->path() == '/') {
                    return redirect('app');
                }
                return $next($request);
            } catch (ExpiredException $e) {
                return $this->redirectTo($request, $next);
            }
        } else {
            return $this->redirectTo($request, $next);
        }
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected function redirectTo($request, $next)
    {
        if (!$request->expectsJson()) {
            if ($request->path() == '/') {
                return $next($request);
            } else {
                return redirect('/');
            }
        } else {
            return response()->json("Welcome");
        }
    }
}
