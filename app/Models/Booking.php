<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;
use App\Library\CarRentFunctions;
use DB;
use App\Models\Payment;
use App\Models\Car;
use App\Models\User;
use App\Models\Notification;



class Booking extends Model
{
    protected $table = 'car_bookings';
    protected $fillable = ['processing_fee','car_id','user_id','from_date','to_date','rental_price','rental_fee','delivery_fee',
    	'proposed_pickup','delivery_address','status','tax_amount','rejected_date',
        'cancelled_date','cancellation_reason','booking_place_id','insurance_policy_id','delivery_distance','approved_date','rcc_charge_to_owner','booking_type','per_day_price'
    ];
    public function car(){
        return $this->belongsTo('App\Models\Car','car_id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }
    public function review(){
        return $this->hasOne('App\Models\Review','booking_id');
    }
    public function payment(){
        return $this->hasOne('App\Models\Payment','booking_id');
    }
    public function owners_payment(){
        return $this->hasOne('App\Models\OwnersPayment','booking_id');
    }
    public function message(){
        return $this->hasMany('App\Models\Message','booking_id','id');
    }

     public function penalty(){
        return $this->hasMany('App\Models\Penalty','booking_id','id');
    }

    public static function getTotalBookingsByStatus($user_id){
        $total_cancelled = Booking::whereIn('status',['cancelled'])->where('user_id',$user_id)->count();
        $car_ids = Car::where('user_id',$user_id)->pluck('id');
        $total_rejected = Booking::whereIn('status',['rejected'])->whereIn('car_id',$car_ids)->count();
        $total_expired = Booking::whereIn('status',['expired'])->whereIn('car_id',$car_ids)->count();
        $output['total_cancelled'] = $total_cancelled;
        $output['total_rejected'] = $total_rejected;
        $output['total_expired'] = $total_expired;
        return $output; 
    }



    public static function approveBooking($booking_obj,$charge){
        //dd('here');
        $car = Car::find($booking_obj->car_id);
        $user = User::find($booking_obj->user_id);   
        $ud = date("Y-m-d H:i:s"); 
        Payment::create(array(
            'booking_id' => $booking_obj->id,
            'omise_charge_id' => $charge['id']
            ));
        $ud = date("Y-m-d H:i:s"); 
        /***************************update the booking table to status approved********************************************/
        DB::update("UPDATE car_bookings SET status = 'approved',approved_date = '$ud' WHERE id = ?",array($booking_obj->id));
        /********************************send email to user about the approval of booking**********************************/
        $sms = "Car Owner Has Approved Booking ID: ".$booking_obj->id.'. Please review accordingly'; 

        $slug = 'email-to-car-user-when-owner-approves-the-booking';
        if($booking_obj->booking_type == 'Instant'){
            $slug = 'email-to-car-user-when-auto-approve';
            $sms = "Instant Booking ID: ".$booking_obj->id.' has been auto approved. Please review accordingly'; 
        }
        //dd($)
        $car_full_name = '';    
        

          if($user->override_name_pic == 'n'){
           $user_full_name = $user->first_name.' '.$user->last_name; 
            }else{
                $setting = \App\Models\Setting::firstOrFail();
                $user_full_name = $setting->default_first_name;
            }


        $car_full_name = $car->make->title_eng.' '.$car->model->title_eng.'('.$car->year_made.')';

          if($car->user->override_name_pic == 'n'){
            $owner_full_name = $car->user->first_name . ' ' .  $car->user->last_name;
            }else{
                $setting = \App\Models\Setting::firstOrFail();
                $owner_full_name = $setting->default_first_name . ' ' . $setting->default_last_name;
            }




        $lang = ($user->lang)?$user->lang:'en';
        //$replace_array["owner_full_name"] = $owner_full_name;
        $replace_array["car_full_name"] = $car_full_name;
        $replace_array["user_full_name"] = $user_full_name;
       

       
        if($replaced_content = CarRentFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){
            $search_array = array("{car_full_name}","{user_full_name}");
            $content = $replaced_content['content']; 
            $subject =  $replaced_content['subject'];
           
            $email_array['to_email'] = $user->email;
           // $email_array['to_email'] = 'es.pradeeparyal@gmail.com';

            $email_array['to_name'] = $user_full_name;
            $email_array['subject'] = $subject;
            $email_array['message'] = $content;
            CarRentFunctions::sendEmail($email_array);

            /*****************************************insert into notification table*********************************************/
          
            $noti_params['user_id'] = $user->id; 
            $noti_params['mobile_target'] = 'my_rentals_detail';
            $noti_params['mobile_target_id'] = $booking_obj->id;
            $noti_params['invoker_user_id'] = $car->user->id;
            $noti_params['slug'] = $slug;
            $replace_array['car_full_name'] = $car_full_name; 
            $replace_array['user_full_name'] = $user_full_name; 
            $noti_params['replace_array'] = $replace_array; 
            $noti_id = Notification::createNotification($noti_params);           
           
            /*****************************************send push notification to car owner*****************************************/           
            $device_tokens_ios = CarRentFunctions::getGoogleDeviceTokens($user->id,'ios');
            $device_tokens_andriod = CarRentFunctions::getGoogleDeviceTokens($user->id,'android');
            if(count($device_tokens_ios) > 0){
                $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_ios,$noti_id,'ios'); 
            }
            if(count($device_tokens_andriod) > 0){
                $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_andriod,$noti_id,'android');     
            }  

        }

         
        $slug = 'approved-email-for-car-owner';
        if($booking_obj->booking_type == 'Instant'){
             $slug = 'intant-booking-email-to-car-owner';
        }
        $replace_array["car_full_name"] = $car_full_name;
        $replace_array["owner_full_name"] = $owner_full_name;
        if($replaced_content = CarRentFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){
           
            $content = $replaced_content['content']; 
            $subject =  $replaced_content['subject'];
            $email_array = [];
            $email_array['to_email'] = $car->user->email;
           // $email_array['to_email'] = 'es.bijan.gopali@gmail.com';
            $email_array['to_name'] = $owner_full_name;
            $email_array['subject'] = $subject;
            $email_array['message'] = $content;
            CarRentFunctions::sendEmail($email_array);
        }

        /**************************************************************send booking receipt to booking user*******************************/
        $slug = 'booking-receipt';

        $booking_detail = Booking::getBookingPrice(['from_date' => strtotime($booking_obj->from_date),'to_date' => strtotime($booking_obj->to_date),'car_id' => $booking_obj->car_id]);
        $rental_fee_text = "(".$booking_detail['no_of_days']."days X ".env('CURRENCY_CODE')."".$booking_detail['price_per_day']." = ".env('CURRENCY_CODE').$booking_obj->rental_price.")"; 
        $insurance_fee_text = "(".$booking_detail['total_insu_days']."days X ".env('CURRENCY_CODE').$booking_detail['rental_fee_per_day']." = ".env('CURRENCY_CODE').$booking_obj->rental_fee.")"; 
        $delivery_fee_text = ($booking_obj->delivery_fee)?$booking_obj->delivery_fee:0; 
        $processing_charge_text = ($booking_obj->processing_fee)?$booking_obj->processing_fee:0; 
        //$replace_array = array($booking_obj->id,$car_full_name,$user_full_name,$booking_obj->from_date,$booking_obj->to_date,$rental_fee_text,$insurance_fee_text,env('CURRENCY_CODE').$delivery_fee_text,env('CURRENCY_CODE').$processing_charge_text,env('CURRENCY_CODE').$booking_obj->tax_amount,env('CURRENCY_CODE').$booking_detail['subtotal'],env('CURRENCY_CODE').$booking_detail['booking_total']);
        $replace_array["booking_id"] = $booking_obj->id;
        $replace_array["car_full_name"] = $car_full_name;
        $replace_array["user_full_name"] = $user_full_name;
        $replace_array["booking_from"] = $booking_obj->from_date;
        $replace_array["booking_to"] = $booking_obj->to_date;
        $replace_array["rental_fee"] = $rental_fee_text;
        $replace_array["insurance_fee"] = $insurance_fee_text;
        $replace_array["delivery_fee"] = env('CURRENCY_CODE').$delivery_fee_text;
        $replace_array["processing_charge"] = env('CURRENCY_CODE').$processing_charge_text;
        $replace_array["tax"] = env('CURRENCY_CODE').$booking_obj->tax_amount;
        $replace_array["sub_total"] = env('CURRENCY_CODE').$booking_detail['subtotal'];
        $replace_array["total"] = env('CURRENCY_CODE').$booking_detail['booking_total'];
        //if($template = CarRentFunctions::getEmailTemplate($slug)){
        if($replaced_content = CarRentFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){            
            $content = $replaced_content['content']; 
            $subject = $replaced_content['subject']; 
                       
            $email_array['to_email'] = $user->email;
            //$email_array['to_email'] = 'es.pradeeparyal@gmail.com'; 
            $email_array['to_cc_email'] = 'es.bijan.gopali@gmail.com';
            $email_array['to_name'] = $user_full_name;
            $email_array['subject'] = $subject;
            $email_array['message'] = $content;
            CarRentFunctions::sendEmail($email_array);                
        }  

        //send SMS to site admin to notify the approval of the car booking      
        $setting = Setting::first();  
        
        CarRentFunctions::SendSmsMessage($setting->admin_mobile_number,$sms,$setting->admin_country_code);  
    }
   

