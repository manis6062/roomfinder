<?php namespace App\Library;

use App\Models\User;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use DB;




class RoomFinderFunctions {  




    public static function createAndSendNotification($device_tokens,$noti_id,$device_type){        

       

        $message_eng = $message_thai = array();

        $device_eng = $device_thai = array();

        $ns = new NotificationScreens();

        $notification_data = $ns->getNotification($noti_id); 

        $params['mobile_target'] = $notification_data['mobile_target']; 

        $params['id'] = $notification_data['mobile_target_id']; 





        $screen_data = $ns->getData($params);

        

        if($notification_data){            

            foreach($device_tokens as $dn){ 

                $device_eng[] = $dn['token']; 

                if($dn['lang'] == 'th'){

                    $device_thai[] = $dn['token']; 

                }

            } 

            //send push notification in multiple language

            $al = array('en','th');

            $total_message = array();

            $dt_total = array();



            $msg_total = array();

            foreach($al as $l){

                $message = $notification_data['content_eng'];

                $dt = $device_eng;

                if($l == 'th'){                    

                    $message = $notification_data['content_thai'];

                    $dt = $device_thai;

                }



                $msg = array

                (

                    'id'                => $notification_data['id'],                    

                    'message'           => $message,

                    'mobile_target'     => $notification_data['mobile_target'],

                    'mobile_target_id'  => $notification_data['mobile_target_id'],

                    'notification'      =>$notification_data,

                    'result'            =>$screen_data                             

                ); 

                if($device_type == 'ios'){

                    $notification = array(

                        'body' =>   $message,

                       //'title' => 'Rent a car club',

                        'sound' => 'default',

                        'click_action' => $notification_data['mobile_target']

                    );



                   // array_push($total_message, $notification);

                    //array_push($dt_total, $dt);

                    //array_push($msg_total, $msg);

                   // self::sendNotification($dt,$msg,$notification);

                   

                    self::sendNotification($dt,$msg,$notification);

                }else{

                    self::sendNotification($dt,$msg);

                }              

                

                

            }

            //print_r($total_message);

            //print_r($dt_total); 

           // dd($msg_total);

        }

        

       

    }



    public static function sendNotification($device_tokens,$message,$notification = null){       

        $url = "https://fcm.googleapis.com/fcm/send"; 



        $fields = array(

                'registration_ids' => $device_tokens,

                'data' => $message,               

                'priority' => 'high',

                'notification' => $notification //this is needed for ios device

            );

        if(!$notification){



            $fields = array(

                'registration_ids' => $device_tokens,

                'data' => $message   

                );             

        }

        

        $fields = json_encode($fields); 



        $headers = array(

                //'Authorization:key = AIzaSyC6zMVytXFSAgUHpUuSx0-ZlV9Uk8CG45E',

                'Authorization:key = AIzaSyCUxLoeeAlXR7w437E3IJf2Ijxp3n2_lL4',            

                'Content-Type: application/json'

            );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);           

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = json_decode(curl_exec($ch)); 

        //var_dump($result); die;

        if($result){            

            foreach($result->results as $res){

               if(isset($res->error)){

                    \Log::warning( 'sending notification failed due to: ' . $res->error);

               }else{

                    \Log::info( 'sending notification success. Message id is: ' . $res->message_id);

               }

            }

        }    

        curl_close($ch); 

