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

Route::get('/', function () {
    return view('login');
});

Route::get('app', [
    "uses" => "DashboardController@show"
]);

Route::get('app/{slug}', [
    "uses" => "DashboardController@showForSlug"
]);

Route::get('slack-login', [
    "uses" => "Auth\SlackController@login"
]);

Route::get('google-login', [
    "uses" => "Auth\GoogleController@login"
]);
