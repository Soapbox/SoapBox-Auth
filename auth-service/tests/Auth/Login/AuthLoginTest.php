<?php

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use App\Libraries\FirebaseJWTLibrary;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use JSHayes\FakeRequests\Traits\Laravel\FakeRequests;

class AuthLoginTest extends TestCase
{
    use FakeRequests;

    protected $test_oauth_code = "ya29.Il-pBx5aS_JhAMwcBo5Ip_cWZ9W19TEYzRKlcLLqZkN4PaFEnrl24y8tXldBR-pPtWxKnwHKa8cpSsuxJXyW2OngfTwVS5G6HKe-KI3pXlP_3C0UdR1XRhYv1ebVwK-fgA";
    protected $abstractUser;
    protected $provider;
    protected $test_token = "ya29.Il-pBx5aS_JhAMwcBo5Ip_cWZ9W19TEYzRKlcLLqZkN4PaFEnrl24y8tXldBR-pPtWxKnwHKa8cpSsuxJXyW2OngfTwVS5G6HKe-KI3pXlP_3C0UdR1XRhYv1ebVwK-fgA";

    public function testValidations()
    {
        $this->json('POST', '/login', [
            'oauth_code' => '',
            'provider' => ''
        ])
            ->seeJson([
                'oauth_code' => ['The oauth code field is required.'],
                'provider' => ['The provider field is required.']
            ])
            ->assertResponseStatus(422);
    }

    public function testOnlySupportedProvidersAreAllowed()
    {
        $example = 'gogle'; //typo in the provider

        $this->json('POST', '/login', [
            'oauth_code' => $this->test_oauth_code,
            'provider' => $example
        ])
            ->seeJson([
                'provider' => ['The selected provider is invalid.']
            ])
            ->assertResponseStatus(422);
    }

    public function assertCanGenerateJWTTokenForValidUser()
    {
        $res = $this->json('POST', '/login', [
            'oauth_code' => $this->test_oauth_code,
            'provider' => $this->driver
        ]);

        $obj = json_decode($res->response->getContent());
        $token = $obj->{'token'};
        $jwt_library = new FirebaseJWTLibrary();
        $decoded_payload = $jwt_library->decode($token);
        $this->assertSame(
            $this->abstractUser->getName(),
            $decoded_payload->name
        );
        $this->assertSame(
            $this->abstractUser->getEmail(),
            $decoded_payload->email
        );
    }

    public function assertStatusCodeIs200()
    {
        $this->json('POST', '/login', [
            'oauth_code' => $this->test_oauth_code,
            'provider' => $this->driver
        ])
            ->seeJsonStructure(['token', 'message'])
            ->seeJson([
                'message' => 'Success.'
            ])
            ->assertResponseStatus(200);
    }

    public function assertSeeJWTInRedisAfterSuccessfulLogin()
    {
        $res = $this->json('POST', '/login', [
            'oauth_code' => $this->test_oauth_code,
            'provider' => $this->driver
        ]);

        $obj = json_decode($res->response->getContent());
        $token = $obj->{'token'};

        //assert the token is infact in Redis
        $this->assertTrue(Cache::has($token));
    }

    public function assertCanLogInWithSoapboxSlug()
    {
        $uri = config('env.dev.login_url') . '/google';

        $handler = $this->fakeRequests();
        $handler
            ->post($uri)
            ->respondWith(
                new GuzzleResponse(
                    Response::HTTP_OK,
                    [],
                    json_encode(['token' => $this->test_token])
                )
            );

        $this->json('POST', 'login', [
            'oauth_code' => $this->test_oauth_code,
            'soapbox-slug' => 'test_slug',
            'redirectUri' => 'the_redirect_url',
            'provider' => 'google'
        ])
            ->seeJsonStructure(['token', 'message'])
            ->seeJson([
                'message' => 'Success.',
                'token' => $this->test_token
            ])
            ->assertResponseStatus(200);
    }
}