        return $result; 

    }



    



    public static function sendChat($data,$booking_id){

        

       // $default_url = 'https://rentacarclub-c4306.firebaseio.com/';

        $default_url = 'https://rent-a-car-club.firebaseio.com/';

        

        //$default_token = 'Tv7xuk5tUYzcyZMuPBvqgVPsaKBHhADaK6NQFsJx';

        $default_token = 'ETHjrXP3nzCGkh1jYmbouu0lOG5pIUjTvyF1ACPi';

        

        $default_path = env('FIREBASE_CHAT_NODE_PATH');



        //$firebase = new Firebase($default_url, $default_token);

        $firebase = new \Firebase\FirebaseLib($default_url, $default_token);

        

       // echo $default_path .'/'.$booking_id;

        

        $res = $firebase->push($default_path .'/'.$booking_id,$data);

       // var_dump($res); die;





    }



	public static function get_nearby( $lat, $lng,  $distance = 60, $unit = 'km' ) {

       

    // radius of earth; @note: the earth is not perfectly spherical, but this is considered the 'mean radius'

        if( $unit == 'km' ) { 

            $radius = 6371.009; 

        }elseif ( $unit == 'mi' ) { 

            $radius = 3958.761; 

        }



        // latitude boundaries

        $maxLat = ( float ) $lat + rad2deg( $distance / $radius );

        $minLat = ( float ) $lat - rad2deg( $distance / $radius );    

        

        $maxLng = ( float ) $lng + rad2deg( $distance / $radius) / cos( deg2rad( ( float ) $lat ) ); 

        $minLng = ( float ) $lng - rad2deg( $distance / $radius) / cos( deg2rad( ( float ) $lat ) );

        



        $max_min_values = array(

            'max_latitude' => $maxLat,

            'min_latitude' => $minLat,

            'max_longitude' => $maxLng,

            'min_longitude' => $minLng

            );  



         //dd($max_min_values); 

        return $max_min_values;

    }

    public static function generateApiToken ($user_id){

        while( $new_ref = md5(uniqid( $user_id , true) )  ) {

            $SQL = "SELECT id from user_sessions WHERE access_token = ? LIMIT 1";

            $res = DB::select($SQL,[$new_ref]);

            if(empty($res)){

                $token = $new_ref;

                return $token;

            }            

        }

    }

    public static function generateApiKey ($user_id){

        while( $new_ref = md5(uniqid( $user_id , true) )  ) {

            $SQL = "SELECT id from webapi_users WHERE api_key = ? LIMIT 1";

            $res = DB::select($SQL,[$new_ref]);

            if(empty($res)){

                $token = $new_ref;

                return $token;

            }            

        }

    }


    public static function generateResetPasswordToken ($user_id){

        while( $new_ref = md5(uniqid( $user_id , true) )  ) {

            $SQL = "SELECT id from users WHERE password_reset_token = ? LIMIT 1";

            $res = DB::select($SQL,[$new_ref]);

            if(empty($res)){

                $token = $new_ref;

                return $token;

            }            

        }

    }

    public static function checkToken($token,$user_id = false){

        $array[] = $token;

        $cond = "";

        if($user_id){

            $cond = " and user_id = ?";   

            $array[] = $user_id;  

        }

        $SQL = "SELECT id from user_sessions WHERE access_token = ? ".$cond." LIMIT 1";

        if(DB::select($SQL,$array)){

            return true;

        }else{

            return false;

        }

    }

    //api key checker
    public static function checkApi($api_key){


        $row = WebapiUser::where('api_key',$api_key)->where('status', 1)->first();

        if($row){

            return true;

        }else{

            return false;

        }
    }



    public static function SendSmsMessage($number  = '9851221698' , $msg = 'no msg send',$country_code = '+66'  ) {   

       
        if(env('SEND_SMS') == 'NO'){
            return; 
        }
            

        $msg.=" -- ".env('SITE_TITLE');        

        if($country_code == '+66'){   

           // echo $number; die;                

            $url = "http://www.thaibulksms.com/sms_api.php";

            $data_string = "username=0939725546&password=311787&msisdn=$number&message=$msg&sender=NOTICE";

            $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4)Gecko/20030624 Netscape/7.1 (ax)";

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_USERAGENT, $agent);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

            $result = curl_exec ($ch);            

            curl_close ($ch);

            if( strpos('<Status>0</Status>', $result )   ) {

                return true;

            } 

            else  {

                \Log::warning( 'error sending sms : ' . $number);

                return false;

            }



        }else{

            try{ 

                $number = $country_code.$number;

                /*echo $number;

                echo $msg; 

                dd('np');*/

                Twilio::from('twilio')->message(  $number  ,  $msg  );

            }

            catch(\Exception $e) {

            //error detected

                //dd($e);

                \Log::warning( ' SMS ERROR : ' .  $number   );

                \Log::warning($e);

                return false;

            }



        }

        



    }



    public static function getMonthsArray(){

        $months = array();

        $months[1] = 'Jan';

        $months[2] = 'Feb';

        $months[3] = 'Mar';

        $months[4] = 'Apr';

        $months[5] = 'May';

        $months[6] = 'June';

        $months[7] = 'Jul';

        $months[8] = 'Aug';

        $months[9] = 'Sep';

        $months[10] = 'Oct';

        $months[11] = 'Nov';

        $months[12] = 'Dec';

        return $months; 

    }



    public static function getYearArray($total_year){

        $years = array();



        for($i = date('Y'); $i<=(date('Y')+$total_year); $i++){

            $years[] = $i; 

        }

        return $years; 

    }

    public static function getModelYear($year_back){

        $years = array();



        for($i = date('Y'); $i>=(date('Y')-$year_back); $i--){

            $years[] = $i; 

        }

        return $years; 

    }



    public static function getDates($total_month = 6,$type = "all"){

        $dates =   array();

        $month = date("m");

        $year = date("Y"); 

        $day = null; 

        $current_date = time(); 

        

        for($j = 1; $j <= $total_month; $j++){   



            $num = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            if(date("m") == $month){

                $day = date("d");

            }else{

                $day = 1;

            }

            for ($i = $day; $i <= $num; $i++) {

                $mktime = mktime(0, 0, 0, $month, $i, $year);

                $d = date("m/d/Y", $mktime); 

                //$date['date'] = $d;                 

                

                $day_index = date("w", $mktime); 

                if($type == "all"){

                    $dates[] = $d;

                }elseif($type == "Weekends"){

                    if ($day_index == 0 || $day_index == 6) {

                        $dates[] = $d;

                    }

                }elseif($type == "Weekdays"){

                    if ($day_index > 0 and $day_index < 6) {

                        $dates[] = $d;

                    }

                }

                

            }            

            $current_date = strtotime ( '+1 month' ,  $current_date ) ;

            $month = date("m",$current_date);

            $year = date("Y",$current_date);

        }

        return $dates;

    }

    public static function getTotalDays($from_date,$to_date){

       // $now = time(); // or your date as well

        //$your_date = strtotime("2010-01-01");

        $datediff =  $to_date - $from_date;

        

         // return round($datediff/(60*60*24),3); 

         return ceil($datediff/(60*60*24));

    }

    public static function readableTime($from_date,$to_date){

        $from_date = strtotime($from_date); 
        $from_date = date('Y-m-d', $from_date);
        $from_date = strtotime($from_date); 
        
        $to_date = strtotime($to_date);
        $to_date = date('Y-m-d', $to_date);
        $to_date = strtotime($to_date);         

        $time = $to_date - $from_date; // to get the time since that moment
        
        $days = intval(intval($time) / (3600*24));
        $ret = "";
        if($days> 0)
        {
            $ret .= "$days days ";
        }
        
        return $ret;



       /* $time = ($time<1)? 1 : $time;

        $tokens = array (

            31536000 => 'y',

            2592000 => 'm',

            604800 => 'w',

            86400 => 'd',

            3600 => 'h',

            60 => 'm',

            1 => 's'

            );



        foreach ($tokens as $unit => $text) {

            if ($time < $unit) continue;

            $numberOfUnits = ceil($time / $unit);

            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');

        }
        */


    }







    public static function humanTiming ($time)

    {



    $time = time() - $time; // to get the time since that moment

    $time = ($time<1)? 1 : $time;

    $tokens = array (

        31536000 => 'y',

        2592000 => 'm',

        604800 => 'w',

        86400 => 'd',

        3600 => 'h',

        60 => 'm',

        1 => 's'

        );



    foreach ($tokens as $unit => $text) {

        if ($time < $unit) continue;

        $numberOfUnits = floor($time / $unit);

        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');

    }



}



    public static function generateSlug($title,$table_name,$column_name,$edit = false)

    {

        $slug = Str::slug($title);

           // $user = DB::table($table_name)->where('slug', "")->first();

        if(!$edit){

           $slugCount = count( DB::table($table_name)->whereRaw("{$column_name} REGEXP '^{$slug}(-[0-9]*)?$'"));

       }else{

        $slugCount = count( DB::table($table_name)->whereRaw("{$column_name} REGEXP '^{$slug}(-[0-9]*)?$'")->where($column_name,"!=",$slug));

    }

    return ($slugCount > 1) ? "{$slug}-{$slugCount}" : $slug;

    }



    public static function getEmailTemplate($slug){

       // DB::enableQueryLog();

     $template = Cms::where("status","active")->where("cms_type","email_template")->where("slug",$slug)->first(); 

       //dd(DB::getQueryLog());

     if($template){

        return $template;

    }else{

        return false;

    }

    }



