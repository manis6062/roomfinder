<?php



namespace App\Http\Controllers\Api\V1;



use Illuminate\Http\Request;



use App\Http\Requests;

use App\Http\Controllers\Controller;

use App\Models\Booking;

use App\Models\Car;

use App\Models\Message;

use App\Models\User;

use App\Models\Payment;

use App\Models\Refund;

use App\Models\Setting;

use App\Models\Notification;

use App\Library\CarRentFunctions;

use App\Models\UserCardInfo; 

use DB,Lang;

require_once(app_path().'/loadomise.php'); 

class BookingController extends Controller

{

    public function getBookingPrice(Request $request){

        $input = $request->all();

        $v = \Validator::make($input,   [ 

            'user_id' => 'required|numeric|exists:users,id',

            'car_id'  => 'required|numeric|exists:cars,id,status,listed',

            'from_date' =>'required|numeric',

            'to_date' =>'required|numeric',

            'place_id' =>'required_if:delivery_option,1'

            ] );

        if ($v->fails())

        {   

            $msg = array();

            $messages = $v->errors();           

            foreach ($messages->all() as $message) {

                return \Response::json(array(  'error' => true,  'message' => $message ) );

            }  

        }  

        $car =  Car::where('id',$input['car_id'])->where('status','listed')->first();

        $input['make_id'] = $car->make->id;

        $input['model_id'] = $car->model->id;

        $input['year_made'] = $car->year_made; 

        if(isset($input['delivery_option']) and $input['delivery_option'] == '1' and isset($input['place_id'])){

            $input['proposed_pickup'] = '1'; 

            $input['booking_place_id'] = $input['place_id']; 

        }

        $booking_price_output = Booking::getBookingPrice($input);



        //dd($booking_price_output); 

        if($booking_price_output['error']){

            return \Response::json(array(  'error' => true,  'result' => $booking_price_output['message'] ) );

        }

        //$booking_price_output['result']['car_image'] = 'test';

        return \Response::json(array(  'error' => false,  'result' => $booking_price_output ) );



    }

