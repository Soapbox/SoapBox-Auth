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

// Route::group(['middleware' => 'web'], function () {
Route::get('/', function () {
    return view('login');
});

Route::get('app', [
    "uses" => "DashboardController@show"
])->middleware("auth");

Route::get('app/{slug}', [
    "uses" => "DashboardController@showForSlug"
])->middleware("auth");

Route::get('google-login', [
    "uses" => "Auth\GoogleController@login"
]);

Route::post('login', [
    "uses" => "Auth\LoginController@login"
]);
// });
