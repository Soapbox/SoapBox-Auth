<?php

namespace App\Providers;

use Laravel\Socialite\SocialiteManager;

class SoapboxSocialiteManager extends SocialiteManager
{
	protected function createSlackDriver()
	{
		$config = $this->app['config']['services.slack'];

		return $this->buildProvider(
			\SocialiteProviders\Slack\Provider::class, $config
		);
	}

	protected function createGraphDriver()
	{
		$config = $this->app['config']['services.graph'];

		return $this->buildProvider(
			\SocialiteProviders\Graph\Provider::class, $config
		);
	}
}