	public function book(Request $request){

		$input = $request->all();

		$v = \Validator::make($input,   [ 

			'user_id' => 'required|numeric|exists:users,id',

			'car_id'  => 'required|numeric|exists:cars,id',

			'from_date' =>'required|numeric',

			'to_date' =>'required|numeric',

            'delivery_address' =>'required_if:delivery_option,1',           

            'message' => 'required_if:delivery_option,1'		

            ] );

        if(DB::table('cars')->where('user_id',$input['user_id'])->where('id',$input['car_id'])->first()){

            return \Response::json(array(  'error' => true,  'message' => Lang::get('booking.cannot_book_own_car') ) );

        }



        if(!DB::table('users')->select('id')

            ->where('id',$input['user_id'])

            ->where('licence_verified', 1)            

            ->first()){

            return \Response::json(array(  'error' => true,  'message' => Lang::get('booking.licence_not_verified') ) );

        }



        if(!DB::table('users')->select('id')

            ->where('id',$input['user_id'])

            ->where('mobile_verified', 1)            

            ->first()){

            return \Response::json(array(  'error' => true,  'message' => Lang::get('booking.mobile_not_verified') ) );

        }



        if($request->delivery_option){

            if(!$request->place_id){

               return \Response::json(array(  'error' => true,  'message' => Lang::get('booking.place_id_required') ) ); 

            }

        }

        if ($v->fails())

        {   

            $msg = array();

            $messages = $v->errors();           

            foreach ($messages->all() as $message) {

                return \Response::json(array(  'error' => true,  'message' => $message ) );

            }  

        }  

        if($input['to_date'] <= $input['from_date']){

            return \Response::json(array(  'error' => true,  'message' => "invalid date range" ) );

        }

        

        if($input['from_date'] < time()){

            return \Response::json(array(  'error' => true,  'message' => "you have selected past date" ) );

        }



        if(!Car::canBook($input['car_id'],$input['from_date'],$input['to_date'])){



            $date = Car::bookingMessage($input['car_id'],$input['from_date'],$input['to_date']);

            $date = date('d M, Y', strtotime($date)). ' '.date("g:ia", strtotime($date));



           return \Response::json(array(  'error' => true, 'error_type' => Lang::get('booking.car_not_available',['date'=>$date]),   'message' => Lang::get('booking.car_not_available',['date'=>$date]) ) );

        }

        $user = User::select('email','first_name','last_name','payment_info_updated', 'override_name_pic')->where('id',$input['user_id'])->first();

        if(!isset($user->payment_info_updated)){

           return \Response::json(array(  'error' => true,   'message' => Lang::get('booking.payment_account_not_set')  ) ); 

        }

        $car =  Car::where('id',$input['car_id'])->where('status','listed')->first();

        $input['make_id'] = $car->make->id;

        $input['model_id'] = $car->model->id;

        $input['year_made'] = $car->year_made; 

        if(isset($input['delivery_option']) and $input['delivery_option'] == '1' and isset($input['place_id'])){

            $input['proposed_pickup'] = '1'; 

            $input['booking_place_id'] = $input['place_id']; 



        }

        $setting = DB::table('setting')->first();

        $booking_price_output = Booking::getBookingPrice($input);

        if($booking_price_output['error']){

            return \Response::json(array(  'error' => true,  'result' => $booking_price_output['message'] ) );

        }

        if(isset($input['delivery_option']) and $input['delivery_option'] == '1' and isset($input['place_id'])){

            $booking_price_output['place_id'] = $input['place_id'];

            $booking_price_output['delivery_address'] = $input['delivery_address'];

        }

        $booking_price_output['from_date'] = date('Y-m-d H:i:s',$input['from_date']);   

        $booking_price_output['to_date'] = date('Y-m-d H:i:s',$input['to_date']);

        $booking_price_output['tax_amount'] = $booking_price_output['booking_tax'];



        $booking_price_output['status'] = 'pending';

        $booking_price_output['car_id'] = $input['car_id'];

        $booking_price_output['user_id'] = $input['user_id'];

        $booking_price_output['booking_type'] = ($car->is_instant_booking_enabled)?'Instant':'Normal';

        //dd($booking_price_output); 

        if($booking_id = Booking::store($booking_price_output)){   



            /**********************send message to the car owner*******************************/

            $msg_array['from_user'] = $input['user_id'];

            $msg_array['to_user'] = $car->user_id;

            $msg_array['message'] = @$input['message'];

            $msg_array['booking_id'] = $booking_id; 

            $msg_array['created_at'] = date('Y-m-d H:i:s'); 

            $msg_array['updated_at'] = date('Y-m-d H:i:s'); 

            $mid = Message::create($msg_array);

            /*****************************send email to car owner********************************/

           

            $car_full_name = $car->make->title_eng.' '.$car->model->title_eng.'('.$car->year_made.')';

            $owner_full_name = $car->user->first_name;

            // $user_full_name = $user->first_name;           

          if($user->override_name_pic == 'n'){
            $user_full_name = $user->first_name . ' ' . $user->last_name;
            }else{
                $setting = \App\Models\Setting::firstOrFail();
                $user_full_name = $setting->default_first_name . ' ' . $setting->default_last_name;
            }

            $lang = ($car->user->lang)?$car->user->lang:'en';



            $replace_array["owner_full_name"] = $owner_full_name;

            $replace_array["car_full_name"] = $car_full_name;

            $replace_array["user_full_name"] = $user_full_name;

            $slug = 'owner-car-booked';

            if($booking_price_output['booking_type'] == 'Instant'){

                $slug = 'intant-booking-email-to-car-owner';

            }



           

            if($replaced_content = CarRentFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){

                $search_array = array("{owner_full_name}","{car_full_name}","{user_full_name}");                

                $user = DB::table("users")->select('first_name','last_name')->where('id',$input['user_id'])->first();

               

               

                $content = $replaced_content['content']; 



                $subject = $replaced_content['subject']; 



                $email_array['to_email'] = $car->user->email;

                //$email_array['to_email'] ='es.pradeeparyal@gmail.com';

                $email_array['to_name'] = $owner_full_name;

                $email_array['subject'] = $subject;

                $email_array['message'] = $content;



                if($booking_price_output['booking_type'] != 'Instant'){

                    CarRentFunctions::sendEmail($email_array);

                }

                /*******************************send sms to car owner*************************************************/

                CarRentFunctions::SendSmsMessage(@$car->user->mobile_number,$subject,@$car->user->mobile_country_code);  

                /*******************************insert into notification table****************************************/               

                $mobile_target_id = $booking_id;

                $content_link = '#';



                $noti_params['user_id'] = $car->user->id; 

                $noti_params['mobile_target'] = 'my_car_rentals_detail';

                $noti_params['mobile_target_id'] = $booking_id;

                $noti_params['invoker_user_id'] = $input['user_id'];

                $noti_params['slug'] = $slug;

                $replace_array['car_full_name'] = $car_full_name; 

                $noti_params['replace_array'] = $replace_array; 

                $noti_id = Notification::createNotification($noti_params);

                

                /***************************************send push notification to car owner*****************************/           

                $device_tokens_ios = CarRentFunctions::getGoogleDeviceTokens($car->user->id,'ios');

                $device_tokens_andriod = CarRentFunctions::getGoogleDeviceTokens($car->user->id,'android');

                if(!empty($device_tokens_ios)){

                    $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_ios,$noti_id,'ios'); 

                }

                if(!empty($device_tokens_andriod)){

                    $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_andriod,$noti_id,'android');     

                }



                /**************************send message as chat to car owner*****************************************/

                $msg_array['message'] = $input['message'];

                $msg_array['booking_id'] = $booking_id; 

                $chat_data = array(  

                    'message' => $msg_array['message'],

                    'time' => date("H:i D d/m/Y"),

                    'receiver' => $msg_array['to_user'],

                    'sender' => $msg_array['from_user'] 

                    );                 

               

                if(trim($msg_array['message'])!="" or $msg_array['message']!=NULL){                

                    CarRentFunctions::sendChat($chat_data,$booking_id);

                }

                /****************************************send chat push notification to message receiver******************/

                $b = Booking::find($booking_id);

                $sub_target = ""; 

                $param['mobile_target'] = 'message';

                if($b->car->user_id == $msg_array['to_user']){

                    $sub_target = 'my_car_rentals_detail';

                }else{

                    $sub_target = 'my_rentals_detail'; 

                }

                $param['mobile_target'] = 'message';

                $param['mid'] = $mid->id;  

                $param['mobile_sub_target'] = $sub_target; 

                $param['booking_id'] = $booking_id;

                

                $device_tokens_ios = CarRentFunctions::getGoogleDeviceTokens($msg_array['to_user'],'ios');

                $device_tokens_andriod = CarRentFunctions::getGoogleDeviceTokens($msg_array['to_user'],'android');

                if(!empty($device_tokens_ios)){

                    $param['device_tokens'] = $device_tokens_ios;                    

                    $param['device_type'] = 'ios'; 

                    $noti_result = CarRentFunctions::createAndSendChatNotification($param); 

                }

                if(!empty($device_tokens_andriod)){

                    $param['device_tokens'] = $device_tokens_andriod;                    

                    $param['device_type'] = 'andriod'; 

                    $noti_result = CarRentFunctions::createAndSendChatNotification($param);     

                }        



            }



            $lang = \App::getLocale();

            $user = DB::table("users")->select('email','first_name','last_name' , 'override_name_pic')->where('id',$input['user_id'])->first();

            // $user_full_name = $user->first_name;

