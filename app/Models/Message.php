<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB,Auth;

class Message extends Model
{
	protected $table = 'messages';
    //public $timestamps  = false;
    protected $fillable = ['from_user','to_user','message','is_read','booking_id'];

    public function user(){
        return $this->belongsTo('App\Models\User','from_user');
    }
    /*public function user1(){
        return $this->belongsTo('App\Models\User','to_user');
    }*/

    public static function getMessage($data){    
	    $input['per_page'] = 100; 
	    $input['page_number'] = 1; 
	    $offset = $input['per_page'] * ($input['page_number']-1);
	    $per_page = $input['per_page'];
	    $limit = " LIMIT {$offset}, {$per_page}";

	    $SQL = "SELECT m.*,u.first_name,u.last_name,u.profile_pic,u.lang FROM messages m INNER JOIN users u 
	    ON u.id = m.from_user WHERE (m.to_user = ? or m.from_user = ?)  and m.booking_id = ?  order by id DESC $limit"; 
	    $rows = DB::select($SQL,array(Auth::user()->id,Auth::user()->id,$data['booking_id']));
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
	        return $results; 
	        
	    }else{
	        return false;
	    }

	}
}
