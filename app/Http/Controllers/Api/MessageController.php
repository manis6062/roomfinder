<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\Booking;
use App\Models\Notification;
use App\Library\CarRentFunctions;
use DB,Lang;
class MessageController extends Controller
{
    public function getMessage(Request $request){
    	$input = $request->all();
    	$v = \Validator::make($input,   [ 
			'user_id' => 'required|numeric|exists:users,id',
            'booking_id' => 'required|numeric|exists:car_bookings,id',
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
        
      //  $user = User::find($input['user_id']);
        $offset = $input['per_page'] * ($input['page_number']-1);
        $per_page = $input['per_page'];
        $limit = " LIMIT {$offset}, {$per_page}";

        $SQL = "SELECT m.*,u.first_name,u.last_name,u.profile_pic,u.lang FROM messages m INNER JOIN users u 
        ON u.id = m.from_user WHERE m.to_user = ?  order by id DESC $limit";
        $rows = DB::select($SQL,array($input['user_id']));
        if($rows){
            $user_img_path = env("BASE_URL")."images/users/thumb/";
            $result = $results = array();
            foreach($rows as $row ){
                $result['content'] = $row->message;
                if($row->profile_pic){
                    $result['profile_pic'] = $user_img_path.$row->profile_pic;
                }else{
                    $result['profile_pic'] = url('images/global/users/default-avatar.png');
                }           
                
                $result['full_name'] = $row->first_name." ".$row->last_name; 
                $result['created_at'] = date("m/d/Y H:i:s",strtotime($row->created_at));
                $results[] = $result;
            }
            return \Response::json(array(  'error' => false,  'result' => $results ) );
        }else{
            return \Response::json(array(  'error' => true,  'message' => Lang::get('messages.resultnotfound') ) );
        }

    }
    public function sendMessage(Request $request){
        $input = $request->all();
        $v = \Validator::make($input,   [             
            'booking_id' => 'required|numeric|exists:car_bookings,id',
            'from_user' => 'required|numeric|exists:users,id',
            'to_user'   => 'required|numeric|exists:users,id',
            'message'   => 'required',
            'created_at'  => 'required|numeric'
            ] );
        if ($v->fails())
        {   
            $msg = array();
            $messages = $v->errors();           
            foreach ($messages->all() as $message) {
                return \Response::json(array(  'error' => true,  'message' => $message ) );
            }  
        }
       $mid = Message::create(array(
        'booking_id'    =>  $input['booking_id'],
        'from_user'     =>  $input['from_user'],
        'to_user'       =>  $input['to_user'],
        'message'       =>  $input['message'],            
        'created_at'    =>  date("Y-m-d H:i:s"),
        'updated_at'    =>  date("Y-m-d H:i:s")
        ));
      
        //send chat push notification to message receiver. 
       $booking = Booking::find($input['booking_id']);
       $sub_target = ""; 
        $param['mobile_target'] = 'message';
        if($booking->car->user_id == $input['to_user']){
            $sub_target = 'my_car_rentals_detail';
        }else{
            $sub_target = 'my_rentals_detail'; 
        }
        $param['booking_id'] = $booking->id; 
        $param['mobile_sub_target'] = $sub_target; 
        $param['mid'] = $mid->id;  
        $device_tokens_ios = CarRentFunctions::getGoogleDeviceTokens($input['to_user'],'ios');
        $device_tokens_andriod = CarRentFunctions::getGoogleDeviceTokens($input['to_user'],'android');
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
        return \Response::json(array(  'error' => false,  'result' => Lang::get('messages.success') ) );
    }

    public function readMessage(Request $request){
        $input = $request->all();
        $v = \Validator::make($input,   [             
            'booking_id' => 'required|numeric|exists:car_bookings,id',
            'user_id' => 'required|numeric|exists:users,id'
            ] );
        if ($v->fails())
        {   
            $msg = array();
            $messages = $v->errors();           
            foreach ($messages->all() as $message) {
                return \Response::json(array(  'error' => true,  'message' => $message ) );
            }  
        }
        Message::where('booking_id', $request->booking_id)
          ->where('to_user', $request->user_id)
          ->update(['is_read' => 1]);
         return \Response::json(array(  'error' => false,  'result' => Lang::get('messages.success') ) ); 
        
    }


}
