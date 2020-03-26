<?php

use App\BlogPost;
use App\Handlungsvorschlag;
use App\Http\Controllers\ClimadviceController;
use App\Http\Controllers\UserController;
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
Route::post('user/addProfilePicture_ByCurrentUser', 'UserController@addProfilePicture_ByCurrentUser');
Route::post('user/getProfilePicture_ByCurrentUser', 'UserController@getProfilePicture_ByCurrentUser');
Route::post('user/saveAddressAndInstagram_ByCurrentUser', 'UserController@saveAddressAndInstagram_ByCurrentUser');
Route::post('user/isCompanyAdmin_ByCurrentUser', 'UserController@isCompanyAdmin_ByCurrentUser');
Route::post('user/checkShowGratulationBecomingClimateMaster_ByCurrentUser', 'UserController@checkShowGratulationBecomingClimateMaster_ByCurrentUser');
Route::post('user/getDataToShowPublicUserProfile_ByUsername', 'UserController@getDataToShowPublicUserProfile_ByUsername');

//CLIMADVICE
Route::post('climadvice/storeClimadvice', 'ClimadviceController@storeClimadvice');
Route::get('climadvice/getAllClimadvices', 'ClimadviceController@getAllClimadvices');
Route::post('climadvice/updateClimadvice_ByClimadviceID', 'ClimadviceController@updateClimadvice_ByClimadviceID');
Route::post('climadvice/destroyClimadvice_ByClimadviceID', 'ClimadviceController@destroyClimadvice_ByClimadviceID');

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
Route::post('company/storeCompany', 'CompanyController@storeCompany');
Route::get('company/getCompany_ByCompanyID', 'CompanyController@getCompany_ByCompanyID');
Route::get('company/getAllCompanies', 'CompanyController@getAllCompanies');
Route::get('company/getActivatedCompanies_ByClimadviceName', 'CompanyController@getActivatedCompanies_ByClimadviceName');
Route::post('company/updateCompany_ByCompanyID', 'CompanyController@updateCompany_ByCompanyID');
Route::post('company/storeHeaderImage_ByCompanyID', 'CompanyController@storeHeaderImage_ByCompanyID');
Route::post('company/storeLogoImage_ByCompanyID', 'CompanyController@storeLogoImage_ByCompanyID');
Route::post('company/getAdminsOfCompany_ByCompanyID', 'CompanyController@getAdminsOfCompany_ByCompanyID');
Route::post('company/addAdmin_ByCompanyID', 'CompanyController@addAdmin_ByCompanyID');
Route::post('company/removeAdmin_ByCompanyID', 'CompanyController@removeAdmin_ByCompanyID');

//Company Slideshowimage
Route::post('companyslideshowimage/storeSlideshowimage', 'CompanySlideshowimageController@storeSlideshowimage');
Route::get('companyslideshowimage/getSlideshowimages_ByCompanyID', 'CompanySlideshowimageController@getSlideshowimages_ByCompanyID');
Route::post('companyslideshowimage/destroySlideshowimage_BySlideshowimageID', 'CompanySlideshowimageController@destroySlideshowimage_BySlideshowimageID');

//ADMIN FUNCTIONALITY
Route::post('admin/getCompaniesToActivate', 'AdminController@getCompaniesToActivate');
Route::post('admin/activateCompany_ByCompanyID', 'AdminController@activateCompany_ByCompanyID');
Route::post('admin/deactivateCompany_ByCompanyID', 'AdminController@deactivateCompany_ByCompanyID');
Route::post('admin/getAllUsers', 'AdminController@getAllUsers');
Route::post('admin/getLastCO2CalculationOfUser_ByUserID', 'AdminController@getLastCO2CalculationOfUser_ByUserID');
Route::post('admin/setUserClimatemaster_ByUserID', 'AdminController@setUserClimatemaster_ByUserID');
Route::post('admin/getAllImagesForPublication', 'AdminController@getAllImagesForPublication');
Route::post('admin/downloadPictureFromImagecreator_ByPictureForImagecreatorID', 'AdminController@downloadPictureFromImagecreator_ByPictureForImagecreatorID');


//CO2Calculation
Route::post('co2calculation/storeCO2Calculation_ByCurrentUser', "CO2CalculationController@storeCO2Calculation_ByCurrentUser");
Route::post('co2calculation/getLatestCO2Calculation_ByCurrentUser', 'CO2CalculationController@getLatestCO2Calculation_ByCurrentUser');
Route::post('co2calculation/getLatestCO2CalculationForPublicProfileByUsername', 'CO2CalculationController@getLatestCO2CalculationForPublicProfileByUsername');

//Climatemaster_steps_completed
Route::post('climatemaster_steps_completed/getClimatemaster_steps_completed_ByCurrentUser', 'Climatemaster_steps_completedController@getClimatemaster_steps_completed_ByCurrentUser');
Route::post('climatemaster_steps_completed/reduceShortTermCompleted_ByCurrentUser', 'Climatemaster_steps_completedController@reduceShortTermCompleted_ByCurrentUser');
Route::post('climatemaster_steps_completed/customizeCalculationCompleted_ByCurrentUser', 'Climatemaster_steps_completedController@customizeCalculationCompleted_ByCurrentUser');

//Paypal
Route::get('paypal/payment', 'PaypalController@payment');
Route::get('paypal/cancel', 'PaypalController@cancel')->name('paypal.cancel');
Route::get('paypal/success', 'PaypalController@success')->name('paypal.success');
Route::post('paypal/checkPayment', 'PaypalController@checkPayment');


//PictureForImageCreator
Route::post('picture_for_imagecreator/storePictureForImageCreator_ByCurrentUser', 'PictureForImagecreatorController@storePictureForImageCreator_ByCurrentUser');
Route::post('picture_for_imagecreator/getPicturesForImagecreator_ByCurrentUser', 'PictureForImagecreatorController@getPicturesForImagecreator_ByCurrentUser');
Route::post('picture_for_imagecreator/destroyPictureForImagecreator_ByPictureForImagecreatorID', 'PictureForImagecreatorController@destroyPictureForImagecreator_ByPictureForImagecreatorID');
Route::post('picture_for_imagecreator/download_ByPictureForImagecreatorID', 'PictureForImagecreatorController@download_ByPictureForImagecreatorID');
Route::post('picture_for_imagecreator/updateSharingPermitted_ByPictureForImagecreatorID', 'PictureForImagecreatorController@updateSharingPermitted_ByPictureForImagecreatorID');


//PublicUserProfile
Route::post('publicUserProfile/getPublicUserProfile_ByCurrentUser', 'PublicUserProfileController@getPublicUserProfile_ByCurrentUser');
Route::post('publicUserProfile/changePublic_ByCurrentUser', 'PublicUserProfileController@changePublic_ByCurrentUser');
Route::post('publicUserProfile/updatePublicUserProfile_ByCurrentUser', 'PublicUserProfileController@updatePublicUserProfile_ByCurrentUser');
Route::post('publicUserProfile/getAllWithCalculationAndProfilePicture', 'PublicUserProfileController@getAllWithCalculationAndProfilePicture');