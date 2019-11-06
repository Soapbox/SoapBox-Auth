<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Http\Response;

class UserNotFoundException extends Exception
{
	public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->code = Response::HTTP_NOT_FOUND;
	}
}
