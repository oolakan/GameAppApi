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

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->group(['prefix' => 'dashboard'], function($app){
    $app->get('/', 'DashboardController@index');
});


//users

$app->group(['prefix'  =>  'v1/users','namespace' => 'App\Http\Controllers'], function($app){
    $app->get('/', 'UserController@index');
    $app->get('/{id}', 'UserController@getUser');
    $app->post('/otp', 'UserController@createOtpForSignUp');
    $app->post('/otp/password_reset', 'UserController@createOtpForPasswordReset');
    $app->post('/register/{id}', 'UserController@completeUserRegistration');
    $app->post('/login', 'UserController@loginUser');
    $app->post('/reset_password', 'UserController@resetPassword');
    $app->put('/{id}', 'UserController@updateUser');
    $app->delete('/{id}', 'UserController@deleteUser');
    $app->get('/transaction/{id}', 'UserController@getTransactions');
});


//credit balance
$app->group(['prefix'  =>  'v1/credit', 'namespace' => 'App\Http\Controllers'], function($app){
    $app->get('/', 'CreditController@index');
    $app->post('/storeOrUpdate', 'CreditController@storeOrUpdate');
});

//game name
$app->group(['prefix'  =>  'v1/game', 'namespace' => 'App\Http\Controllers'], function($app){
    $app->get('/', 'GameController@index');
    $app->post('/gameInfo', 'GameController@gameInfo');
    $app->get('/transactions', 'GameTransactionsController@index');
    $app->post('/gameAvailability', 'GameController@checkGameAvailability');
});
