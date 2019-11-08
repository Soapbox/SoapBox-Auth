<?php

use App\Http\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;

class AuthenticateTest extends TestCase
{
    private $jwt;
    public function setUp(): void
    {
        parent::setUp();
        $key = env('JWT_KEY');
        $exp = strtotime('+1 week');

        $token = array(
            "iss" => "http://auth-server.test",
            "aud" => "http://api-gateway.test",
            "iat" => time(),
            "exp" => $exp
        );

        $this->jwt = JWT::encode($token, $key, 'HS256');

        Cache::add($this->jwt, '', 10);
    }

    /** @test */
    public function request_without_authorization()
    {
        $request = Request::create('/email/health-check', 'GET');

        $middleware = new Authenticate();

        $middleware->handle($request, function ($req) {
            $this->assertEquals('email', $req->service);
            $this->assertEquals('health-check', $req->path);
        });
    }

    public function request_with_invalid_route()
    {
        $request = Request::create('/email/not-found', 'GET');

        $middleware = new Authenticate();

        $response = $middleware->handle($request, function () {});

        $this->assertEquals(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );
    }

    public function request_with_invalid_authorization()
    {
        $request = Request::create('/email/send-body', 'GET');

        $request->merge([
            'subject' => 'Title is in mixed CASE',
            'body' => 'Lorem ipsum dolor amat'
        ]);

        $jwt =
            "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hdXRoLXNlcnZlci50ZXN0IiwiYXVkIjoiaHR0cDpcL1wvYXBpLWdhdGV3YXkudGVzdCIsImlhdCI6MTU3MjM0NDUxMywiZXhwIjoxNTcyOTQ5MzEzLCJuYW1lIjoiQ2FsZWIgTWJha3dlIiwiZW1haWwiOiJjYWxlYkBzb2FwYm94aHEuY29tIiwiYXZhdGFyIjoiaHR0cHM6XC9cL2xoNS5nb29nbGV1c2VyY29udGVudC5jb21cLy13Z3dXWF9LNkZWQVwvQUFBQUFBQUFBQUlcL0FBQUFBQUFBQUFBXC9BQ0hpM3JlYWM2cVRuX0pTak9RQU9WelBRXzZOV3VTWmRnXC9waG90by5qcGcifQ.fUsixNLW87PbTecfTt46TjEVgv1gT4byCkHbfizuFZ9";

        $request->headers->set('Authorization', 'Bearer ' . $jwt);

        $middleware = new Authenticate();

        $response = $middleware->handle($request, function () {});

        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode()
        );
    }

    public function request_with_valid_authorization()
    {
        $request = Request::create('/email/send-body', 'GET');

        $request->merge([
            'subject' => 'Title is in mixed CASE',
            'body' => 'Lorem ipsum dolor amat'
        ]);

        $request->headers->set('Authorization', 'Bearer ' . $this->jwt);

        $middleware = new Authenticate();

        $middleware->handle($request, function ($req) {
            $this->assertArrayHasKey('payload', $req->all());
        });
    }

    public function tearDown(): void
    {
        Cache::forget($this->jwt);
        parent::tearDown();
    }
}
