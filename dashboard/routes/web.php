<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', function () {
        return view('login');
    });

    Route::get('app', [
        "uses" => "DashboardController@show"
    ]);

    Route::get('logout', [
        "uses" => "Auth\LoginController@logout"
    ]);
});

Route::get('google-login', [
    "uses" => "Auth\GoogleController@login"
]);

Route::post('login', [
    "uses" => "Auth\LoginController@login"
]);
