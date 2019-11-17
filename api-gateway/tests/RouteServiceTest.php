<?php

use GuzzleHttp\Client;
use App\Services\RoutesMapService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class RouteServiceTest extends TestCase
{
    /**
     * An instance of the routesMapService
     *
     * @var App\Services\RoutesMapService
     */
    private $routesService;

    /**
     * A request instance to be reused in tests
     *
     * @var Illuminate\Http\Request
     */
    private $request;

    /**
     * Setup initialises service to be used in tests
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $response = new GuzzleResponse(Response::HTTP_OK);
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')->andReturn($response);

        $this->request = Request::create('/test/health-check', 'GET');
        $this->request->merge([
            'service' => 'test',
            'path' => 'health-check'
        ]);
        $this->request->headers->set('Cache-Control', 'no-cache');

        $this->routesService = new RoutesMapService($client);
    }

    /**
     * A test for fetching the route object from a particular request
     * given the service and the endpoint as specified by the URL
     * eg. [base-url]/service/endpoint
     *
     * @return void
     */
    public function testValidRoute(): void
    {
        $route = $this->routesService->getRoute($this->request);

        $this->assertEquals(
            'http://test.soapboxhqtestservice.com/health-check',
            $route->url
        );
        $this->assertNull($route->code);
    }

    /**
     * This test checks that the service gracefully handles routes for services
     *  that do not exist [base-url]/service/endpoint
     *
     * @return void
     */
    public function testInvalidService(): void
    {
        $request = Request::create('/invalid/invalid-get', 'GET');
        $request->merge([
            'service' => 'invalid',
            'path' => 'invalid-get'
        ]);

        $route = $this->routesService->getRoute($request);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $route->code);
        $this->assertNull($route->url);
    }

    /**
     * This test checks that the service gracefully handles routes for service endpoints
     *  that do not exist [base-url]/service/endpoint
     *
     * @return void
     */
    public function testInvalidEndpoint(): void
    {
        $request = Request::create('/test/invalid-get', 'GET');
        $request->merge([
            'service' => 'test',
            'path' => 'invalid-get'
        ]);

        $route = $this->routesService->getRoute($request);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $route->code);
        $this->assertNull($route->url);
    }

    /**
     * This test checks that the service forwards requests and headers to the underlying service
     * eg. [base-url]/service/endpoint
     *
     * @return void
     */
    public function testHandlerRouteForwardingWithHeaders(): void
    {
        $jwt =
            "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hdXRoLXNlcnZlci50ZXN0IiwiYXVkIjoiaHR0cDpcL1wvYXBpLWdhdGV3YXkudGVzdCIsImlhdCI6MTU3MjM0NDUxMywiZXhwIjoxNTcyOTQ5MzEzLCJuYW1lIjoiQ2FsZWIgTWJha3dlIiwiZW1haWwiOiJjYWxlYkBzb2FwYm94aHEuY29tIiwiYXZhdGFyIjoiaHR0cHM6XC9cL2xoNS5nb29nbGV1c2VyY29udGVudC5jb21cLy13Z3dXWF9LNkZWQVwvQUFBQUFBQUFBQUlcL0FBQUFBQUFBQUFBXC9BQ0hpM3JlYWM2cVRuX0pTak9RQU9WelBRXzZOV3VTWmRnXC9waG90by5qcGcifQ.fUsixNLW87PbTecfTt46TjEVgv1gT4byCkHbfizuFZ9";
        $options = [
            'service' => 'test',
            'path' => 'send-email',
            'title' => 'The title of the email',
            'body' => 'The body of the email'
        ];
        $expectedOptions = [
            'headers' => ['Authorization' => "Bearer " . $jwt],
            'verify' => false,
            'json' => [
                'title' => $options['title'],
                'body' => $options['body']
            ]
        ];
        $url = "http://test.soapboxhqtestservice.com/send-email";

        $response = new GuzzleResponse(Response::HTTP_OK);
        $request = Request::create('/test/send-email', 'POST');
        $request->merge($options);
        $request->headers->set('Authorization', 'Bearer ' . $jwt);

        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('request')
            ->with($request->method(), $url, $expectedOptions)
            ->andReturn($response);

        $routesService = new RoutesMapService($client);
        $route = $routesService->getRoute($request);

        $response = $routesService->handler($request, "json", $route->url);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * This test checks that the service gracefully handles exceptions for requests that do not exist
     * eg. [base-url]/service/endpoint
     *
     * @return void
     */
    public function testHandlerCatchesException(): void
    {
        $client = new Client();
        $this->routesService = new RoutesMapService($client);
        $route = $this->routesService->getRoute($this->request);

        $response = $this->routesService->handler(
            $this->request,
            "query",
            $route->url
        );

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode()
        );
    }
}
