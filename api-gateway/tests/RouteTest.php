<?php

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

class RouteTest extends TestCase
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
