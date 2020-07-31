<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::get('users','Api\UserController@index');
Route::post('register','Api\UserController@register');
Route::get('email/verify/{id}', 'Api\UserController@verify')->name('verification.verify'); // Make sure to keep this as your route name
Route::get('email/resend', 'Api\UserController@resend')->name('verification.resend');
Route::get('/user/verifySuccess/{id}', 'Api\UserController@verifySuccess');

Route::group([
    'prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
  
    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});