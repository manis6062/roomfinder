<?php

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Jagga;

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




Route::group(array('prefix' => 'v1/' ), function() {


# the api routes version 1
Route::group(array('namespace' => 'Api\V1' ), function() {
	Route::post('users/fblogin', 'UsersController@fblogin');


	Route::get('room/search-room','RoomsController@searchRoom');
	Route::get('jagga/search-jagga','JaggasController@searchJagga');
	Route::get('room/detail','RoomsController@rdetail');
	Route::get('jagga/detail','JaggasController@jdetail');
	Route::get('all-room-jagga','UsersController@allRoomJagga');
});


 Route::get('room/checkDeleteOldRooms',function(){
    	Room::checkDeleteOldRooms();
    });
  Route::get('jagga/checkDeleteOldJaggas',function(){
    	Jagga::checkDeleteOldJaggas();
    });
# all routes which requires accesstoken
Route::group(array('middleware' =>['accesstokenchecker'], 'namespace' => 'Api\V1' ), function() {
	Route::post('users/logout', 'UsersController@logout');
	Route::post('room/add','RoomsController@addRoom');
	Route::put('room/update-room','RoomsController@updateRoom');
	Route::post('jagga/add','JaggasController@addJagga');
	Route::put('jagga/update-jagga','JaggasController@updateJagga');
	Route::delete('room/delete','RoomsController@deleteRoom');
	Route::delete('jagga/delete','JaggasController@deleteJagga');

	Route::get('room/my-rooms','RoomsController@myRooms');
	Route::get('jagga/my-jaggas','JaggasController@myJaggas');
	Route::get('jagga/my-favourite-jaggas','JaggasController@myFavouriteJaggas');
	Route::get('room/my-favourite-rooms','RoomsController@myFavouriteRooms');
    Route::post('users/add-to-favourites','UsersController@AddToFavourite');
	Route::post('fav/add-to-favourites','UsersController@AddToFavourite');

   


});

});