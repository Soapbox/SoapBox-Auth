<?php

use GuzzleHttp\Client;
use App\Services\RoutesMapService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

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
        $response = new \GuzzleHttp\Psr7\Response(Response::HTTP_OK);
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
     * This test checks that the service forwards requests to the underlying service
     * eg. [base-url]/service/endpoint
     *
     * @return void
     */
    public function testHandlerRouteForwarding(): void
    {
        $route = $this->routesService->getRoute($this->request);

        $response = $this->routesService->handler(
            $this->request,
            "query",
            $route->url
        );

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
