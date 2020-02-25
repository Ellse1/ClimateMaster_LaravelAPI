<?php

use App\BlogPost;
use App\Handlungsvorschlag;
use App\Http\Controllers\ClimadviceController;
use App\Http\Resources\HandlungsvorschlagResource;
use App\Http\Resources\UserResource;

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
Route::post('user/saveAddressAndInstagram', 'UserController@saveAddressAndInstagram');
Route::post('user/isCompanyAdmin', 'UserController@isCompanyAdmin');
Route::post('user/getPictureForImagecreator', 'UserController@getPictureForImageCreator');
Route::post('user/addPictureForImagecreator', 'UserController@addPictureForImageCreator');
Route::post('user/checkShowGratulationBecomingClimateMaster', 'UserController@checkShowGratulationBecomingClimateMaster');


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

//Files
Route::get('file/conceptSummary', 'FileController@showConceptSummary');
Route::get('file/concept', 'FileController@showConcept');

//COMPANY
Route::post('company/store', 'CompanyController@store');
Route::get('company/getCompany', 'CompanyController@getCompany');
Route::get('company/getCompanies', 'CompanyController@getCompanies');
Route::get('company/getActivatedCompaniesByClimadviceName', 'CompanyController@getActivatedCompaniesByClimadviceName');
Route::post('company/update', 'CompanyController@update');
Route::post('company/storeHeaderImage', 'CompanyController@storeHeaderImage');
Route::post('company/storeLogoImage', 'CompanyController@storeLogoImage');
Route::post('company/getAdminsOfCompany', 'CompanyController@getAdminsOfCompany');
Route::post('company/addAdmin', 'CompanyController@addAdmin');
Route::post('company/removeAdmin', 'CompanyController@removeAdmin');

//Company Slideshowimage
Route::post('companyslideshowimage/store', 'CompanySlideshowimageController@store');
Route::get('companyslideshowimage/getSlideshowimageByCompanyID', 'CompanySlideshowimageController@getSlideshowimageByCompanyID');
Route::post('companyslideshowimage/destroy', 'CompanySlideshowimageController@destroy');

//ADMIN FUNCTIONALITY
Route::post('admin/getCompaniesToActivate', 'AdminController@getCompaniesToActivate');
Route::post('admin/activateCompany', 'AdminController@activateCompany');
Route::post('admin/deactivateCompany', 'AdminController@deactivateCompany');
Route::post('admin/getAllUsers', 'AdminController@getAllUsers');
Route::post('admin/getLastCalculationOfUser', 'AdminController@getLastCalculationOfUser');
Route::post('admin/setUserClimatemaster', 'AdminController@setUserClimatemaster');

//CO2Calculation
Route::post('co2calculation/store', "CO2CalculationController@store");
Route::post('co2calculation/getLatestCalculation', 'CO2CalculationController@getLatestCalculation');

//Climatemaster_steps_completed
Route::post('climatemaster_steps_completed/getCurrentClimatemaster_steps_completed', 'Climatemaster_steps_completedController@getCurrentClimatemaster_steps_completed');
Route::post('climatemaster_steps_completed/reduceShortTermCompleted', 'Climatemaster_steps_completedController@reduceShortTermCompleted');
Route::post('climatemaster_steps_completed/customizeCalculationCompleted', 'Climatemaster_steps_completedController@customizeCalculationCompleted');

//Paypal
Route::get('paypal/payment', 'PaypalController@payment');
Route::get('paypal/cancel', 'PaypalController@cancel')->name('paypal.cancel');
Route::get('paypal/success', 'PaypalController@success')->name('paypal.success');
Route::post('paypal/checkPayment', 'PaypalController@checkPayment');


//PictureForImageCreator
Route::post('picture_for_imagecreator/store', 'PictureForImagecreatorController@store');
Route::post('picture_for_imagecreator/getPicturesOfCurrentUser', 'PictureForImagecreatorController@getPicturesOfCurrentUser');
Route::post('picture_for_imagecreator/destroy', 'PictureForImagecreatorController@destroy');
Route::post('picture_for_imagecreator/download', 'PictureForImagecreatorController@download');
Route::post('picture_for_imagecreator/updateSharingPermitted', 'PictureForImagecreatorController@updateSharingPermitted');