             // $user_full_name = $user->first_name;
              if($user->override_name_pic == 'n'){
            $user_full_name = $user->first_name . ' ' . $user->last_name;
            }else{
                $setting = \App\Models\Setting::firstOrFail();
                $user_full_name = $setting->default_first_name . ' ' . $setting->default_last_name;
            }

            $replace_array['user_full_name'] = $user_full_name;

            $replace_array['car_full_name'] = $car_full_name;

           

            if($replaced_content = CarRentFunctions::getEmailContentSubject(['lang' => $lang,'slug' => 'car-booking-request-received','replace_array' => $replace_array])){

               

                $content = $replaced_content['content']; 

                $subject = $replaced_content['subject'];               

                $email_array['to_email'] = $user->email;

                //$email_array['to_email'] = 'es.pradeeparyal@gmail.com';

                $email_array['to_name'] = $user_full_name;

                $email_array['subject'] = $subject;

                $email_array['message'] = $content;

                CarRentFunctions::sendEmail($email_array);

            }

            /********************************************Send SMS to Site Admin About The Booking*****************************/

            CarRentFunctions::SendSmsMessage($setting->admin_mobile_number,"User has booked a car id ".$car->id." . Please login to RCC admin and see more details",$setting->admin_country_code);  





            /*****************************for instant booking only*************************************************************/

            $booking = Booking::find($booking_id);

            if($booking->booking_type == 'Instant'){

                $res = Payment::chargeCard($booking->id); 



                if($res['error']){

                        

                    return \Response::json(array(  'error' => true, 'payment_error' => 1,   'message' => Lang::get('booking.instant_booking_payment_error')) );

                }

                $charge = $res['result'];

                

                if($charge['paid'] == true){

                    Booking::approveBooking($booking,$charge);

                }else{   

                    return \Response::json(array(  'error' => true, 'payment_error' => 1,  'message' => Lang::get('booking.instant_booking_payment_error') ) );   

                }

            }