public static function sendEmail($data){

       // dd($data); 

    Mail::send(['html' => 'emails.email_template'], ['content' => $data['message']], function ($m) use ($data) {

        $m->from('support@rentacarclub.co', 'Rent a car club');

        if(isset($data['attachment'])){

            $m->attach($data['attachment'], ['mime' => $data['mime']]);

        }

        $to_name = ''; 

        if(isset($data['to_name'])){ //this is because if the to_name is in thai or non english, space between the first name and last name is creating issue malformed receipient address

            $to_name_array = explode(' ',$data['to_name']); 

            $to_name = $to_name_array[0]; 

        }

        $to_name_array = $data['to_name'];

        

        $to_name = "=?UTF-8?B?".base64_encode($to_name)."?="; 

        /*echo "<br>"; 

        echo $data['to_email']; die;*/

        if(isset($data['to_cc_email'])){
             $m->cc($data['to_cc_email']);

        }
         //dd($data['to_cc_email']);

        $m->to($data['to_email'],$to_name)->subject($data['subject']);

      // $m->to('es.pradeeparyal@gmail.com',$to_name)->subject($data['subject']);



    });



   

}



public static function addURLParameter($url,$params){

   // $url = 'http://example.com/search?keyword=test&category=1&tags[]=fun&tags[]=great';

//var_dump($params);

    $url_parts = parse_url($url);

   // var_dump($url_parts); die;

    if(isset($url_parts['query'])){

        parse_str($url_parts['query'], $params);

    }

    



  //  $params['category'] = 2;     // Overwrite if exists

   // $params['tags'][] = 'cool';  // Allows multiple values



    // Note that this will url_encode all values

    $url_parts['query'] = http_build_query($params);



    // If you have pecl_http

   // echo http_build_url($url_parts);



    // If not

    return $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'] . '?' . $url_parts['query'];

    }



  public static function getDeliveryPriceList(){

    $price = ['100','200','300','400','500','600','700','800','900','1000'];

    return $price; 

  } 

  public static function getModulesArray(){

    $modules['cars'] = 'Cars';    

    $modules['make'] = 'Make';

    $modules['models'] = 'Models';

    $modules['features'] = 'Features';

    $modules['estimated-values'] = 'Car estimated values';

    $modules['vehicle-type'] = 'Vehicle Type';

    $modules['users'] = 'Users';

    $modules['cms'] = 'CMS';

    $modules['banks'] = 'Banks';

    $modules['licences'] = 'Licences';

    $modules['bookings'] = 'Bookings';

    $modules['admins'] = 'Admins';

    $modules['setting'] = 'Setting';

    $modules['logs'] = 'Logs';

    $modules['payments'] = 'Payments';

    $modules['search'] = 'search';   

    $modules['user-login-details'] = 'user-login-details';   

    $modules['location'] = 'location';  

    $modules['banking'] = 'banking';    

    $modules['feedback'] = 'feedback'; 

    $modules['webapiusers'] = 'webapiusers';  

    

    return $modules; 

}


