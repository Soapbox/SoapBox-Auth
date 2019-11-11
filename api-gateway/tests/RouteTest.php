<?php

use Illuminate\Http\Response;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;

class RouteTest extends TestCase
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
     * This function prepares a test to return a valid response to a guzzle request
     *
     * @param int $code
     * @return void
     */
    public function prepareValidResponse($code): void
    {
        $response = new \GuzzleHttp\Psr7\Response($code);
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
        $this->prepareValidResponse(Response::HTTP_OK);

        $this->get('/email/health-check');

        $this->assertEquals(
            Response::HTTP_OK,
            $this->response->getStatusCode()
        );

        $this->app->instance(Client::class, null);
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
        $this->prepareValidResponse(Response::HTTP_OK);

        $this->json(
            'POST',
            '/email/send-email',
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
        $response = $this->json('POST', '/email/send-email', [
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
        $this->prepareValidResponse(Response::HTTP_OK);

        $response = $this->json(
            'PUT',
            '/email/address',
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
        $this->prepareValidResponse(Response::HTTP_OK);

        $response = $this->json(
            'DELETE',
            '/email/records',
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
