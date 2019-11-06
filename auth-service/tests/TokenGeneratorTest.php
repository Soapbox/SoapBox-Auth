<?php

use Firebase\JWT\JWT;
use Laravel\Socialite\Facades\Socialite;

class TokenGeneratorTest extends TestCase
{

	public function testCannotGenerateTokenIfProviderIsNotSet()
	{
		$this->expectException(\InvalidArgumentException::class);
		$token_service = new \App\Services\TokenGeneratorService();
		$token_service->generateToken();
	}

	public function testCannotGenerateTokenIfProviderIsNotSupported()
	{
		$this->expectException(\InvalidArgumentException::class);
		$token_service = new \App\Services\TokenGeneratorService();
		$token_service->setProvider('unsupported');
		$token_service->generateToken();
	}

	public function testCannotGenerateTokenIfCodeIsNotSet()
	{
		$this->expectException(\InvalidArgumentException::class);
		$token_service = new \App\Services\TokenGeneratorService();
		$token_service->generateToken();
	}

	public function testCanGenerateTokenIfCodeAndProviderAreNotSet()
	{
		$token_service = new \App\Services\TokenGeneratorService();
		$token_service->setProvider('google');
		$token_service->setCode('ya29.Il-pBx5aS_JhAMwcBo5Ip_cWZ9W19TEYzRKlcLLqZkN4PaFEnrl24y8tXldBR-pPtWxKnwHKa8cpSsuxJXyW2OngfTwVS5G6HKe-KI3pXlP_3C0UdR1XRhYv1ebVwK-fgA"');

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

		$token = $token_service->generateToken();
		$this->assertNotEmpty($token);

		$decoded_payload = JWT::decode($token, env('JWT_KEY'), [env('JWT_ALGO')]);
		$this->assertSame($abstractUser->getName(), $decoded_payload->name);
		$this->assertSame($abstractUser->getEmail(), $decoded_payload->email);
	}
}
