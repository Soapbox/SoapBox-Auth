<?php

use Illuminate\Support\Facades\Redis;
use Laravel\Socialite\Facades\Socialite;

class AuthLoginTest extends TestCase
{
	protected $test_driver = 'google';
	protected $test_oauth_code = "ya29.Il-pBx5aS_JhAMwcBo5Ip_cWZ9W19TEYzRKlcLLqZkN4PaFEnrl24y8tXldBR-pPtWxKnwHKa8cpSsuxJXyW2OngfTwVS5G6HKe-KI3pXlP_3C0UdR1XRhYv1ebVwK-fgA";

	public function testValidations()
	{
		$this->json(
			'POST', '/login', [
				'oauth_code' => '',
				'provider' => '',
			]
		)->seeJson(
			[
				'oauth_code' => [
					'The oauth code field is required.'
				],
				'provider' => [
					'The provider field is required.'
				]
			]
		)->assertResponseStatus(422);
	}

	public function testOnlySupportedProvidersAreAllowed()
	{
		$example = ['gogle', 'slck', 'micrsoft', 'unsupported']; //typos or more generic

		$this->json(
			'POST', '/login', [
				'oauth_code' => $this->test_oauth_code,
				'provider' => $example[array_rand($example, 1)],
			]
		)->seeJson(
			[
				'provider' => [
					'The selected provider is invalid.'
				]
			]
		)->assertResponseStatus(422);
	}

	public function testCanGenerateJWTTokenForValidUser()
	{
		$abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
		$abstractUser->shouldReceive('getId')
			->andReturn(1)
			->shouldReceive('getName')
			->andReturn('florence')
			->shouldReceive('getEmail')
			->andReturn('florence@gmail.com');

		$provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
		$provider->shouldReceive('userFromToken')->andReturn($abstractUser);

		Socialite::shouldReceive('driver')->with($this->test_driver)->andReturn($provider);

		$res = $this->json('POST', '/login', [
			'oauth_code' => $this->test_oauth_code,
			'provider' => $this->test_driver,
		]);

		$obj = json_decode($res->response->getContent());
		$token = $obj->{'token'};
		$jwt_library = new \App\Libraries\FirebaseJWTLibrary();
		$decoded_payload = $jwt_library->decode($token);
		$this->assertSame($abstractUser->getName(), $decoded_payload->name);
		$this->assertSame($abstractUser->getEmail(), $decoded_payload->email);
	}

	public function testAssertStatusCodeIs200()
	{
		$abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
		$abstractUser->shouldReceive('getId')
			->andReturn(1)
			->shouldReceive('getName')
			->andReturn('florence')
			->shouldReceive('getEmail')
			->andReturn('florence+1@gmail.com');

		$provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
		$provider->shouldReceive('userFromToken')->andReturn($abstractUser);

		Socialite::shouldReceive('driver')->with($this->test_driver)->andReturn($provider);

		$this->json('POST', '/login', [
			'oauth_code' => $this->test_oauth_code,
			'provider' => $this->test_driver,
		])->seeJsonStructure(
			[
				'token',
				'message'
			]
		)->seeJson(
			[
				'message' => 'Success.'
			]
		)->assertResponseStatus(200);
	}

	public function testSeeJWTInRedisAfterSuccessfulLogin()
	{
		$abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
		$abstractUser->shouldReceive('getId')
			->andReturn(1)
			->shouldReceive('getName')
			->andReturn('florence')
			->shouldReceive('getEmail')
			->andReturn('florence+2@gmail.com');

		$provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
		$provider->shouldReceive('userFromToken')->andReturn($abstractUser);

		Socialite::shouldReceive('driver')->with($this->test_driver)->andReturn($provider);

		$tgsMock = Mockery::mock('\App\Services\TokenGeneratorService');
		$test_iat = time();
		$tgsMock->shouldReceive('getIat')->andReturn($test_iat);

		$res = $this->json('POST', '/login', [
			'oauth_code' => $this->test_oauth_code,
			'provider' => $this->test_driver,
		]);

		$obj = json_decode($res->response->getContent());
		$token = $obj->{'token'};

		//assert the token is infact in Redis
		$this->assertSame($token, Redis::get($test_iat));
	}
}
