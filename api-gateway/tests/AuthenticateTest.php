<?php

use App\Http\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;

class AuthenticateTest extends TestCase
{
    private $jwt;

    /**
     * Setup adds JWT to cache
     *
     * @return void
     */
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

    /**
     * This test checks that the authentication module allows requests
     * without authorization pass through.
     * Before doing so, it breaks the request down and adds them to the request parameters
     *
     * @return void
     */
    public function testRequestWithoutAuthorization(): void
    {
        $request = Request::create('/email/health-check', 'GET');

        $middleware = new Authenticate();

        $middleware->handle($request, function ($req) {
            $this->assertEquals('email', $req->service);
            $this->assertEquals('health-check', $req->path);
        });
    }

    /**
     * This test checks that a request with an invalid JWT is not allowed through the API gateway
     *
     * @return void
     */
    public function testRequestWithInvalidAuthorization(): void
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

    /**
     * this test checks that the authentication middleware allows requests with valid
     * JWT through. It does so by adding the details of the service to the request params
     *
     * @return void
     */
    public function testRequestWithValidAuthorization(): void
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

    /**
     * tearDown removes JWT from cache
     *
     * @return void
     */
    public function tearDown(): void
    {
        Cache::forget($this->jwt);
        parent::tearDown();
    }
}
