<?php

use Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Http\Response;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

class LoginControllerTest extends TestCase
{

    /**
     * A test when the login flow is successful.
     *
     * @return void
     */
    public function test_success_login()
    {
        $this->mock(Client::class, function ($mock) {
            $res = new GuzzleResponse(Response::HTTP_OK, [], json_encode([
                "token" => "12345"
            ]));
            $mock->shouldReceive('request')->andReturn($res);
        });

        $response = $this->post('/login', [
            "oauth_code"    => "12345",
            "provider"      => "google",
            "redirectUri"   => "http://ommlete.du",
            "soapbox-slug"  => "slug"
        ]);

        $response->assertRedirect('app');
        $response->assertStatus(302);
        $response->assertSessionHas("jwt", "12345");
    }

    /**
     * A test when the login flow is unsuccessful.
     *
     * @return void
     */
    public function test_error_login()
    {
        $this->mock(Client::class, function ($mock) {
            $res = new GuzzleResponse(Response::HTTP_UNAUTHORIZED);
            $mock->shouldReceive('request')->andReturn($res);
        });

        $response = $this->post('/login', [
            "oauth_code"    => "12345",
            "provider"      => "google",
            "redirectUri"   => "http://ommlete.du",
            "soapbox-slug"  => "slug"
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas("message", "A problem happened during login");
    }

    /**
     * A test for logout
     *
     * @return void
     */
    public function test_logout()
    {
        $response = $this->get('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing('jwt');
    }
}
