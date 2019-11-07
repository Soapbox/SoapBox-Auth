<?php

namespace App\Providers;

use Laravel\Socialite\SocialiteManager;

class SoapboxSocilateManager extends SocialiteManager
{
	protected function createSlackDriver()
	{
		$config = $this->app['config']['services.slack'];

		return $this->buildProvider(
			SlackProvider::class, $config
		);
	}
}
