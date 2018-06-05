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



// # the api routes version 1
// Route::group(array('namespace' => 'Api' ), function() {
// 	Route::post('users/fblogin', 'UsersController@fblogin');
// });



// # all routes which requires accesstoken
// Route::group(array('middleware' =>['accesstokenchecker'], 'namespace' => 'Api' ), function() {
// 	Route::post('users/logout', 'UsersController@logout');
// 	Route::post('room/add','RoomsController@addRoom');
// 	Route::patch('room/update-room','RoomsController@updateRoom');
// 	Route::post('jagga/add','JaggasController@addJagga');
// 	Route::patch('jagga/update-jagga','JaggasController@updateJagga');
// 	Route::delete('room/delete','RoomsController@deleteRoom');
// 	Route::delete('jagga/delete','JaggasController@deleteJagga');

// });

Route::group(array('prefix' => 'v1/' ), function() {


# the api routes version 1
Route::group(array('namespace' => 'Api\V1' ), function() {
	Route::post('users/fblogin', 'UsersController@fblogin');


	Route::get('room/search-room','RoomsController@searchRoom');
	Route::get('jagga/search-jagga','JaggasController@searchJagga');
	Route::get('room/detail','RoomsController@rdetail');
	Route::get('jagga/detail','JaggasController@jdetail');
});



# all routes which requires accesstoken
Route::group(array('middleware' =>['accesstokenchecker'], 'namespace' => 'Api\V1' ), function() {
	Route::post('users/logout', 'UsersController@logout');
	Route::post('room/add','RoomsController@addRoom');
	Route::put('room/update-room','RoomsController@updateRoom');
	Route::post('jagga/add','JaggasController@addJagga');
	Route::put('jagga/update-jagga','JaggasController@updateJagga');
	Route::post('room/delete','RoomsController@deleteRoom');
	Route::post('jagga/delete','JaggasController@deleteJagga');

	Route::post('room/my-rooms','RoomsController@myRooms');
	Route::post('jagga/my-jaggas','JaggasController@myJaggas');

});

});