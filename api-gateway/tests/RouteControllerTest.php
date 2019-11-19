<?php

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\RouteController;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class RouteControllerTest extends TestCase
{
    /**
     * An instance of guzzle http client
     *
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * The jwt to be used in requests
     * @var string
     */
    private $jwt;

    /**
     * Setup initialises controller to be used in tests
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->jwt =
            "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hdXRoLXNlcnZlci50ZXN0IiwiYXVkIjoiaHR0cDpcL1wvYXBpLWdhdGV3YXkudGVzdCIsImlhdCI6MTU3MjM0NDUxMywiZXhwIjoxNTcyOTQ5MzEzLCJuYW1lIjoiQ2FsZWIgTWJha3dlIiwiZW1haWwiOiJjYWxlYkBzb2FwYm94aHEuY29tIiwiYXZhdGFyIjoiaHR0cHM6XC9cL2xoNS5nb29nbGV1c2VyY29udGVudC5jb21cLy13Z3dXWF9LNkZWQVwvQUFBQUFBQUFBQUlcL0FBQUFBQUFBQUFBXC9BQ0hpM3JlYWM2cVRuX0pTak9RQU9WelBRXzZOV3VTWmRnXC9waG90by5qcGcifQ.fUsixNLW87PbTecfTt46TjEVgv1gT4byCkHbfizuFZ9";
        $response = new GuzzleResponse(Response::HTTP_OK);
        $this->client = Mockery::mock(Client::class);
        $this->client->shouldReceive('request')->andReturn($response);
    }

    /**
     * A test for a valid get.
     * By Valid, means we have this endpoint mapped in our routes json file
     *
     * @return void
     */
    public function testValidGet(): void
    {
        $request = Request::create('/test/health-check', 'GET');
        $request->merge([
            'service' => 'test',
            'path' => 'health-check'
        ]);

        $controller = new RouteController($request, $this->client);
        $response = $controller->get($request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * A test for an invalid get request.
     * By invalid, it means we don't have the route in our json
     *
     * @return void
     */
    public function testInvalidGet(): void
    {
        $request = Request::create('/invalid/invalid-get', 'GET');
        $request->merge([
            'service' => 'invalid',
            'path' => 'invalid-get'
        ]);

        $controller = new RouteController($request, $this->client);
        $response = $controller->get($request);
        $this->assertEquals(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode()
        );
    }

    /**
     * A test for a valid post.
     * By Valid, means we have this endpoint mapped in our routes json file
     * And if the request requires authorization, like in this case, then we send in a JWT
     *
     * @return void
     */
    public function testValidPost(): void
    {
        $request = Request::create('/test/send-email', 'POST');
        $request->merge([
            'service' => 'test',
            'path' => 'send-email'
        ]);

        $request->headers->set('Authorization', 'Bearer ' . $this->jwt);

        $controller = new RouteController($request, $this->client);
        $response = $controller->post($request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * A test for an unauthorized post request.
     * By unauthorized, it means we don't send an authorization header
     *
     * @return void
     */
    public function testUnauthorizedPost(): void
    {
        $request = Request::create('/test/send-email', 'POST');
        $request->merge([
            'service' => 'test',
            'path' => 'send-email'
        ]);

        $controller = new RouteController($request, $this->client);
        $response = $controller->post($request);
        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode()
        );
    }

    /**
     * A test for a valid put.
     * By Valid, means we have this endpoint mapped in our routes json file
     * And if the request requires authorization, like in this case, then we send in a JWT
     *
     * @return void
     */
    public function testValidPut(): void
    {
        $request = Request::create('/test/address', 'PUT');
        $request->merge([
            'service' => 'test',
            'path' => 'address'
        ]);
        $request->headers->set('Authorization', 'Bearer ' . $this->jwt);

        $controller = new RouteController($request, $this->client);
        $response = $controller->put($request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * A test for an unauthorized put.
     * The request requires authorization, in this case, and we do not send in a JWT
     *
     * @return void
     */
    public function testUnauthorizedPut(): void
    {
        $request = Request::create('/test/address', 'PUT');
        $request->merge([
            'service' => 'test',
            'path' => 'address'
        ]);

        $controller = new RouteController($request, $this->client);
        $response = $controller->put($request);
        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode()
        );
    }

    /**
     * A test for a valid deletion.
     * By Valid, means we have this endpoint mapped in our routes json file
     * And if the request requires authorization, like in this case, then we send in a JWT
     *
     * @return void
     */
    public function testValidDelete(): void
    {
        $request = Request::create('/test/records', 'DELETE');
        $request->merge([
            'service' => 'test',
            'path' => 'records'
        ]);
        $request->headers->set('Authorization', 'Bearer ' . $this->jwt);

        $controller = new RouteController($request, $this->client);
        $response = $controller->delete($request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * A test for an unauthorized deletion.
     * The request requires authorization, in this case, and we don't send in a JWT
     *
     * @return void
     */
    public function testUnauthorizedDelete(): void
    {
        $request = Request::create('/test/records', 'DELETE');
        $request->merge([
            'service' => 'test',
            'path' => 'records'
        ]);

        $controller = new RouteController($request, $this->client);
        $response = $controller->delete($request);
        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode()
        );
    }
}