    public static function store($data){
        $setting = Setting::select('processing_fee','insurance_percent')->first(); 
        
    	$booking =  self::create([
    		'car_id' 						=> 	$data['car_id'],
    		'user_id' 						=> 	$data['user_id'],
    		'from_date'						=> 	$data['from_date'],
    		'to_date'						=>	$data['to_date'],
    		'rental_price'					=>	$data['rental_price'],
			'rental_fee'					=>	$data['rental_fee'],
            'delivery_fee'                  =>  $data['delivery_fee'],
            'delivery_address'              =>  @$data['delivery_address'],
            'booking_place_id'              =>  @$data['place_id'],
            'delivery_distance'             =>  @$data['delivery_distance'],
			'proposed_pickup'				=>	$data['proposed_pickup'],	
            'tax_amount'                    =>  $data['tax_amount'],   		
    		'status'						=>	$data['status'],
            'rcc_charge_to_owner'           =>  $data['rental_price'] * ($setting->insurance_percent/100),
            'processing_fee'                =>  $setting->processing_fee,
            'booking_type'                  =>  $data['booking_type'],
            'per_day_price'                 =>  $data['price_per_day']
    	]);
        
        return $booking->id; 
    }

    public static function getPaymentDetailsByID($booking_id){
        $booking = self::find($booking_id);
        $setting = setting::first(); 

        $payment_array['rental_price'] = $booking->rental_price;
       
        $payment_array['delivery_fee'] = $booking->delivery_fee;

        $payment_array['rcc_charge_to_owner'] = $booking->rcc_charge_to_owner; 
        
        $payment_array['payable_amount'] = ( $payment_array['rental_price'] + $payment_array['delivery_fee'] ) - $payment_array['rcc_charge_to_owner']; 

        $payment_array['payment_amount'] = round($payment_array['payable_amount'],2); 
       
        return $payment_array; 
    }