            return \Response::json(array(  'error' => false, 'booking_id'=>$booking_id, 'message' => 'success' ) );

        }else{

        	return \Response::json(array(  'error' => true,  'message' => 'error' ) );

        }

    }



    public function myRentals(Request $request){

        $input = $request->all();       

        $v = \Validator::make($input,   [ 

            'user_id' => 'required|numeric|exists:users,id',

            'per_page' =>'numeric|required',

            'page_number' =>'numeric|required',

            ] );        

        if ($v->fails())

        {   

            $msg = array();

            $messages = $v->errors();           

            foreach ($messages->all() as $message) {

                return \Response::json(array(  'error' => true,  'message' => $message ) );

            }  

        }  

        $skip = 0;

        if($input['page_number'] > 1){



            $skip = (($input['page_number']-1) * $input['per_page']);

        }    

        $bookings = DB::table('car_bookings as cb')

        ->select('cb.id','cb.id as can_message','cb.from_date','cb.booking_type', 'cb.to_date','cb.delivery_fee as total_delivery_fee',

            'cb.delivery_distance','cb.status','mk.title_eng as make_title',

            'md.title_eng as model_title','c.year_made','u.first_name','u.last_name','c.delivery_fee',

            'ci.photo')



        ->join('cars as c','c.id','=','cb.car_id')

        ->join('users as u','u.id','=','c.user_id')

        ->join('car_make as mk','mk.id','=','c.make_id')

        ->join('car_models as md','md.id','=','c.model_id')

        ->leftjoin('car_photos as ci','cb.car_id','=','ci.car_id')

        ->where('cb.user_id',$input['user_id'])

        ->groupBy('cb.id')

        ->orderBy('cb.id','desc')

        ->skip($skip)

        ->take($input['per_page'])

        ->get();

       //dd($bookings); 



        $car_img_path = env("BASE_URL")."images/cars/thumb/"; 

        $default_car_pic =  url('images/global/cars/car_default.jpg');

        if($bookings){

            foreach($bookings as $booking){

                $mc = DB::select("SELECT count(id) as total_count from messages 

                    where booking_id = {$booking->id} and to_user = {$input['user_id']} and is_read = 0"); 

                if($mc){

                    $booking->total_unread_messages = $mc[0]->total_count; 

                }

                $booking->from_date = date("m/d/Y H:i:s",strtotime($booking->from_date));

                $booking_to = $booking->to_date;

                $booking->to_date = date("m/d/Y H:i:s",strtotime($booking->to_date));

                if($booking->photo){

                    $booking->photo = $car_img_path.$booking->photo;

                }else{

                    $booking->photo = $default_car_pic;

                }

                if(!$booking->delivery_distance){

                    $booking->delivery_distance = 0; 

                }

                $booking->can_message = Booking::canMessage($booking->status, $booking_to);

            }

            return \Response::json(array(  'error' => false,  'result' => $bookings ) );

        }else{

         return \Response::json(array(  'error' => false,  'result' => array()  ));    

     }



 }



 public function myRentalDetails(Request $request){

    $input = $request->all();

    $v = \Validator::make($input, [ 

        'user_id' => 'required|numeric|exists:users,id',

        'booking_id' => 'required|numeric|exists:car_bookings,id'

        ] );        

    if ($v->fails())

    {   

        $msg = array();

        $messages = $v->errors();           

        foreach ($messages->all() as $message) {

            return \Response::json(array(  'error' => true,  'message' => $message ) );

        }  

    }  

    $booking = DB::table('car_bookings as cb')

    ->select('cb.id', 'cb.processing_fee','cb.per_day_price','cb.id as can_message', 'cb.from_date','cb.booking_type','cb.to_date','cb.status','cb.proposed_pickup','cb.id as is_cancellable','cb.booking_place_id',

        'cb.delivery_fee as total_delivery_fee','cb.delivery_distance',

        'cb.delivery_address','cb.rental_price','cb.rental_fee','cb.tax_amount',

        'mk.title_eng as make_title','md.title_eng as model_title',

        'c.id as car_id','c.year_made','c.delivery_fee','c.address','ci.photo','c.loc_lat','c.loc_lon',

        'u.first_name','u.last_name','u.profile_pic','u.id as user_id',

                        'u.mobile_number','u.mobile_country_code', // car owner's mobile number

                        'cr.review','cr.rating_given')



    ->join('cars as c','c.id','=','cb.car_id')

    ->join('users as u','u.id','=','c.user_id')

    ->join('car_make as mk','mk.id','=','c.make_id')

    ->join('car_models as md','md.id','=','c.model_id')                   

    ->leftJoin('car_photos as ci','cb.car_id','=','ci.car_id')

    ->leftJoin('car_reviews as cr','cb.id','=','cr.booking_id')

    ->where('cb.user_id',$input['user_id'])                    

    ->where('cb.id',$input['booking_id'])

    ->groupBy('cb.id')

    ->first();

    $car_img_path = env("BASE_URL")."images/cars/thumb/"; 

    $user_img_path = env("BASE_URL")."images/users/thumb/"; 

    $default_car_pic =  url('images/global/cars/car_default.jpg');

    $default_profile_pic =   url('images/global/users/default-avatar.png');



    if($booking){

        $booking->unread_message = Message::where('booking_id',$booking->id)->where('is_read',0)->where('to_user',$input['user_id'])->count();  

        $booking->from_date = date("m/d/Y H:i:s",strtotime($booking->from_date));

        $booking_to = $booking->to_date;

        $booking->to_date = date("m/d/Y H:i:s",strtotime($booking->to_date));

        if($booking->profile_pic){

            $booking->profile_pic = $user_img_path.$booking->profile_pic;

        }else{

            $booking->profile_pic = $default_profile_pic;

        }

        if($booking->photo){

            $booking->photo = $car_img_path.$booking->photo;

        }else{

            $booking->photo = $default_car_pic;

        }

        if(!$booking->rating_given){

            $booking->rating_given = ''; 

        }

        if(!$booking->review){

            $booking->review = ''; 

        }

        if(!$booking->delivery_distance){

            $booking->delivery_distance = 0 ; 

        }

        if(!$booking->delivery_address){

            $booking->delivery_address = ''; 

        }



        if(!$booking->booking_place_id){

            $booking->booking_place_id = ''; 

        }



        $booking->total_delivery_fee = "".$booking->total_delivery_fee;

        $booking->delivery_distance = "".$booking->delivery_distance;

        $booking->rental_price = "".$booking->rental_price;

        $booking->rental_fee = "".$booking->rental_fee;

        $booking->tax_amount = "".$booking->tax_amount;

        $booking->delivery_fee = "".$booking->delivery_fee;

       



        $booking_pricing_details = Booking::getBookingPriceFromDB([

            'from_date' => strtotime($booking->from_date),

            'to_date' => strtotime($booking->to_date),

            'car_id' => $booking->car_id,

            'booking_id' => $booking->id

            ]);

        

        if($booking->status == 'expired' or $booking->status =='completed' or $booking->status == 'cancelled' or $booking->status == 'approved' or $booking->status == 'rejected'){

            $booking->is_cancellable = 0;

        }else{

            $booking->is_cancellable = 1;

        }

        $booking_pricing_details['delivery_fee'] = "".$booking_pricing_details['delivery_fee'];

        $booking_pricing_details['rental_fee_per_day'] = "".$booking_pricing_details['rental_fee_per_day'];

        $booking_pricing_details['rental_fee'] = "".$booking_pricing_details['rental_fee'];

        $booking_pricing_details['processing_fee'] = "".$booking_pricing_details['processing_fee'];

        $booking_pricing_details['price_per_day'] = "".$booking_pricing_details['price_per_day'];

        $booking_pricing_details['rental_price'] = "".$booking_pricing_details['rental_price'];

        $booking_pricing_details['subtotal'] = "".$booking_pricing_details['subtotal'];

        $booking_pricing_details['booking_total'] =  "".$booking_pricing_details['booking_total'];

        $booking_pricing_details['booking_tax'] = "".$booking_pricing_details['booking_tax']; 

        $booking->can_message = Booking::canMessage($booking->status, $booking_to);

        return \Response::json(array(  'error' => false,  'result' => $booking,'booking_pricing_details' => $booking_pricing_details ) );

    }else{

     return \Response::json(array(  'error' => true, 'message' =>Lang::get('messages.resultnotfound') ) );    

    }



}





public function myCarRentals(Request $request){

    $input = $request->all();

    $v = \Validator::make($input,   [ 

        'user_id' => 'required|numeric|exists:users,id',

        'per_page' => 'required|numeric',

        'page_number' => 'required|numeric'

        ] );        

    if ($v->fails())

    {   

        $msg = array();

        $messages = $v->errors();           

        foreach ($messages->all() as $message) {

            return \Response::json(array(  'error' => true,  'message' => $message ) );

        }  

    }  

    $skip = 0;

    if($input['page_number'] > 1){

        

        $skip = (($input['page_number']-1) * $input['per_page']);

    }    

    $bookings = DB::table('car_bookings as cb')

    ->select('cb.id','cb.id as can_message','cb.from_date','cb.to_date','cb.booking_type','cb.status','cb.delivery_fee as total_delivery_fee',

         'cb.delivery_distance',

        'mk.title_eng as make_title',

        'md.title_eng as model_title','c.year_made','c.delivery_fee','u.first_name','u.last_name',

        'u.profile_pic')



    ->join('cars as c','c.id','=','cb.car_id')

    ->join('users as u','u.id','=','cb.user_id')

    ->join('car_make as mk','mk.id','=','c.make_id')

    ->join('car_models as md','md.id','=','c.model_id')                    

    ->where('c.user_id',$input['user_id'])

    ->groupBy('cb.id')

    ->orderBy('cb.id','desc')

    ->skip($skip)

    ->take($input['per_page'])

    ->get();

    $user_img_path = env("BASE_URL")."images/users/thumb/"; 

    $default_profile_pic =   url('images/global/users/default-avatar.png');

    if($bookings){

        foreach($bookings as $booking){

         $mc = DB::select("SELECT count(id) as total_count from messages 

                where booking_id = {$booking->id} and to_user = {$input['user_id']} and is_read = 0"); 

            if($mc){

                $booking->total_unread_messages = $mc[0]->total_count; 

            }

            $booking->from_date = date("m/d/Y H:i:s",strtotime($booking->from_date));

            $booking_to = $booking->to_date;

            $booking->to_date = date("m/d/Y H:i:s",strtotime($booking->to_date));

            if($booking->profile_pic){

                $booking->profile_pic = $user_img_path.$booking->profile_pic;

            }else{

                $booking->profile_pic = $default_profile_pic;

            }

            if(!$booking->delivery_distance){

                $booking->delivery_distance = 0; 

            }

            $booking->can_message = Booking::canMessage($booking->status, $booking_to);

        }

        return \Response::json(array(  'error' => false,  'result' => $bookings ) );

    }else{

     return \Response::json(array(  'error' => false,  'result' => array()  ));    

 }



}



public function myCarRentalDetails(Request $request){

    $input = $request->all();

    $v = \Validator::make($input,   [ 

        'user_id' => 'required|numeric|exists:users,id',

        'booking_id' => 'required|numeric|exists:car_bookings,id'

        ] );        

    if ($v->fails())

    {   

        $msg = array();

        $messages = $v->errors();           

        foreach ($messages->all() as $message) {

            return \Response::json(array(  'error' => true,  'message' => $message ) );

        }  

    }  

    $booking = DB::table('car_bookings as cb')

    ->select('cb.id', 'cb.id as can_message', 'cb.per_day_price','cb.from_date','cb.to_date','cb.booking_type','cb.status','cb.proposed_pickup','cb.rcc_charge_to_owner','cb.booking_place_id',

        'cb.delivery_fee as total_delivery_fee','cb.delivery_distance','cb.id as is_cancellable',

        'cb.delivery_address','cb.rental_price','cb.rental_fee', 'cb.rental_fee as rental_commission','cb.tax_amount','cb.status as total_receiving',

        'mk.title_eng as make_title','md.title_eng as model_title',

        'c.id as car_id','c.year_made','c.address','c.delivery_fee','c.loc_lat','c.loc_lon',

        'u.profile_pic','u.first_name','u.last_name','u.id as user_id',

        'u.mobile_number','u.mobile_country_code', 

        'cr.review','cr.rating_given')



    ->join('cars as c','c.id','=','cb.car_id')

    ->join('users as u','u.id','=','cb.user_id')

    ->join('car_make as mk','mk.id','=','c.make_id')

    ->join('car_models as md','md.id','=','c.model_id')                   

    ->join('car_photos as ci','cb.car_id','=','ci.car_id')

    ->leftJoin('car_reviews as cr','cb.id','=','cr.booking_id')                                   

    ->where('cb.id',$input['booking_id'])

    ->groupBy('cb.id')

    ->first();

    $user_img_path = env("BASE_URL")."images/users/thumb/"; 

    $default_profile_pic =   url('images/global/users/default-avatar.png');

    $setting = Setting::first();

    if($booking){

        

        $booking->rental_commission = "".$booking->rcc_charge_to_owner;  



        $booking->unread_message = Message::where('booking_id',$booking->id)->where('is_read',0)->where('to_user',$input['user_id'])->count();  

        $booking->from_date = date("m/d/Y H:i:s",strtotime($booking->from_date));

        $booking_to = $booking->to_date;

        $booking->to_date = date("m/d/Y H:i:s",strtotime($booking->to_date));

        if($booking->rcc_charge_to_owner){

            $booking->rcc_charge_to_owner = "".$booking->rcc_charge_to_owner; 

        }

        

        if($booking->profile_pic){

            $booking->profile_pic = $user_img_path.$booking->profile_pic;

        }else{

            $booking->profile_pic = $default_profile_pic;

        }

        if(!$booking->rating_given){

            $booking->rating_given = ''; 

        }

        if(!$booking->review){

            $booking->review = ''; 

        }

        if(!$booking->delivery_distance){

            $booking->delivery_distance = "0" ; 

        }

        if(!$booking->delivery_address){

            $booking->delivery_address = ''; 

        } 

        if(!$booking->booking_place_id){

            $booking->booking_place_id = ''; 

        } 

        if($booking->status == 'expired' or $booking->status =='completed' or $booking->status == 'cancelled' or $booking->status == 'approved' or $booking->status == 'rejected'){

            $booking->is_cancellable = 0;

        }else{

            $booking->is_cancellable = 1;

        }



        $booking->can_message = Booking::canMessage($booking->status, $booking_to);

       

        $booking->total_receiving = ( $booking->rental_price + $booking->total_delivery_fee ) - $booking->rental_commission; 

        $booking->total_receiving = "".$booking->total_receiving;

        $booking->rental_price = "".$booking->rental_price; 

        $booking->rental_commission = "".$booking->rental_commission;

        $booking->rental_fee = "".$booking->rental_fee;

        $booking->tax_amount = "".$booking->tax_amount;

        $booking->delivery_fee = "".$booking->delivery_fee;

        $booking->total_delivery_fee = "".$booking->total_delivery_fee;

        $booking->delivery_distance = "".$booking->delivery_distance;

        //$booking = null;    

        return \Response::json(array(  'error' => false,  'result' => $booking ) );

    }else{

     return \Response::json(array(  'error' => true, 'message' =>Lang::get('messages.resultnotfound') ) );    

 }



}



public function cancelBooking(Request $request){

    $input = $request->all();

    $current_date = date('Y-m-d H:i:s');

   

    $v = \Validator::make($input,   [ 

        'user_id' => 'required|numeric|exists:users,id',

        'booking_id' => 'required|numeric|exists:car_bookings,id,user_id,'.$input['user_id'],

        'cancellation_reason' => 'required',

        ] );        

    if ($v->fails())

    {   

        $msg = array();

        $messages = $v->errors();           

        foreach ($messages->all() as $message) {

            return \Response::json(array(  'error' => true,  'message' => $message ) );

        }  

    }  



    $SQL = "SELECT id,car_id,from_date,status FROM car_bookings WHERE id = ? and status = 'pending' LIMIT 1";        

    $res = DB::select($SQL,array($input['booking_id']));

    if(!$res){



        return \Response::json(array(  'error' => true, 'message' =>Lang::get('booking.cannot_cancel') ) );    

    }

       

    

    $car = Car::find($res[0]->car_id);

    //insert record into refunded payment table for refunding the amount if the booking has been paid and later cancelled

    $payment = Payment::select('id')->where('booking_id',$input['booking_id'])->first(); 

    if($payment){

        $res = Refund::createRefund($input['booking_id']); 

        if($res['error']){

             return \Response::json(array(  'error' => true, 'message' =>$res['message'] ) );  

        }

    }

    DB::update("UPDATE car_bookings SET status = 'cancelled', 

        cancelled_date = '$current_date',cancellation_reason = ? WHERE id = ?",

        array($input['cancellation_reason'],$input['booking_id']));



    /*******************************send email to car owner about the cancellation*******************************************/

    $owner_full_name = $car->user->first_name;

    $user = DB::table("users")->select('first_name','last_name' , 'override_name_pic')->where('id',$input['user_id'])->first();


 // $user_full_name = $user->first_name;
              if($user->override_name_pic == 'n'){
            $user_full_name = $user->first_name . ' ' .$user->last_name;
            }else{
                $setting = \App\Models\Setting::firstOrFail();
                $user_full_name = $setting->default_first_name . ' ' . $setting->default_last_name;
            }

    $car_full_name = $car->make->title_eng.' '.$car->model->title_eng.'('.$car->year_made.')';

   

    $lang = ($car->user->lang)?$car->user->lang:'en';

    $replace_array["owner_full_name"] = $owner_full_name;

    $replace_array["car_full_name"] = $car_full_name;

    $replace_array["user_full_name"] = $user_full_name;

    $replace_array["cancellation_reason"] = $request->cancellation_reason;

    $slug = 'email-to-car-owner-when-user-cancels-the-booking';



    //if($template = CarRentFunctions::getEmailTemplate($slug)){

    if($replaced_content = CarRentFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){

        $search_array = array("{owner_full_name}","{car_full_name}","{user_full_name}","{cancellation_reason}");

        $content = $replaced_content['content']; 

        $subject = $replaced_content['subject'];

        $email_array['to_email'] = $car->user->email;

        //$email_array['to_email'] = 'es.pradeeparyal@gmail.com';

        $email_array['to_name'] = $user_full_name;

        $email_array['subject'] = $subject;

        $email_array['message'] = $content;

        CarRentFunctions::sendEmail($email_array); 



        /******************Insert and send notification about the cancellation to car owner**************************/



        $noti_params['user_id'] = $car->user->id; 

        $noti_params['mobile_target'] = 'my_car_rentals_detail';

        $noti_params['mobile_target_id'] = $input['booking_id'];

        $noti_params['invoker_user_id'] = $input['user_id'];

        $noti_params['slug'] = $slug;

        $replace_array['car_full_name'] = $car_full_name; 

        $replace_array['user_full_name'] = $user_full_name; 

        $replace_array['cancel_reason'] = $request->cancellation_reason; 

        $noti_params['replace_array'] = $replace_array; 

        $noti_id = Notification::createNotification($noti_params);





        //send push notification to car owner           

        $device_tokens_ios = CarRentFunctions::getGoogleDeviceTokens($car->user_id,'ios');

        $device_tokens_andriod = CarRentFunctions::getGoogleDeviceTokens($car->user_id,'android');

        if(count($device_tokens_ios) > 0){

            $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_ios,$noti_id,'ios'); 

        }

        if(count($device_tokens_andriod) > 0){

            $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_andriod,$noti_id,'android');     

        }  



        //send email to admin about the cancellation

        $setting = Setting::first();

        $email_array['to_email'] = $setting->site_email;

        $email_array['to_name'] = 'Admin';

        $email_array['subject'] = "Booking ID: ".$input['booking_id']." has been cancelled.";

        $email_array['message'] = "Dear Admin, <br> Booking ID ".$input['booking_id']." has been cancelled by user. You can view the details by login to RCC control panel.";

        CarRentFunctions::sendEmail($email_array);

           

    }



    return \Response::json(array(  'error' => false, 'message' =>Lang::get('messages.success') ) ); 



}



