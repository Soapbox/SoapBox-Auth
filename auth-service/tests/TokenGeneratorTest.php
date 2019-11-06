<?php

use Firebase\JWT\JWT;
use App\Libraries\FirebaseJWTLibrary;
use Laravel\Socialite\Facades\Socialite;

class TokenGeneratorTest extends TestCase
{
	protected $config;

	public function setUp(): void
	{
		parent::setup();

		$this->config = [
			'key' => 'omlettedufromage',
			'exp' => 'week',
			'algo' => 'HS256'
		];
	}

	public function testCannotGenerateTokenIfProviderIsNotSet()
	{
		$this->expectException(\InvalidArgumentException::class);
		$token_service = new \App\Services\TokenGeneratorService(new FirebaseJWTLibrary());
		$token_service->generateToken();
	}

	public function testCannotGenerateTokenIfProviderIsNotSupported()
	{
		$this->expectException(\InvalidArgumentException::class);
		$token_service = new \App\Services\TokenGeneratorService(new FirebaseJWTLibrary());
		$token_service->setProvider('unsupported');
		$token_service->generateToken();
	}

	public function testCannotGenerateTokenIfCodeIsNotSet()
	{
		$this->expectException(\InvalidArgumentException::class);
		$token_service = new \App\Services\TokenGeneratorService(new FirebaseJWTLibrary());
		$token_service->generateToken();
	}

	public function testCanGenerateTokenIfCodeAndProviderAreSet()
	{
		$token_service = new \App\Services\TokenGeneratorService(new FirebaseJWTLibrary());
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

		$decoded_payload = JWT::decode($token, $this->config['key'], [$this->config['algo']]);
		$this->assertSame($abstractUser->getName(), $decoded_payload->name);
		$this->assertSame($abstractUser->getEmail(), $decoded_payload->email);
	}
}
