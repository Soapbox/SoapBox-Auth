<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    public function login(Request $request)
	{
		$this->validate($request, [
			'code' => 'required|string',
			'provider' => 'required'
		]);

		try {
			$user = Socialite::driver($request->provider)->stateless()->user();
			$statusCode = 200;
		} catch (\Exception $e) {
			$user = null;
			$statusCode = 404;
			$msg = $e->getMessage();
		}


		return response(
			[
				"user"      => $user,
				'status'    => $user ? 'success' : $msg
			], $statusCode ?? 200
		);
	}

	public function logout(){}
}
