<?php

return [
	"firebase_jwt" => [
		"key" => env("JWT_KEY"),
		"exp" => env("JWT_EXP"),
		"algo" => env("JWT_ALGO")
	],
	'supported_providers' => [
		'google', 'slack', 'microsoft'
	]
];