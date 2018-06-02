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

Route::get('/home', 'HomeController@index')->name('home');


Route::group(array('namespace' => 'Admin' ), function() {
		Route::get('/admin/rooms', 'RoomsController@lists');
		Route::get('/admin/jaggas', 'JaggasController@lists');
		Route::get('/admin/room-edit/{id}', 'RoomsController@edit');
		Route::get('/admin/jagga-edit/{id}', 'JaggasController@edit');
		Route::get('/admin/feedbacks', 'FeedbackController@lists');
		Route::get('/admin/spam', 'SpamController@lists');
});

