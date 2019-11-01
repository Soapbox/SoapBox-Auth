<?php

use App\Http\Middleware\Authenticate;
use Illuminate\Http\Request;

class AuthenticateTest extends TestCase
{
    protected $jwt;
    public function setUp(): void
    {
        parent::setUp();
        $this->jwt =
            "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hdXRoLXNlcnZlci50ZXN0IiwiYXVkIjoiaHR0cDpcL1wvYXBpLWdhdGV3YXkudGVzdCIsImlhdCI6MTU3MjM0NDUxMywiZXhwIjoxNTcyOTQ5MzEzLCJuYW1lIjoiQ2FsZWIgTWJha3dlIiwiZW1haWwiOiJjYWxlYkBzb2FwYm94aHEuY29tIiwiYXZhdGFyIjoiaHR0cHM6XC9cL2xoNS5nb29nbGV1c2VyY29udGVudC5jb21cLy13Z3dXWF9LNkZWQVwvQUFBQUFBQUFBQUlcL0FBQUFBQUFBQUFBXC9BQ0hpM3JlYWM2cVRuX0pTak9RQU9WelBRXzZOV3VTWmRnXC9waG90by5qcGcifQ.fUsixNLW87PbTecfTt46TjEVgv1gT4byCkHbfizuFZ8";
        app('redis')->sAdd(env('REDIS_KEY'), $this->jwt);
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
        app('redis')->sRem(env('REDIS_KEY'), $this->jwt);
        parent::tearDown();
    }
}
