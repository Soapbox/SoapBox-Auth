<?php

return [
	'google' => [
		'client_id' => env('GOOGLE_CLIENT_ID'),
		'client_secret' => env('GOOGLE_CLIENT_SECRET'),
		'redirect' => null,
	],
	'slack' => [
		'client_id' => env('SLACK_KEY'),
		'client_secret' => env('SLACK_SECRET'),
		'redirect' => null
	]
];