public function rejectBooking(Request $request){

    $input = $request->all();

    $current_date = date('Y-m-d H:i:s');

    $v = \Validator::make($input,   [ 

        'user_id' => 'required|numeric|exists:users,id',

        'booking_id' => 'required|numeric|exists:car_bookings,id'

        ] );        

    if ($v->fails())

    {   

        $msg = array();

        $messages = $v->errors();           

        foreach ($messages->all() as $message) {

            return \Response::json(array(  'error' => true,  'message' => $message ) );

        }  

    }   

    $SQL = "SELECT id,car_id,user_id,rental_price,rental_fee,tax_amount FROM car_bookings WHERE id = ? and status = 'pending' and 

    from_date >= '$current_date' LIMIT 1";        

    $res = DB::select($SQL,array($input['booking_id']));

    if(!$res){

        return \Response::json(array(  'error' => true, 'message' =>Lang::get('booking.cannot_reject') ) );    

    }

    //insert record into refunded payment table for refunding the amount if the booking has been paid and later rejected

    $payment = Payment::select('id','omise_charge_id')->where('booking_id',$input['booking_id'])->first();  

    try{

        if($payment){

            $result = Refund::createRefund($res[0]->id,true); 

            if($result['error']){

                 return \Response::json(array(  'error' => true, 'message' =>$result['message'] ) );  

            }

        }

    }catch(\Exception $e){

        return \Response::json(array(  'error' => true, 'message' => $e->getMessage() ) );  

    }

    



    DB::update("UPDATE car_bookings SET status = 'rejected', rejected_date = '$current_date' WHERE id = ?",array($input['booking_id']));

    $car = Car::find($res[0]->car_id);

    $car->total_booking_rejected = $car->total_booking_rejected + 1;

    $car->save();

    $user = User::find($res[0]->user_id);

    /***************************************send email to user about the rejection************************************************/

   

    $car_full_name = $car->make->title_eng.' '.$car->model->title_eng.'('.$car->year_made.')';

     // $user_full_name = $user->first_name;
              if($user->override_name_pic == 'n'){
            $user_full_name = $user->first_name . ' ' . $user->last_name;
            }else{
                $setting = \App\Models\Setting::firstOrFail();
                $user_full_name = $setting->default_first_name . ' ' . $setting->default_last_name;
            }

    $replace_array["car_full_name"] = $car_full_name;

    $replace_array["user_full_name"] = $user_full_name;

    $slug = 'email-to-car-user-when-owner-rejects-the-booking';

    $lang = ($user->lang)?$car->user->lang:'en';



    if($replaced_content = CarRentFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){   

       

        $content = $replaced_content['content']; 

        $subject = $replaced_content['subject']; 

        

        

        $email_array['to_email'] = $user->email;

        $email_array['to_name'] = $user_full_name;

        $email_array['subject'] = $subject;

        $email_array['message'] = $content;

        CarRentFunctions::sendEmail($email_array);

        /***********************************insert into notification table********************************************/        



        $noti_params['user_id'] = $user->id; 

        $noti_params['mobile_target'] = 'my_car_rentals_detail';

        $noti_params['mobile_target_id'] = $input['booking_id'];

        $noti_params['invoker_user_id'] =  $car->user->id;

        $noti_params['slug'] = $slug;

        $replace_array['car_full_name'] = $car_full_name; 

        $replace_array['user_full_name'] = $user_full_name; 

        $noti_params['replace_array'] = $replace_array; 

        $noti_id = Notification::createNotification($noti_params);       



        //send push notification to car owner           

        $device_tokens_ios = CarRentFunctions::getGoogleDeviceTokens($user->id,'ios');

        //dd($device_tokens_ios); 

        $device_tokens_andriod = CarRentFunctions::getGoogleDeviceTokens($user->id,'android');

        if(count($device_tokens_ios) > 0){

            $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_ios,$noti_id,'ios'); 

        }

        if(count($device_tokens_andriod) > 0){

            $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_andriod,$noti_id,'android');     

        }  

    }

    

    /******************************************send sms to site admin about the rejection************************************/

    $setting = Setting::first();  

    $sms = "Car booking id ".$input['booking_id']." has been rejected by car owner. Please review"; 

    CarRentFunctions::SendSmsMessage($setting->admin_mobile_number,$sms,$setting->admin_country_code);  

    //CarRentFunctions::SendSmsMessage(9851221698,$sms,'+977');  

    //send email to site admin about the rejection

    $email_array['to_email'] = $setting->site_email;

    //$email_array['to_email'] = 'es.pradeeparyal@gmail.com'; 

    $email_array['to_name'] = 'RCC Admin'; 

    $email_array['subject'] = "Car booking id ".$input['booking_id']." has been rejected by car owner"; 

    $email_array['message'] = "Hi Admin, <br> Car booking id ".$input['booking_id']." has been rejected by car owner. Please login to admin control panel to know more."; 

    CarRentFunctions::sendEmail($email_array);



    return \Response::json(array(  'error' => false, 'message' =>Lang::get('messages.success') ) ); 



}

