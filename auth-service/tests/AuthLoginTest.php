<?php

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

		$request = new Illuminate\Http\Request;
		$request->oauth_code = "ll";
		$request->provider = 'google';

		$response["jwt"] = "jwt_token";
		$response["status"] = 200;
		$response["message"] = "Success";

		$mock = Mockery::mock('App\Http\Controllers\AuthController');
		$mock->shouldReceive('generateJWTToken')
			->with($request)
			->andReturn($response);

		$this->assertSame($response, $mock->generateJWTToken($request));
	}
}

