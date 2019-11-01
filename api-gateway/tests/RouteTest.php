<?php

use App\Http\Controllers\RouteController;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class RouteTest extends TestCase
{
    protected $jwt;
    public function setUp(): void
    {
        parent::setUp();
        $this->jwt =
            "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hdXRoLXNlcnZlci50ZXN0IiwiYXVkIjoiaHR0cDpcL1wvYXBpLWdhdGV3YXkudGVzdCIsImlhdCI6MTU3MjM0NDUxMywiZXhwIjoxNTcyOTQ5MzEzLCJuYW1lIjoiQ2FsZWIgTWJha3dlIiwiZW1haWwiOiJjYWxlYkBzb2FwYm94aHEuY29tIiwiYXZhdGFyIjoiaHR0cHM6XC9cL2xoNS5nb29nbGV1c2VyY29udGVudC5jb21cLy13Z3dXWF9LNkZWQVwvQUFBQUFBQUFBQUlcL0FBQUFBQUFBQUFBXC9BQ0hpM3JlYWM2cVRuX0pTak9RQU9WelBRXzZOV3VTWmRnXC9waG90by5qcGcifQ.fUsixNLW87PbTecfTt46TjEVgv1gT4byCkHbfizuFZ8";
        app('redis')->sAdd(env('REDIS_KEY'), $this->jwt);
    }

    public function prepareValidResponse()
    {
        $response = new Response(200);
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('request')
            ->once()
            ->andReturn($response);
        $this->app->instance(Client::class, $client);
    }

    public function testValidGet()
    {
        $this->prepareValidResponse();

        $this->get('/email/health-check');

        $this->assertEquals(200, $this->response->getStatusCode());

        $this->app->instance(Client::class, null);
    }

    public function testInvalidGet()
    {
        $this->get('/service/not-found');

        $this->assertEquals(404, $this->response->getStatusCode());
    }

    public function testValidPost()
    {
        $this->prepareValidResponse();

        $this->json(
            'POST',
            '/email/send-email',
            ['subject' => 'Sally', 'body' => 'Ommlette du fromage'],
            ['Authorization' => 'Bearer ' . $this->jwt]
        );
        $this->assertEquals(200, $this->response->status());
    }

    public function testUnauthorizedPost()
    {
        $response = $this->json('POST', '/email/send-email', [
            'subject' => 'Sally',
            'body' => 'Ommlette du fromage'
        ]);
        $this->assertEquals(401, $this->response->status());
    }

    public function testValidPut()
    {
        $this->prepareValidResponse();

        $response = $this->json(
            'PUT',
            '/email/address',
            ['user_id' => 1, 'email' => 'ommlette.du@fromage.com'],
            ['Authorization' => 'Bearer ' . $this->jwt]
        );
        $this->assertEquals(200, $this->response->status());
    }

    public function testValidDelete()
    {
        $this->prepareValidResponse();

        $response = $this->json(
            'DELETE',
            '/email/records',
            ['user_id' => 1],
            ['Authorization' => 'Bearer ' . $this->jwt]
        );
        $this->assertEquals(200, $this->response->status());
    }

    public function tearDown(): void
    {
        app('redis')->sRem(env('REDIS_KEY'), $this->jwt);
        parent::tearDown();
    }
}
