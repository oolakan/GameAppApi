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

$app->group(['prefix'  =>  'api/v1/users','namespace' => 'App\Http\Controllers'], function($app){
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
    $app->get('/agent/{mid}', 'UserController@agents');
    $app->get('/agent/{status}/{uid}', 'UserController@changeStatus');
});


//credit balance
$app->group(['prefix'  =>  'api/v1/credit', 'namespace' => 'App\Http\Controllers'], function($app){
    $app->get('/', 'CreditController@index');
    $app->post('/storeOrUpdate', 'CreditController@storeOrUpdate');
});

//game name
$app->group(['prefix'  =>  'api/v1/game', 'namespace' => 'App\Http\Controllers'], function($app){
    $app->get('/', 'GameController@index');
    $app->get('/all/{uid}', 'GameController@allGames');
    $app->post('/gameInfo', 'GameController@gameInfo');
    $app->get('/transactions', 'GameTransactionsController@index');
    $app->get('/transactions/{id}/{from}/{to}', 'GameTransactionsController@transactions');
    $app->post('/gameAvailability', 'GameController@checkGameAvailability');
    $app->post('/save/transactions', 'GameTransactionsController@store');
    $app->get('/validate_game/{serial_no}', 'GameController@validateGame');
    $app->get('/delete/{serial_no}', 'GameTransactionsController@destroy');
    $app->get('/block/{status}/{id}/{uid}', 'GameController@block');
    $app->get('/search/{serial_no}', 'GameController@searchGame');
    $app->get('/statistics/{day}/{date}/{userid}', 'GameController@getGamesStatistics');
    $app->get('/agent/statistics/{from}/{to}/{mid}', 'GameController@getAgentsGamesStatistics');

});

//credit balance
$app->group(['prefix'  =>  'api/v1/winnings', 'namespace' => 'App\Http\Controllers'], function($app){
    $app->get('/view/{id}/{from}/{to}/{status}', 'WinningsController@index');
    $app->get('/win_machine/{from}/{to}', 'WinningsController@winningMachineNos');
    $app->post('/storeOrUpdate', 'CreditController@storeOrUpdate');
});

//credit balance
$app->group(['prefix'  =>  'api/v1/credit', 'namespace' => 'App\Http\Controllers'], function($app){
    $app->get('/{id}', 'CreditController@index');
    $app->get('/balance/{id}/{credit}', 'CreditController@deductCredit');
    $app->get('/update/{aid}/{uid}/{amount}', 'CreditController@updateCredit');
    $app->get('/remove/{aid}/{uid}/{amount}', 'CreditController@removeCredit');

});