public static function getDistanceDifference($data,$by_place_id = true){

   // $key = "AIzaSyBAK9AawAlU0TbzFWrGIFOJLO46KBv6aV0"; 

    $key = "AIzaSyCUxLoeeAlXR7w437E3IJf2Ijxp3n2_lL4"; 

    
    $url = '';
    if($by_place_id){
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=place_id:".$data['place_id']."&destinations=".$data['car_lat'].",".$data['car_lon']."&mode=driving&key=".$key;
    }else{
     $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$data['source_lat'].",".$data['source_lon']."&destinations=".$data['desti_lat'].",".$data['desti_lon']."&mode=driving&key=".$key;
    }
   //die;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch);

    curl_close($ch);

    $response_a = json_decode($response, true);



    if(isset($response_a['error_message'])){

        return false;

    }

    if($response_a['rows'][0]['elements'][0]['status'] == 'ZERO_RESULTS'){

        return false;

    }    

    $dist = $response_a['rows'][0]['elements'][0]['distance']['value'];

    //return array('distance' => $dist, 'time' => $time);

    $dist = ($dist / 1000); 

    $dist = $dist *2; //making both ways

    $dist = bcdiv($dist, 1, 1);

   // die;

    return $dist; 

}

public static function getCurrentCountryCode(){

    $ip = $_SERVER['REMOTE_ADDR'];

    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));

    if(!isset($details->country))

        { $details->country = "th";}

    $cc = DB::table('countries')->select('phonecode')->where('iso',$details->country)->first();



    if($cc){



        return ('+'.$cc->phonecode); 

    }else{

        return false; 

    }

}



 public static function getSendCode($user_id = 0) {

    $user = User::find($user_id);

    if($user) {         

      $rem = rand( 1000, 9999 );

      $user->fill( array( 'mobile_verification_code' => $rem ))->save();         

      return $rem;

    } 

    else {

      return false;

    }    

  }



 public static function getAccounttype(){    

    $account_type['individual'] = Lang::get('formlabels.individual');

    $account_type['corporation'] = Lang::get('formlabels.corporation');   

    return $account_type;

 }



 public static function getSexList(){    

    $sex['Male'] = Lang::get('formlabels.male');

    $sex['Female'] = Lang::get('formlabels.female');   

    return $sex;

 }

 public static function getYesNo(){

    $yes_no['1'] = Lang::get('formlabels.yes');

    $yes_no['0'] = Lang::get('formlabels.no');   

    return $yes_no;

 }

 public static function getMileageOption(){

    $mileage[] = '0 - 15,000';

    $mileage[] = '15,000 - 50,000';

    $mileage[] = '51,000 - 100,000';

    $mileage[] = '100,000 or more miles';

    return $mileage;

 }



 public static function getMileageLimitOption(){

    $mileage[] = '100';

    $mileage[] = '200';

    $mileage[] = '300';

    $mileage[] = '500';

    $mileage[] = '700';

    $mileage[] = '1000';

    $mileage[] = '1200';

    $mileage[] = '1500';

    return $mileage;

 }



 public static function getTransmissionOption(){

    $transmission['automatic'] = 'Automatic';

    $transmission['manual'] = 'Manual';

    return $transmission;

 }



 public static function getPickupDeliveryOption(){

    $delivery_option['0'] = 'Pickup';

    $delivery_option['1'] = 'Delivery';

    return $delivery_option;

 }



 public static function convertStringToAnchor($content){

    $str = str_replace('www.', 'http://www.', $content);

    $str = preg_replace('|http://([a-zA-Z0-9-./]+)|', '<a href="http://$1">$1</a>', $str);

    $str = preg_replace('/(([a-z0-9+_-]+)(.[a-z0-9+_-]+)*@([a-z0-9-]+.)+[a-z]{2,6})/', '<a href="mailto:$1">$1</a>', $str);

    return $str;

 }

 public static function getResponseRateAndTime($user_id){

    $booking = Booking::from('car_bookings as cb')->select('cb.status','cb.created_at','cb.approved_date','cb.rejected_date')

    ->join('cars as c','c.id','=','cb.car_id')

    ->where('c.user_id',$user_id)   

    ->whereIn('cb.status',['approved','completed','rejected','expired'])

    ->get();

    $rate_time['response_rate'] = 'NA';

    $rate_time['response_time'] = 'NA';

    //echo $booking->count(); die;

    if($booking){

        $total_time = 0; 

        $total_event = 0;

        $total_expired_dates = 0; 

        $time_in_minutes = 0;

        foreach($booking as $b){  



            if($b->status == 'expired'){  

                $total_expired_dates++;               

                continue;

            }

           // echo $b->status; 

            if($b->status == 'approved' or $b->status == 'completed' and $b->approved_date){

                $time_in_minutes = self::getTimeDifferenceInMinutes($b->created_at,$b->approved_date);                

            }elseif($b->rejected_date){

                $time_in_minutes = self::getTimeDifferenceInMinutes($b->created_at,$b->rejected_date);

            }

           /* echo "here".$time_in_minutes; 

            echo "<br>"; */

            if($time_in_minutes){

                $total_time+=$time_in_minutes;

                $total_event+=1; 

            }

        }

        //echo "total_time"; 



        //echo $total_time; 



        //die;

        //echo "asdf".$total_expired_dates; die;

      

        if($total_time > 0 and $total_event > 0){

             $rate_time['response_time'] = ceil($total_time / $total_event);

             $rate_time['response_rate'] = (($booking->count() - $total_expired_dates) / $booking->count())*100;

             $rate_time['response_rate'] = number_format($rate_time['response_rate'],2); 

        }    

       // dd($rate_time);     

        return $rate_time; 



    }



 }



 public static function getTimeDifferenceInMinutes($from,$to){



    /*echo "sadf".$from; 

    echo $to; 

    die;*/

    if($from!=NULL or $to!=NULL){

        $to_time = strtotime($to);

        $from_time = strtotime($from);

        return (($to_time - $from_time) / 60); 

    }else{

        return false; 

    }

    

 }



 public static function chageToDBDateFormat($format,$date){

    $date=date_create_from_format($format,$date);

    return $date->format('Y-m-d'); 



 }



 public static function chageDateFormat($date,$current_format,$to_format){

    if($date!=null){

        $date=date_create_from_format($current_format,$date);

        return $date->format($to_format); 

    }

    

 }



 public static function changeDateFormatArray($dates,$current_format,$to_format){

    $date_array = array();



    for($i = 0; $i<count($dates);$i++){

     

     $date=date_create_from_format($current_format,$dates[$i]['start']);

     $date_array[] = $date->format($to_format); 

    } 

   

    return $date_array;

 }



 public static function changeDateFormatArray2($dates,$current_format,$to_format){

    $date_array = array();



    for($i = 0; $i<count($dates);$i++){

     

     $date=date_create_from_format($current_format,$dates[$i]);

     $date_array[] = $date->format($to_format); 

    } 

   

    return $date_array;

 }



 public static function getEmailOrPushOrSmsContent($data){

    //dd($data); 

    if($template = self::getEmailTemplate($data['slug'])){ 

        $lang = \App::getLocale();      

        if(isset($data['lang'])){

            $lang = $data['lang'];

        }     

        $content = $template->content_eng; 

        $subject = $template->subject_eng;

        if($lang == 'th'){

            $content = $template->content_thai; 

            $subject = $template->subject_thai;

        }
        $search_array = self::getEmailTemplateVariables();
        $ret_content['content'] = str_replace($data['search_array'],$data['replace_array'],$content);

        $ret_content['subject'] = str_replace($data['search_array'],$data['replace_array'],$subject);

        /*if($data['field'] == 'subject'){

            $ret_content['content'] = str_replace($data['search_array'],$data['replace_array'],$subject);

            $ret_content['subject'] = str_replace($data['search_array'],$data['replace_array'],$subject);

        }*/

        return $ret_content;

    }else{

        return false;

    }

 }

