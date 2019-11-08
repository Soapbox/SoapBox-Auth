<?php

const UNKNOWN_ERROR = 520;

function http_code_by_exception_type(\Exception $e)
{

	if ($e instanceof GuzzleHttp\Exception\ConnectException) {
		return Illuminate\Http\Response::HTTP_BAD_GATEWAY;
	}

	if ($e instanceof \InvalidArgumentException) {
		return Illuminate\Http\Response::HTTP_FORBIDDEN;
	}

	if ($e instanceof GuzzleHttp\Exception\ClientException) {
		return Illuminate\Http\Response::HTTP_UNAUTHORIZED;
	}

	if ($e instanceof GuzzleHttp\Exception\RequestException) {
		return Illuminate\Http\Response::HTTP_BAD_REQUEST;
	}

	return UNKNOWN_ERROR;
}
