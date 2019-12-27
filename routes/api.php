<?php

use App\Handlungsvorschlag;
use App\Http\Controllers\ClimadviceController;
use App\Http\Resources\HandlungsvorschlagResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

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
// Route::get('/testMail', 'ClimadviceController@testMail');

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return new UserResource($request->user());
// });

//REGISTRATION AND LOGIN
Route::post('/verification', 'Auth\AuthController@verification');
Route::post('/register', 'Auth\AuthController@register');
Route::post('/login', 'Auth\AuthController@login');
Route::get('/user', 'Auth\AuthController@user');//Gets the signed in User
Route::post('/logout', 'Auth\AuthController@logout');
Route::post('/resendVerificationLink', 'Auth\AuthController@resendVerificationLink');

//CLIMADVICE
Route::post('climadvice/store', 'ClimadviceController@store');
Route::get('climadvice/index', 'ClimadviceController@index');
Route::post('climadvice/update', 'ClimadviceController@update');
Route::post('climadvice/destroy', 'ClimadviceController@destroy');

//BLOG
Route::post('blogPost/store', 'BlogPostController@store');
Route::get('blogPost/index', 'BlogPostController@index');