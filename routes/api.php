<?php

use App\BlogPost;
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

//REGISTRATION,  LOGIN, GET_USER, LOGOUT
Route::post('/register', 'Auth\AuthController@register');
Route::post('/login', 'Auth\AuthController@login');
Route::get('/user', 'Auth\AuthController@user');//Gets the signed in User
Route::post('/logout', 'Auth\AuthController@logout');

//EMAIL VERIFICATION
Route::post('/resendVerificationLink', 'Auth\AuthController@resendVerificationLink');
Route::post('/verification', 'Auth\AuthController@verification');

//PASSWORD RESET
Route::post('/sendResetPasswordLink', 'Auth\AuthController@sendPasswordResetLink');
Route::post('/passwordReset', 'Auth\AuthController@setNewPassword');

//USER
Route::post('user/addProfilePicture', 'UserController@addProfilePicture');
Route::post('user/getProfilePicture', 'UserController@getProfilePicture');

//CLIMADVICE
Route::post('climadvice/store', 'ClimadviceController@store');
Route::get('climadvice/index', 'ClimadviceController@index');
Route::post('climadvice/update', 'ClimadviceController@update');
Route::post('climadvice/destroy', 'ClimadviceController@destroy');

//BLOG
Route::post('blogPost/store', 'BlogPostController@store');
Route::get('blogPost/index', 'BlogPostController@index');
Route::get('blogPost/getBlogPost', 'BlogPostController@getBlogPost');
Route::post('blogPost/update', 'BlogPostController@update');
Route::post('blogPost/destroy', 'BlogPostController@destroy');
