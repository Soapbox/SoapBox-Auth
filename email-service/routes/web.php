<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    //redirect request back to API gateway
});

// TODO:
//request must come from API Gateway any request not from the dedicated API Gateway is rejected
//this can be implemented as middleware over all protected routes like the following

$router->get('user/{id}/emails', function ($id) {
  //at this point user is already authenticated in API Gateway and is GTG
    return 'view all emails for user: ' . $id ;
});

$router->post('user/{id}/email', function ($id) {
  //at this point user is already authenticated in API Gateway and is GTG
    return 'User id: '. $id . ' create new email';
});

$router->put('user/{id}/email/{email_id}', function ($id, $email_id) {
  //at this point user is already authenticated in API Gateway and is GTG
    return 'User id: '. $id . ' full update an exiting email with id: ' . $email_id;
});

$router->patch('user/{id}/email/{email_id}', function ($id, $email_id) {
  //at this point user is already authenticated in API Gateway and is GTG
    return 'User id: '. $id . ' partial update an exiting email with id: ' . $email_id;
});

$router->delete('user/{id}/email/{email_id}', function ($id, $email_id) {
  //at this point user is already authenticated in API Gateway and is GTG
    return 'User id: '. $id . ' delete an exiting email with id: ' . $email_id;
});
