<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Library\CarRentFunctions;
use DB,Lang;
use Carbon\Carbon;
use App\Models\Booking; 
class NotificationController extends Controller
{
    public function getUnreadNotificationCount(Request $request){
       // echo "here"; die;
        $input = $request->all();
        $v = \Validator::make($input,   [ 
            'user_id' => 'required|numeric|exists:users,id'
            ] );
        if($v->fails())
        {   
            $msg = array();
            $messages = $v->errors();           
            foreach ($messages->all() as $message) {
                return \Response::json(array(  'error' => true,  'message' => $message ) );
            }  
        }  

        $total_unread = Notification::where('is_read',0)->where('user_id',$request->user_id)->count();
        $result = [
            'total_unread' => $total_unread
        ];
        return \Response::json(array(  'error' => false,  'result' => $result ) );

    }

    public function readNotification(Request $request){
       // echo "here"; die;
        $input = $request->all();
        $v = \Validator::make($input,   [ 
            'user_id' => 'required|numeric|exists:users,id'            
            ] );
        if($v->fails())
        {   
            $msg = array();
            $messages = $v->errors();           
            foreach ($messages->all() as $message) {
                return \Response::json(array(  'error' => true,  'message' => $message ) );
            }  
        }  

        Notification::where('user_id',$request->user_id)->update(['is_read' => 1]);
        $result = [
            'message' => Lang::get('messages.success')
        ];
        return \Response::json(array(  'error' => false,  'result' => $result ) );

    }

    

    public function getNotification(Request $request){
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
        $lang = \App::getLocale();
      //  $user = User::find($input['user_id']);
        $offset = $input['per_page'] * ($input['page_number']-1);
        $per_page = $input['per_page'];
        $limit = " LIMIT {$offset}, {$per_page}";
        //left join because need to show the system notification to user app and system does not have user id
        $SQL = "SELECT n.*,u.first_name,u.last_name,u.profile_pic FROM notifications n LEFT JOIN users u 
        ON u.id = n.invoker_user_id WHERE n.user_id = ?  order by n.id DESC $limit";
        $rows = DB::select($SQL,array($input['user_id']));
        $result = $results = array();
        if($rows){
            $user_img_path = env("BASE_URL")."images/users/thumb/";
            
            foreach($rows as $row ){
                $result = NULL; 
                $result['id'] = $row->id; 
                $result['content'] = $row->content_eng;
                if($lang == 'th'){
                    $result['content'] = $row->content_thai;
                }
                if($row->notification_type == 'user'){
                    if($row->profile_pic){
                        $result['profile_pic'] = $user_img_path.$row->profile_pic;
                    }else{
                       $result['user_profile_pic'] = url('images/global/users/default-avatar.png'); 
                    }                    
                    $result['full_name'] = $row->first_name." ".$row->last_name;                    
                }else{
                    $result['profile_pic'] = url('images/site/logo.png');
                    $result['full_name'] = env('SITE_TITLE');
                }
                
                $result['mobile_target'] = $row->mobile_target; 
                $result['mobile_target_id'] = $row->mobile_target_id; 

                /*******Check if the booking *********************/
                $booking_screens = ['my_car_rentals_detail','my_car_rentals','my_rentals_detail','my_rentals'];
                if(in_array($row->mobile_target, $booking_screens)){
                    $booking_detail = Booking::from('car_bookings as b')->where('b.id',$row->mobile_target_id)->join('cars as c','c.id','=','b.car_id')->first();
                    if(!$booking_detail){
                        continue; 
                    }
                }
                $result['human_read_created_at'] = Carbon::parse($row->created_at)->diffForHumans(null, true);
               
                $results[] = $result;
            }
            return \Response::json(array(  'error' => false,  'result' => $results ) );
        }else{
            return \Response::json(array(  'error' => false,  'result' => $results));
        }

    }
}
