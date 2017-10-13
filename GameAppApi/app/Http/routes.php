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
$app->group(['prefix'  =>  'users'], function($app){
    $app->get('/', 'UserController@index');
    $app->get('/create', 'UserController@create');
    $app->post('/update/{id}', 'UserController@update');
    $app->get('/delete/{id}', 'UserController@destroy');
    $app->post('/store', 'UserController@store');
});


//credit balance
$app->group(['prefix'  =>  'credit'], function($app){
    $app->get('/', 'CreditController@index');
    $app->post('/storeOrUpdate', 'CreditController@storeOrUpdate');
});

//game name
$app->group(['prefix'  =>  'game_name'], function($app){
    $app->get('/', 'GameNameController@index');
    $app->post('/store', 'GameNameController@store');
    $app->post('/update/{id}', 'GameNameController@update');
    $app->post('/delete/{id}', 'GameNameController@destroy');
});

//game type
$app->group(['prefix'  =>  'game_type'], function($app){
    $app->get('/', 'GameTypeController@index');
    $app->post('/store', 'GameTypeController@store');
    $app->post('/update/{id}', 'GameTypeController@update');
    $app->post('/delete/{id}', 'GameTypeController@destroy');
});
//
////game type option
//Route::group(['prefix'  =>  'game_type_option'], function(){
//    Route::get('/', 'GameTypeOptionsController@index')->middleware('auth');
//    Route::post('/store', 'GameTypeOptionsController@store')->middleware('auth');
//    Route::post('/update/{id}', 'GameTypeOptionsController@update')->middleware('auth');
//    Route::post('/delete/{id}', 'GameTypeOptionsController@destroy')->middleware('auth');
//});
//
////game type option
//Route::group(['prefix'  =>  'game_quater'], function(){
//    Route::get('/', 'GameQuaterController@index')->middleware('auth');
//    Route::post('/store', 'GameQuaterController@store')->middleware('auth');
//    Route::post('/update/{id}', 'GameQuaterController@update')->middleware('auth');
//    Route::post('/delete/{id}', 'GameQuaterController@destroy')->middleware('auth');
//});
//
////game full Information
//Route::group(['prefix'  =>  'game'], function(){
//    Route::get('/', 'GameController@index')->middleware('auth');
//    Route::post('/store', 'GameController@store')->middleware('auth');
//    Route::post('/update/{id}', 'GameController@update')->middleware('auth');
//    Route::post('/delete/{id}', 'GameController@destroy')->middleware('auth');
//});
