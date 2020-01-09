<?php

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

class SlackControllerTest extends TestCase
{
    /**
     * An instance of guzzle http client
     *
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * Setup initialises controller to be used in tests
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $response = new GuzzleResponse(Response::HTTP_OK);
        $this->client = Mockery::mock(Client::class);
        $this->client->shouldReceive('request')->andReturn($response);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_login()
    {
        $response = $this->get('/api/slack-login');

        $response->assertStatus(200);
    }
}
