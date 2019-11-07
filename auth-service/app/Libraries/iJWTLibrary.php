<?php

namespace App\Libraries;

interface iJWTLibrary
{
	public function encode($payload);
	public function decode($token);
	public function getExpiry();
	public function getKey();
}