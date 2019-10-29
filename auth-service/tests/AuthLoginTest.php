<?php

use Firebase\JWT\JWT;
use Laravel\Socialite\Facades\Socialite;

class AuthLoginTest extends TestCase
{
	/**
	 * A basic test example.
	 *
	 * @return void
	 */
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

	public function testGenerateJWTToken() {

		$abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
		$abstractUser->shouldReceive('getId')
			->andReturn(1)
			->shouldReceive('getName')
			->andReturn('florence')
			->shouldReceive('getEmail')
			->andReturn('florence@gmail.com');

		$provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
		$provider->shouldReceive('userFromToken')->andReturn($abstractUser);

		Socialite::shouldReceive('driver')->with('google')->andReturn($provider);



//		$socialliteMock = Socialite::shouldReceive('driver->userFromToken')->andReturn($abstractUser);


//		$this->json('POST', '/login', [
//			'oauth_code' => 'ya29.Il-pBx5aS_JhAMwcBo5Ip_cWZ9W19TEYzRKlcLLqZkN4PaFEnrl24y8tXldBR-pPtWxKnwHKa8cpSsuxJXyW2OngfTwVS5G6HKe-KI3pXlP_3C0UdR1XRhYv1ebVwK-fgA',
//			'provider' => 'google',
//		])->seeJsonStructure(
//			[
//				'token', 'message'
//			]
//		)->assertResponseStatus(200);

		$res = $this->json('POST', '/login', [
			'oauth_code' => 'ya29.Il-pBx5aS_JhAMwcBo5Ip_cWZ9W19TEYzRKlcLLqZkN4PaFEnrl24y8tXldBR-pPtWxKnwHKa8cpSsuxJXyW2OngfTwVS5G6HKe-KI3pXlP_3C0UdR1XRhYv1ebVwK-fgA',
			'provider' => 'google',
		]);

		$obj = json_decode($res->response->getContent());
		$token = $obj->{'token'};
		dd(JWT::decode($token, 'omlettedufromage', ['HS256']));
	}
}

