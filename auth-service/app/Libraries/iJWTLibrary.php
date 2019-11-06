<?php

namespace App\Libraries;

interface iJWTLibrary
{
	public function encode($payload);
}