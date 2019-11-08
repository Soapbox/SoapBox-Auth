<?php

namespace App\Providers;

use Laravel\Socialite\SocialiteManager;

class SoapboxSocialiteManager extends SocialiteManager
{
	protected function createSlackDriver()
	{
		$config = $this->app['config']['services.slack'];

		return $this->buildProvider(
			SlackProvider::class, $config
		);
	}
}