public function rejectBookingwithMessage(Request $request){

    $input = $request->all();

    $current_date = date('Y-m-d H:i:s');

    $v = \Validator::make($input,   [ 

        'user_id' => 'required|numeric|exists:users,id',

        'booking_id' => 'required|numeric|exists:car_bookings,id',

        'message' => 'required'

        ] );        

    if ($v->fails())

    {   

        $msg = array();

        $messages = $v->errors();           

        foreach ($messages->all() as $message) {

            return \Response::json(array(  'error' => true,  'message' => $message ) );

        }  

    }   

    $SQL = "SELECT id,car_id,user_id,rental_price,rental_fee,tax_amount FROM car_bookings WHERE id = ? and status = 'pending' and 

    from_date >= '$current_date' LIMIT 1";        

    $res = DB::select($SQL,array($input['booking_id']));

    if(!$res){

        return \Response::json(array(  'error' => true, 'message' =>Lang::get('booking.cannot_reject') ) );    

    }

    //insert record into refunded payment table for refunding the amount if the booking has been paid and later rejected

    $payment = Payment::select('id','omise_charge_id')->where('booking_id',$input['booking_id'])->first();  

    try{

        if($payment){

            $result = Refund::createRefund($res[0]->id,true); 

            if($result['error']){

                 return \Response::json(array(  'error' => true, 'message' =>$result['message'] ) );  

            }

        }

    }catch(\Exception $e){

        return \Response::json(array(  'error' => true, 'message' => $e->getMessage() ) );  

    }

    



    DB::update("UPDATE car_bookings SET status = 'rejected', rejected_date = '$current_date' , rejection_reason = '$request->message' WHERE id = ?",array($input['booking_id']));

    $car = Car::find($res[0]->car_id);

    $car->total_booking_rejected = $car->total_booking_rejected + 1;

    $car->save();

    $user = User::find($res[0]->user_id);

    /***************************************send email to user about the rejection************************************************/

   

    $car_full_name = $car->make->title_eng.' '.$car->model->title_eng.'('.$car->year_made.')';

    // $user_full_name = $user->first_name;
              if($user->override_name_pic == 'n'){
            $user_full_name = $user->first_name . ' ' . $user->last_name;
            }else{
                $setting = \App\Models\Setting::firstOrFail();
                $user_full_name = $setting->default_first_name . ' ' . $setting->default_last_name;
            }

    $replace_array["car_full_name"] = $car_full_name;

    $replace_array["user_full_name"] = $user_full_name;

    $slug = 'email-to-car-user-when-owner-rejects-the-booking';

    $lang = ($user->lang)?$car->user->lang:'en';



    if($replaced_content = CarRentFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){   

       

        $content = $replaced_content['content']; 

        $subject = $replaced_content['subject']; 

        

        

        $email_array['to_email'] = $user->email;

        $email_array['to_name'] = $user_full_name;

        $email_array['subject'] = $subject;

        $email_array['message'] = $content;

        CarRentFunctions::sendEmail($email_array);

        /***********************************insert into notification table********************************************/        



        $noti_params['user_id'] = $user->id; 

        $noti_params['mobile_target'] = 'my_car_rentals_detail';

        $noti_params['mobile_target_id'] = $input['booking_id'];

        $noti_params['invoker_user_id'] =  $car->user->id;

        $noti_params['slug'] = $slug;

        $replace_array['car_full_name'] = $car_full_name; 

        $replace_array['user_full_name'] = $user_full_name; 

        $noti_params['replace_array'] = $replace_array; 

        $noti_id = Notification::createNotification($noti_params);       



        //send push notification to car owner           

        $device_tokens_ios = CarRentFunctions::getGoogleDeviceTokens($user->id,'ios');

        //dd($device_tokens_ios); 

        $device_tokens_andriod = CarRentFunctions::getGoogleDeviceTokens($user->id,'android');

        if(count($device_tokens_ios) > 0){

            $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_ios,$noti_id,'ios'); 

        }

        if(count($device_tokens_andriod) > 0){

            $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_andriod,$noti_id,'android');     

        }  

    }

    

    /******************************************send sms to site admin about the rejection************************************/

    $setting = Setting::first();  

    $sms = "Car booking id ".$input['booking_id']." has been rejected by car owner. Please review"; 

    CarRentFunctions::SendSmsMessage($setting->admin_mobile_number,$sms,$setting->admin_country_code);  

    //CarRentFunctions::SendSmsMessage(9851221698,$sms,'+977');  

    //send email to site admin about the rejection

    $email_array['to_email'] = $setting->site_email;

    //$email_array['to_email'] = 'es.pradeeparyal@gmail.com'; 

    $email_array['to_name'] = 'RCC Admin'; 

    $email_array['subject'] = "Car booking id ".$input['booking_id']." has been rejected by car owner"; 

    $email_array['message'] = "Hi Admin, <br> Car booking id ".$input['booking_id']." has been rejected by car owner. Please login to admin control panel to know more."; 

    CarRentFunctions::sendEmail($email_array);



    return \Response::json(array(  'error' => false, 'message' =>Lang::get('messages.success') ) ); 



}



