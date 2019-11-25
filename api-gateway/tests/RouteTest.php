<?php

use Illuminate\Http\Response;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class RouteTest extends TestCase
{
    /**
     * The jwt to be used in requests
     * @var string
     */
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
     * This function prepares a test to return a valid response to a guzzle request
     *
     * @param \GuzzleHttp\Psr7\Response $response
     * @return void
     */
    public function prepareValidResponse($response = null): void
    {
        $response = isset($response)
            ? $response
            : new GuzzleResponse(Response::HTTP_OK);
        $client = Mockery::mock(Client::class);
        $client
            ->shouldReceive('request')
            ->once()
            ->andReturn($response);
        $this->app->instance(Client::class, $client);
    }

    /**
     * This test checks that a valid get operation returns 200 as expected
     *
     * @return void
     */
    public function testValidGet(): void
    {
        $this->prepareValidResponse();

        $this->get('/test/health-check');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->response->getStatusCode()
        );
    }

    /**
     * This test checks that the API gateway forwards raw strings
     * if received as response from the service it relays to
     *
     * @return void
     */
    public function testAPIGatewayForwardsRawString(): void
    {
        $response = new GuzzleResponse(Response::HTTP_OK, [], "Body Text");
        $this->prepareValidResponse($response);

        $this->get('/test/health-check');

        $content = json_decode($this->response->getContent(), true);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->response->getStatusCode()
        );
        $this->assertEquals("Body Text", $content);
    }

    /**
     * This test checks that the API gateway forwards JSON strings
     * if received as response from the service it relays to
     *
     * @return void
     */
    public function testAPIGatewayForwardsJSONString(): void
    {
        $response = new GuzzleResponse(
            Response::HTTP_OK,
            [],
            json_encode("Body Text")
        );
        $this->prepareValidResponse($response);

        $this->get('/test/health-check');

        $content = json_decode($this->response->getContent(), true);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->response->getStatusCode()
        );
        $this->assertEquals("Body Text", $content);
    }

    /**
     * This test checks that the API gateway forwards JSON arrays
     * if received as response from the service it relays to
     *
     * @return void
     */
    public function testAPIGatewayForwardsJSONArray(): void
    {
        $response = new GuzzleResponse(
            Response::HTTP_OK,
            [],
            json_encode([
                "name" => "Name body"
            ])
        );
        $this->prepareValidResponse($response);

        $this->get('/test/health-check');

        $content = json_decode($this->response->getContent(), true);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->response->getStatusCode()
        );
        $this->assertArrayHasKey("name", $content);
    }

    /**
     * This test checks that a request to a non existent service returns 404
     *
     * @return void
     */
    public function testInvalidGet(): void
    {
        $this->get('/service/not-found');

        $this->assertEquals(
            Response::HTTP_NOT_FOUND,
            $this->response->getStatusCode()
        );
    }

    /**
     * This test checks that a valid post operation returns 200 as expected
     *
     * @return void
     */
    public function testValidPost(): void
    {
        $this->prepareValidResponse();

        $this->json(
            'POST',
            '/test/send-email',
            ['subject' => 'Sally', 'body' => 'Ommlette du fromage'],
            ['Authorization' => 'Bearer ' . $this->jwt]
        );
        $this->assertEquals(Response::HTTP_OK, $this->response->status());
    }

    /**
     * This test checks that an unauthorized post operation returns 401
     *
     * @return void
     */
    public function testUnauthorizedPost(): void
    {
        $this->json('POST', '/test/send-email', [
            'subject' => 'Sally',
            'body' => 'Ommlette du fromage'
        ]);
        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $this->response->status()
        );
    }

    /**
     * This test checks that a valid put operation returns 200 as expected
     *
     * @return void
     */
    public function testValidPut(): void
    {
        $this->prepareValidResponse();

        $this->json(
            'PUT',
            '/test/address',
            ['user_id' => 1, 'email' => 'ommlette.du@fromage.com'],
            ['Authorization' => 'Bearer ' . $this->jwt]
        );
        $this->assertEquals(Response::HTTP_OK, $this->response->status());
    }

    /**
     * This test checks that a valid delete operation returns 200 as expected
     *
     * @return void
     */
    public function testValidDelete(): void
    {
        $this->prepareValidResponse();

        $this->json(
            'DELETE',
            '/test/records',
            ['user_id' => 1],
            ['Authorization' => 'Bearer ' . $this->jwt]
        );
        $this->assertEquals(Response::HTTP_OK, $this->response->status());
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