    public static function getBookingPrice($input){

        //dd($input); 
        $car_img_path = env("BASE_URL")."images/cars/thumb/"; 
        $default_car_pic =  url('images/global/cars/car_default.jpg');
        $car = Car::find($input['car_id']); 

        $ret_val['no_of_days'] = CarRentFunctions::getTotalDays($input['from_date'],$input['to_date']);

        $from_time = $input['from_date']; 
        $to_time = $input['to_date']; 
        $no_of_days = $ret_val['no_of_days']; 
      /*  $from_hour = date('H',$from_time); 
        $to_hour = date('H',$to_time); 
        $total_insu_days = 0;
        if($from_hour < 16){
            $total_insu_days++; 
        } 
        if($to_hour >= 16){
            $total_insu_days++; 
        } 
        if($no_of_days > 1 ){
            $total_insu_days = $total_insu_days + $no_of_days; 
        }elseif($no_of_days == 1 and $from_hour < 16){
             $total_insu_days = $total_insu_days + $no_of_days; 
        }*/
        




        $from_hour = date('H',$from_time); 
        $from_minute = date('i',$from_time);

        $from_time = $from_hour.':'.$from_minute; 
        

        $to_hour = date('H',$to_time); 
        $to_minute = date('i',$to_time);
        $to_time = $to_hour.':'.$to_minute; 
       

        $total_insu_days = 0;
        $to_already_added = false;
        $from_already_added = false;
        if($from_time < '16:30'){
            $total_insu_days++; 
        } 
        if($to_time > '16:30'){   
            //echo "here"; die;         
            $total_insu_days++; 
        } 
        $string_from_date = date('Y-m-d',$input['from_date']); 
        $string_to_date = date('Y-m-d',$input['to_date']); 

        $datetime1 = date_create_from_format('Y-m-d',$string_from_date);

        $datetime2 = date_create_from_format('Y-m-d',$string_to_date);

        $difference = $datetime1->diff($datetime2);
        
        if($no_of_days > 1 ){
            $total_insu_days = $total_insu_days + $difference->days; //since rental days and insurance days are different, i.e rental days are based on 24 hours, so not adding 
        }elseif($no_of_days == 1 and $string_from_date!=$string_to_date){
            /*echo $input['from_date']; 
            echo "<br>"; 
            echo $input['to_date'];*/
            $total_insu_days++; 
        }










        $ret_val['total_insu_days'] = $total_insu_days; 

        $setting = DB::table('setting')->first();
        $ret_val['rental_price'] = 0; 
        if($car->enable_custom_price){
            if($ret_val['no_of_days'] > 0 and $ret_val['no_of_days'] < 7){ // per day unit
                $ret_val['price_per_day'] = $car->custom_price; 
                $ret_val['rental_price'] = ($ret_val['price_per_day'] * $ret_val['no_of_days']);

            }elseif($ret_val['no_of_days'] >= 7 and $ret_val['no_of_days'] < 28 ){ //per week unit
               $ret_val['price_per_day'] = ($car->custom_price_week/7);                
               $ret_val['rental_price'] = ($ret_val['price_per_day'] * $ret_val['no_of_days']);
            }else{ //per month unit
                $ret_val['price_per_day'] = ($car->custom_price_month/28);    
                
                $ret_val['rental_price'] = ($ret_val['price_per_day'] * $ret_val['no_of_days']);
            }           
        }else{
            $ret_val['price_per_day'] = $car->car_estimated_values->default_price_per_day; 
            $ret_val['rental_price'] = $ret_val['price_per_day'] * $ret_val['no_of_days'];
        }
        $ret_val['price_per_day'] = "".$ret_val['price_per_day']; 
        $ret_val['rental_fee_per_day'] = "".round(($ret_val['rental_price']*$setting->rental_fee_percent)/100,2);
        $ret_val['rental_price'] = "".$ret_val['rental_price'];
        $input['task'] = 'getDailyRate';
        $input['make_id'] = $car->make_id; 
        $input['model_id'] = $car->model_id; 
        $input['year_made'] = $car->year_made; 
        $api_output = Car::getRentalFeeUsingAPI($input);
        

        
        if($api_output['success'] and $api_output['data']->salepremium > 0  ){
            $ret_val['rental_fee'] = "".$api_output['data']->salepremium; 
            $ret_val['rental_fee_per_day'] = "".round($api_output['data']->salepremium/$ret_val['total_insu_days'],2); 
        }else{
            $ret_val['rental_fee'] = $ret_val['rental_fee_per_day'] * $ret_val['total_insu_days'];  
        }

        $ret_val['rental_fee'] = "".$ret_val['rental_fee']; 

        $ret_val['booking_place_id'] = '';
        $ret_val['delivery_distance'] = '';
        $ret_val['proposed_pickup'] = 'pickup';
        $ret_val['error'] = false;  
        //dd($input); 
        if($car->offers_delivery and isset($input['proposed_pickup']) and $input['proposed_pickup'] == '1' and $input['booking_place_id']){

            $ret_val['booking_place_id'] = $input['booking_place_id'];
             $ret_val['proposed_pickup'] = 'delivery';
            $distance = CarRentFunctions::getDistanceDifference([
                    'car_lat'   => $car->loc_lat,
                    'car_lon'   => $car->loc_lon,
                    'place_id'  => $input['booking_place_id']
                ]); 
            $ret_val['delivery_fee'] = $ret_val['delivery_fee_per_ten_km'] = $car->delivery_fee;
            //$distance = 29; 

            if($distance){                
                if( $distance > 10){
                    $rem = ($distance%10);
                    $quot = floor($distance/10);
                    
                    $unit = $quot; 
                    if($rem > 0){
                        $unit = $unit + 1;
                    }
                    $ret_val['delivery_fee'] = ($car->delivery_fee * $unit);
                }                
                $ret_val['delivery_distance'] = $distance;                 
             }else{
                $ret_val = array();
                $ret_val['error'] = true;
                $ret_val['message'] = "Could not calculate distance"; 
                return $ret_val; 
             }
        }else{
            $ret_val['proposed_pickup'] = 'Pickup';   
            $ret_val['delivery_fee'] = 0;
        } 
        $ret_val['delivery_fee'] = "".$ret_val['delivery_fee']; 
        $ret_val['processing_fee'] = "".$setting->processing_fee; 
        $ret_val['subtotal'] = ($ret_val['rental_price'] + $ret_val['rental_fee'] + $ret_val['delivery_fee'] + $ret_val['processing_fee']);
        $ret_val['subtotal'] = "".$ret_val['subtotal'];       
        $ret_val['booking_tax'] =  "".round(($setting->tax_percent*$ret_val['subtotal'])/100,2); 
        
        $ret_val['booking_total'] =  round(($ret_val['subtotal'] + $ret_val['booking_tax']),2);
        $ret_val['booking_total'] = (string)$ret_val['booking_total']; 
        $ret_val['extra_charge_per_mile'] = "".$setting->extra_charge_per_mile;
        if($car->enable_custom_mileage){
            $ret_val['mileage_limit'] = $car->mileage_limit;
        }else{
            $ret_val['mileage_limit'] = $setting->mileage_limit_per_day;
        }
        
        if($car->car_images!=NULL){
            $car_image = $car->car_images->first();
            
            $ret_val['car_image'] = $car_img_path.@$car_image->photo; 
        }else{
            $ret_val['car_image'] = $default_car_pic;
        }
       
        //dd($ret_val);
        return $ret_val; 
    }  

