<?php namespace App\Library;

use App\Models\User;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use DB;




class RoomFinderFunctions {  






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



public static function getPagination($input){

   $paginate = array();
      $paginate['page'] = $input['page_number']+1;
       
      if(isset($input['per_page'])){
          $paginate['limit'] = $input['per_page'];
      } 
      $paginate['total'] = $input['total'];
      return $paginate;
}


public static function getMessage($msg){

   
          $message = array();
          $message['detail'] = $msg['detail'];
          if(!empty($msg['type'])){
             $message['type'] = $msg['type'];
          }
          $message['context'] = $msg['context'];
           $message['code'] = rand ( 1000 , 9999 );
      return $message;
}





}