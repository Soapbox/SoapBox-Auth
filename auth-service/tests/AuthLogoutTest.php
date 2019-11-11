<?php

use Illuminate\Support\Facades\Cache;

class AuthLogoutTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLogoutIsUnssuccessfulIfNoJWTIProvided()
    {
		$this->json(
			'POST', '/logout', [
				'jwt' => '',
			]
		)->seeJson(
			[
				'jwt' => [
					'The jwt field is required.'
				],
			]
		)->assertResponseStatus(422);
    }

    public function testLogoutIsSuccessfulIfJWTIsFoundInCache()
	{
		$test_jwt = "ya29.Il-pBx5aS_JhAMwcBo5Ip_cWZ9W19TEYzRKlcLLqZkN4PaFEnrl24y8tXldBR-pPtWxKnwHKa8cpSsuxJXyW2OngfTwVS5G6HKe-KI3pXlP_3C0UdR1XRhYv1ebVwK-fgA";
		Cache::add($test_jwt, '', 10);

		$this->json(
			'POST', '/logout', [
				'jwt' => $test_jwt,
			]
		)->assertResponseStatus(200);
	}

	public function testLogoutIsNotSuccessfulIfJWTIsNotFoundInCache()
	{
		$test_jwt = "ya29.Il-pBx5aS_JhAMwcBo5Ip_cWZ9W19TEYzRKlcLLqZkN4PaFEnrl24y8tXldBR-pPtWxKnwHKa8cpSsuxJXyW2OngfTwVS5G6HKe-KI3pXlP_3C0UdR1XRhYv1ebVwK-fgA";

		$this->json(
			'POST', '/logout', [
				'jwt' => $test_jwt,
			]
		)->assertResponseStatus(401);
	}
}
