<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

# the api routes version 1
// Route::group(array('namespace' => 'Api/V1'), function() {
//     Route::get('/api/room/detail','RoomsController@rdetail');
// 	Route::get('/api/jagga/detail','JaggasController@jdetail');
// 	Route::get('/api/room/search','RoomsController@search');

// });


Auth::routes();
Route::group(array('middleware' =>['auth']), function() {

Route::get('/home', 'HomeController@index')->name('home');


Route::group(array('namespace' => 'Admin' ), function() {
		Route::get('/admin/rooms', 'RoomsController@lists');
		Route::get('/admin/jaggas', 'JaggasController@lists');
		Route::get('/admin/room-view/{id}', 'RoomsController@view');
		Route::get('/admin/jagga-view/{id}', 'JaggasController@view');
		Route::get('/admin/feedback-view/{id}', 'FeedbackController@view');
		Route::get('/admin/feedbacks', 'FeedbackController@lists');
		Route::get('/admin/spam', 'SpamController@lists');
		Route::get('/admin/spam-edit/{id}', 'SpamController@edit');
	    Route::put('/admin/spam-update/{id}', 'SpamController@update')->name('spam.update');
	    Route::delete('/admin/spam-delete/{id}', 'SpamController@destroy');
});

});