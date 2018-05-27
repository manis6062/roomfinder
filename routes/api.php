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
	Route::post('users/reset-password', 'UsersController@resetPassword');
	Route::post('users/reset-password-process', 'UsersController@resetPasswordProcess');

	Route::post('users/detail', 'UsersController@userDetail');
	
	Route::post('user/getcurrentuser', 'UsersController@getCurrentUser');

	


	Route::post('cars/search','CarsController@search');	
	Route::post('cars/detail','CarsController@detail');
	Route::post('cars/review','CarsController@carReview');
	Route::post('cars/reviewofuser','CarsController@carReviewOfUser');
	
	Route::post('cars/calender','CarsController@getCarCalender');
	Route::post('cars/reapply-car-listing','CarsController@reapplyCarListing');
	
	Route::post('cars/delete','CarsController@deleteCar');

	Route::post('vehicletype','VehicleTypeController@index');

	Route::post('make','MakeController@index');

	Route::post('models','ModelsController@index');

	Route::post('features','CarFeaturesController@index');

	Route::post('countries','CountryController@index');

	Route::post('setting','SettingController@index');

	//Route::post('setting','SettingController@index');

	Route::post('cms/get-page','CmsController@getPage');

	Route::post('estimated-valuesetAppSessions','EstimatedValuesController@index');
	Route::post('cars/delivery-fee-list','CarsController@getDeliveryPricingList');
	Route::post('cars/get-car-photos','CarsController@getCarPhotos');

	Route::post('banks/get-bank-list', 'BanksController@index');

	//Route::post('bookings/edit-booking/{booking}','BookingController@editBookingProcess');
	//http://192.168.0.73/car_rent/public/api/cms/get-page
});



# all routes which requires accesstoken
Route::group(array('middleware' =>['accesstokenchecker'], 'namespace' => 'Api' ), function() {



     Route::post('users/logout', 'UsersController@logout');
     Route::post('rooms/add','RoomsController@addRooms');
	
	Route::post('users/update-card-information', 'UserCardController@save');
	Route::post('users/update-card-inform', 'UserCardController@inform');
	
	Route::post('booking/get-booking-price','BookingController@getBookingPrice');
	Route::post('booking','BookingController@book');
	Route::post('booking/my-rentals','BookingController@myRentals');
	Route::post('booking/my-rental-details','BookingController@myRentalDetails');
	Route::post('booking/my-car-rentals','BookingController@myCarRentals');
	Route::post('booking/my-car-rental-details','BookingController@myCarRentalDetails');
	Route::post('booking/cancel-booking','BookingController@cancelBooking');
	Route::post('booking/reject-booking','BookingController@rejectBooking');
	Route::post('booking/reject-booking-with-message', 'BookingController@rejectBookingwithMessage');

	Route::post('booking/approve-booking','BookingController@approveBooking');
	Route::post('booking/booking-message', 'BookingController@bookingMessage');


	Route::post('review/add-review','ReviewsController@addReview');

	Route::post('users/verify-licence', 'UsersController@verifyLicence');
	Route::post('users/verify-mobile', 'UsersController@verifyMobile');
	Route::post('users/verify-mobile-code', 'UsersController@verifyMobileCode');
	Route::post('users/update-profile', 'UsersController@editProfile');
	
	Route::post('users/get-card-info', 'UserCardController@getPaymentInfo');
	Route::post('users/update-deposite-account', 'EarningAccountController@save');
	Route::post('users/get-deposite-account-info', 'EarningAccountController@getDepositeAccountInfo');
	Route::post('users/notification', 'NotificationController@getNotification');
	Route::post('users/get-unread-notification-count', 'NotificationController@getUnreadNotificationCount');
	Route::post('users/read-notification', 'NotificationController@readNotification');
	Route::post('users/message', 'MessageController@getMessage');
	Route::post('users/send-message', 'MessageController@sendMessage');
	Route::post('users/read-message', 'MessageController@readMessage');
	Route::post('users/change-language', 'UsersController@changeLanguage');
	Route::post('users/change-password', 'UsersController@changePassword');
	Route::post('users/feedback', 'UsersController@feedback');

	
	Route::post('cars/update-car','CarsController@updateCar');
	Route::post('cars/delete-car-photos','CarsController@deleteCarPhotos');
	Route::post('cars/change-status','CarsController@changeStatus');
	Route::post('cars/update-availability','CarsController@updateAvailability');
	
	Route::post('cars/update-delivery-option','CarsController@updateCarDeliveryOption');
	Route::post('cars/update-mileage-option','CarsController@updateMileageOption');
	Route::post('cars/add-car-photos','CarsController@addCarPhoto');

	Route::post('cars/get-instant-booking-status','CarsController@getCardAndInstantBookingStatus');
	Route::post('cars/update-instant-booking-status','CarsController@updateInstantBookingStatus');
   Route::post('users/checkLicenceStatus', 'UsersController@checkLicenceStatus');
   Route::post('users/checkMobileStatus', 'UsersController@checkMobileStatus');

	
	
});
