<?php

use App\Handlungsvorschlag;
use App\Http\Controllers\ClimadviceController;
use App\Http\Resources\HandlungsvorschlagResource;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('/register', 'Auth\AuthController@register');
Route::post('/login', 'Auth\AuthController@login');
Route::get('/user', 'Auth\AuthController@user');//Gets the signed in User
Route::post('/logout', 'Auth\AuthController@logout');

// Route::post('handlungsvorschlagHinzufuegen', 'HandlungsvorschlagController@store');
// Route::get('handlungsvorschlag/{ID}', function($id){
//     return new HandlungsvorschlagResource(handlungsvorschlag::find($id));
// });
// Route::get('handlungsvorschlag', function(){
//     return HandlungsvorschlagResource::collection(handlungsvorschlag::all());
// });



Route::post('climadvice/store', 'ClimadviceController@store');
Route::get('climadvice/index', 'ClimadviceController@index');
Route::post('climadvice/update', 'ClimadviceController@update');
Route::post('climadvice/destroy', 'ClimadviceController@destroy');