public static function getEmailContentSubject($data){
   // dd($data);
    $email_template = Cms::where('slug',$data['slug'])->first();
    if($email_template){
        $subject_col = self::getColumnName('email_template','subject',$data['lang']);
        $content_col = self::getColumnName('email_template','content',$data['lang']);

        $return_array['content'] = $email_template->$content_col;
        $return_array['subject'] = $email_template->$subject_col;
        $variables = self::getEmailTemplateVariables();
        foreach($data['replace_array'] as $key=>$val){
            if(in_array('{'.$key.'}',$variables)){
                $search[] = '{'.$key.'}';
                $replace[] = $val;  
            }
        }
        $return_array['content'] = str_replace($search, $replace, $email_template->$content_col);
        $return_array['subject'] = str_replace($search, $replace, $email_template->$subject_col);
        return $return_array; 
    }else{
        return false;
    }
}

public static function getEmailTemplateVariables(){
    $variables[] = "{first_name}"; 
    $variables[] = "{last_name}";
    $variables[] = "{link}";
    $variables[] = "{site_title}";
    $variables[] = "{link}";
    $variables[] = "{full_name}";
    $variables[] = "{car_full_name}";
    $variables[] = "{owner_full_name}";
    $variables[] = "{user_full_name}";
    $variables[] = "{reason_for_rejection}";
    $variables[] = "{cancellation_reason}";
    $variables[] = "{cancelation_reason}";
    $variables[] = "{booking_id}";
    $variables[] = "{booking_from}";
    $variables[] = "{booking_to}";
    $variables[] = "{rental_fee}";
    $variables[] = "{insurance_fee}";
    $variables[] = "{delivery_fee}";
    $variables[] = "{processing_charge}";
    $variables[] = "{sub_total}";
    $variables[] = "{tax}";
    $variables[] = "{total}";
    $variables[] = "{delivery_fee}";
    $variables[] = "{password}";
    return $variables; 

}

