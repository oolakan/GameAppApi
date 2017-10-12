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
$app->get('/api/mail/credential/{name}/{email}/{username}/{password}', 'MailController@credentials');
$app->get('/api/mail/message/{username}/{email}/{msg}/{title}', 'MailController@message');


//send sms
$app->post('/api/v1/sms', 'SmsController@sms');
$app->get('/api/mail/cred/{name}/{email}/{username}/{password}/{msg}', 'MailController@cred');
$app->get('/api/mail/msg/{email}/{msg}/{title}', 'MailController@msg');



//$app->get('/api/invoice', 'MailController@invoice');


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


//users
$app->group(['prefix' => 'api/v1/user', 'namespace' => 'App\Http\Controllers'], function($app)
{
    $app->post('/activate', 'AppUserController@activate');
});

//Course codes and materials route
$app->group(['prefix' => 'api/v1/course', 'namespace' => 'App\Http\Controllers'], function($app)
{
    $app->get('/code', 'CourseCodeController@index');
    $app->get('/material/{course_id}/{material_type}', 'CourseMaterialController@fetch');
//    $app->get('/material/{id}', 'CourseMaterialController@fetch');
});