public function approveBooking(Request $request){

    $input = $request->all();

    $current_date = date('Y-m-d H:i:s');

    $v = \Validator::make($input,   [ 

        'user_id' => 'required|numeric|exists:users,id',

        'booking_id' => 'required|numeric|exists:car_bookings,id'

        ] );        

    if ($v->fails())

    {   

        $msg = array();

        $messages = $v->errors();           

        foreach ($messages->all() as $message) {

            return \Response::json(array(  'error' => true,  'message' => $message ) );

        }  

    }   

    $booking = Booking::where('id',$input['booking_id'])->where('status','pending')->where('from_date',">=",$current_date)->first(); 

    if(!$booking){

        return \Response::json(array(  'error' => true, 'message' =>Lang::get('booking.cannot_approve') ) );    

    }   



    $car = Car::find($booking->car_id);

    $user = User::find($booking->user_id);   

       



    $res = Payment::chargeCard($booking->id); 



    if($res['error']){



        return \Response::json(array(  'error' => true, 'message' =>Lang::get('booking.payment_error_charge') ) );

    }

    $charge = $res['result'];

    

    if($charge['paid'] == true){

        Booking::approveBooking($booking,$charge);

        return \Response::json(array(  'error' => false,  'message' => 'success' ) );



    }else{

        return \Response::json(array(  'error' => true, 'message' =>Lang::get('booking.payment_error') ) ); 

    }

        



        

        

    }



    public function bookingMessage(Request $request){

    $input = $request->all();

   // dd($input);

    $v = \Validator::make($input,   [ 

        'car_id' => 'required|numeric|exists:cars,id',

        'from_date' => 'required',

        'to_date' => 'required'

        ]);        

    if ($v->fails())

    {   

        $msg = array();

        $messages = $v->errors();           

        foreach ($messages->all() as $message) {

            return \Response::json(array(  'error' => true,  'message' => $message ) );

        }  

    }  

    //dd($input);

    $booked_dates = Car::getBookedDates($input['car_id']); 

    $from_date = date('Y-m-d H:i:s',$input['from_date']);

    $to_date = date('Y-m-d H:i:s',$input['to_date']);

   

  // dd($booked_dates, $from_date, $to_date);

    //dd($car_id);

    $date = null;

    foreach ($booked_dates as $row) {

      if($row >=$from_date  && $row <=$to_date ){

        //return $row;



        $date = date('d M, Y', strtotime($row)). ' '.date("g:ia", strtotime($row));

        break;

         

      }

    }

    $array['has_message'] = false;

     $array['message'] = null;

    if($date){ 

        $array['has_message'] = true; 

         $array['message'] = Lang::get('booking.car_not_available',['date'=>$date]) ;

    }

    $array['error'] = false;

   

    return \Response::json($array); 

    }

    

}