public static function CallAPI($data)

{

    

    //$param['hashcode'] = 'OS1001MP27412';

    $param['hashcode'] = 'RRC28572125';

    $url = null; 

    switch ($data['task']) {

        case 'getDailyRate':

            $param['duration'] = 1;

            if(isset($data['duration'])){

                $param['duration'] = $data['duration']; 

            }

            $param['year'] = $data['year'];

            $param['make'] = $data['make'];

            $param['model'] = $data['model'];

            $url =  "https://misterprakan.com/apiws/motor/afmotor.svc/cdailyquotes";

            break;

        case 'start_cover':

            //dd($data); 

            if(isset($data['year']) and isset($data['sinsuredphone']) and isset($data['make']) and isset($data['model']) and isset($data['startdate']) and isset($data['enddate']) and isset($data['license']) and isset($data['chasis']) and isset($data['sinsuredname']) and isset($data['sinsuredpassport']) and isset($data['sinsuredaddress']) and isset($data['year']) and isset($data['sinsuredemail'])){

                $param['year'] = $data['year'];

                $param['make'] = $data['make'];

                $param['model'] = $data['model'];

                $param['startdate'] = $data['startdate'];

                $param['enddate'] = $data['enddate'];

                $param['license'] = $data['license'];

                $param['chasis'] = $data['chasis'];

                $param['sinsuredname'] = $data['sinsuredname'];

                $param['sinsuredpassport'] = $data['sinsuredpassport'];

                $param['sinsuredaddress'] = $data['sinsuredaddress'];

                $param['sinsuredemail'] = $data['sinsuredemail'];

                $param['sinsuredphone'] = $data['sinsuredphone'];

                //dd($param); 

            }else{

                return "params_missing";

            }            

            $url =  "https://misterprakan.com/apiws/motor/afmotor.svc/cdailystartcover";

            break;

        case 'download_covernote':

            if(isset($data['policyid'])){

                $param['policyid'] = $data['policyid'];               

            }else{

                return "params_missing";

            }            

            $url =  "https://misterprakan.com/apiws/motor/afmotor.svc/cdailydownloadcv";

            break;

        default:

            # code...

            return; 

    }

    $url = sprintf("%s?%s", $url, http_build_query($param));

    //echo $url = "https://misterprakan.com/apiws/motor/afmotor.svc/cdailyquotes?hashcode=".$hashcode;

    //

    //$url = 'https://misterprakan.com/apiws/motor/afmotor.svc/cdailystartcover?hashcode=OS1001MP27412&year=2010&make=TOYOTA&model=YARIS&startdate=2017-04-29&enddate=2017-04-30&license=123 กขค กท&chasis=AAAAAAAAAA123&sinsuredname=Dom&sinsuredpassport=A1234&sinsuredaddress=110/13 Bangkok&sinsuredemail=es.pradeeparyal'

   //$data_string = json_encode($data);   

    //$url = 'https://misterprakan.com/apiws/motor/afmotor.svc/cdailydownloadcv?hashcode=OS1001MP27412&policyid=7022';

    //echo $url; die;

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);



    if($data['task']!='download_covernote'){

        //echo "here"; die;

        $result = json_decode(curl_exec($curl));

        $return = [];

        //dd($result); 

        if($result->d[0]->status == "OK"){

            $return['success'] = true; 

            $return['data'] = $result->d[0]; 

        }else{

            $return['success'] = false;        

        }  

        return $return;

    }else{

        $pdf_name = 'policy-'.$data['policyid'].'.pdf';        

        $pdf_policy_path = base_path()."/pdf/insurance_policy/".$pdf_name; 

        $result = curl_exec($curl);

        $raw = $result; 

        $fpf = fopen($pdf_policy_path,'w');

        fwrite($fpf, $raw);

        fclose($fpf);

        $return['success'] = true; 

        $return['pdf_policy_path'] = $pdf_policy_path; 

    }

    curl_close($curl);

    return $return; 

}

public static function getColumnName($table_name,$column_start,$lang_code = 'en'){

    $lang['en'] = 'eng';
    $lang['th'] = 'thai';
    $lang['zh'] = 'chn';
    $lang['id'] = 'idn';
    $lang['lo'] = 'lao';
    $lang['km'] = 'cam';
    $lang['my'] = 'bur';
    $lang['ru'] = 'rus';
    $lang['ms'] = 'mys';
    if(trim($lang_code) == ''){
        $lang_code = 'en'; 
    }    
    $column_name = $column_start.'_'.$lang[$lang_code]; 
    return $column_name; 
}


}