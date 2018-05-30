<?php

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



# the api routes version 1
Route::group(array('namespace' => 'Api' ), function() {
	Route::post('users/fblogin', 'UsersController@fblogin');
	Route::post('room/detail','RoomsController@rdetail');
	Route::post('jagga/detail','JaggasController@jdetail');

});



# all routes which requires accesstoken
Route::group(array('middleware' =>['accesstokenchecker'], 'namespace' => 'Api' ), function() {
	Route::post('users/logout', 'UsersController@logout');
	Route::post('room/add','RoomsController@addRoom');
	Route::post('room/update-room','RoomsController@updateRoom');
	Route::post('jagga/add','JaggasController@addJagga');
	Route::post('jagga/update-jagga','JaggasController@updateJagga');
	Route::post('room/delete','RoomsController@deleteRoom');
	Route::post('jagga/delete','JaggasController@deleteJagga');

});