     public static function getBookingPriceFromDB($input){

        //dd($input); 
        $car_img_path = env("BASE_URL")."images/cars/thumb/"; 
        $default_car_pic =  url('images/global/cars/car_default.jpg');
        $car = Car::find($input['car_id']); 
        $booking = Booking::where('id',$input['booking_id'])->first(); 
        $ret_val['no_of_days'] = CarRentFunctions::getTotalDays($input['from_date'],$input['to_date']);

        $from_time = $input['from_date']; 
        $to_time = $input['to_date']; 
        $no_of_days = $ret_val['no_of_days']; 

        $from_hour = date('H',$from_time); 
        $from_minute = date('i',$from_time);

        $from_time = $from_hour.':'.$from_minute; 
        

        $to_hour = date('H',$to_time); 
        $to_minute = date('i',$to_time);
        $to_time = $to_hour.':'.$to_minute; 
       

        $total_insu_days = 0;
        $to_already_added = false;
        $from_already_added = false;
        if($from_time < '16:30'){
            $total_insu_days++; 
        } 
        if($to_time > '16:30'){               
            $total_insu_days++; 
        } 
        $string_from_date = date('Y-m-d',$input['from_date']); 
        $string_to_date = date('Y-m-d',$input['to_date']); 

        $datetime1 = date_create_from_format('Y-m-d',$string_from_date);

        $datetime2 = date_create_from_format('Y-m-d',$string_to_date);

        $difference = $datetime1->diff($datetime2);
        
        if($no_of_days > 1 ){
            $total_insu_days = $total_insu_days + $difference->days; //since rental days and insurance days are different, i.e rental days are based on 24 hours, so not adding 
        }elseif($no_of_days == 1 and $string_from_date!=$string_to_date){          
            $total_insu_days++; 
        }

        $ret_val['total_insu_days'] = $total_insu_days; 
        $setting = DB::table('setting')->first();
        $ret_val['rental_price'] = $booking->rental_price; 
        $ret_val['price_per_day'] = $booking->per_day_price;  
        $ret_val['rental_fee'] = $booking->rental_fee;   
        $ret_val['rental_fee_per_day'] = round($booking->rental_fee/$ret_val['total_insu_days']); 
        $ret_val['rental_price'] = $ret_val['rental_price'];
        $ret_val['booking_place_id'] = '';
        $ret_val['delivery_distance'] = '';
        $ret_val['proposed_pickup'] = 'pickup';
        $ret_val['error'] = false;  
        
        
        $ret_val['proposed_pickup'] = $booking->proposed_pickup; 
        $ret_val['delivery_fee'] = $booking->delivery_fee; 

        $ret_val['processing_fee'] = $booking->processing_fee; 
        $ret_val['subtotal'] = ($ret_val['rental_price'] + $ret_val['rental_fee'] + $ret_val['delivery_fee'] + $ret_val['processing_fee']);
        $ret_val['subtotal'] = $ret_val['subtotal'];       
        $ret_val['booking_tax'] =  $booking->tax_amount; 
        
        $ret_val['booking_total'] =  round(($ret_val['subtotal'] + $ret_val['booking_tax']),2);
        $ret_val['booking_total'] = $ret_val['booking_total']; 
        $ret_val['extra_charge_per_mile'] = $setting->extra_charge_per_mile;
        if($car->enable_custom_mileage){
            $ret_val['mileage_limit'] = $car->mileage_limit;
        }else{
            $ret_val['mileage_limit'] = $setting->mileage_limit_per_day;
        }        
        if($car->car_images!=NULL){
            $car_image = $car->car_images->first();
            $ret_val['car_image'] = $car_img_path.@$car_image->photo; 
        }else{
            $ret_val['car_image'] = $default_car_pic;
        }
        return $ret_val; 
    } 


    public static function canMessage($status, $to_date){
        //dd($to_date);
        $today_last = date('Y-m-d');
        $end_date =date('Y-m-d',strtotime($to_date));
       // dd($end_date);
        if($status == 'completed' && $end_date == $today_last){
            return true;
        }elseif ($status == 'approved') {
            return true;
        }else{
            return false;
        }
    }

}
