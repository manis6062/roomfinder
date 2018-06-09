<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Library\RoomFinderFunctions;
use App\Models\Images;
use Log; 
use Illuminate\Database\Eloquent\SoftDeletes; 
class Room extends Model
{  

   use SoftDeletes;
  protected $dates = ['created_at', 'updated_at', 'deleted_at'];

  protected $table = 'rooms';
  protected $fillable = array( 
    'user_id','type','no_of_floor' , 'no_of_room','parking','restroom',
    'phone_no','loc_lat','loc_lon','address','preference',
    'price' , 'kitchen' , 'image_id' , 'description' , 'occupied');
  public function user(){
    return $this->belongsTo('App\Models\User','user_id');
  }


public function car_images(){
  return $this->hasMany('App\Models\CarImagesModel','car_id');
}


public static function search($data){
   $limit = "";


  if( isset($data['per_page']) and isset($data['page_number']) ){

    $offset = $data['per_page'] * ($data['page_number']-1);
    $per_page = $data['per_page'];
    $limit.=" LIMIT {$offset}, {$per_page}";
  }   

  $SQL = "SELECT r.* FROM rooms r inner join users u ON r.user_id = u.id where r.deleted_at IS NULL";

  $cond = "";
  // $where= " where r.deleted_at = NULL";
  // $SQL.=$where;
  $param = array();
  if(isset($data['type'])){
    $cond.= " and r.type = ?";
    $param[] = $data['type'];
  }
   
    if(isset($data['no_of_floor'])){
    $cond.= " and r.no_of_floor = ?";
    $param[] = $data['no_of_floor'];
  }

     if(isset($data['no_of_room'])){
    $cond.= " and r.no_of_room = ?";
    $param[] = $data['no_of_room'];
  }

      if(isset($data['parking'])){
    $cond.= " and r.parking = ?";
    $param[] = $data['parking'];
  }

        if(isset($data['kitchen'])){
    $cond.= " and r.kitchen = ?";
    $param[] = $data['kitchen'];
  }

         if(isset($data['restroom'])){
    $cond.= " and r.restroom = ?";
    $param[] = $data['restroom'];
  }

          if(isset($data['phone_no'])){
    $cond.= " and r.phone_no = ?";
    $param[] = $data['phone_no'];
  }

           if(isset($data['loc_lon'])){
    $cond.= " and r.loc_lon = ?";
    $param[] = $data['loc_lon'];
  }

             if(isset($data['loc_lat'])){
    $cond.= " and r.loc_lat = ?";
    $param[] = $data['loc_lat'];
  }

               if(isset($data['address'])){
    $cond.= " and r.address = ?";
    $param[] = $data['address'];
  }

                 if(isset($data['preference'])){
    $cond.= " and r.preference = ?";
    $param[] = $data['preference'];
  }


                     if(isset($data['description'])){
    $cond.= " and r.description = ?";
    $param[] = $data['description'];
  }

                       if(isset($data['occupied'])){
    $cond.= " and r.occupied = ?";
    $param[] = $data['occupied'];
  }

                         if(isset($data['high_price'])){
    $cond.= " and r.price <= ?";
    $param[] = $data['high_price'];
  }

                           if(isset($data['low_price'])){
    $cond.= " and r.price >= ?";
    $param[] = $data['low_price'];
  }


  

  $SQL.=$cond;
  $SQL.=$limit;  


  $rooms = DB::select($SQL,$param);

  if($rooms){

     foreach($rooms as $key => $room){

        $images = Images::where('room_id' , $room->id)->get();


      $room_image = array();
      foreach ($images as $key => $value) {
      $room_image[$key] = url('/public/images/rooms/full') . '/' . $value->image;
     
        }
         $room->image[] = $room_image;


   }

  return $rooms;
  }

  else{
    return false;
  }

 
}



public static function detail($room_id){        
  $room_img_path = env("BASE_URL")."images/rooms/full/";  

  $room = DB::table('rooms as r')
            ->select('r.*', 'u.id as user_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->where('r.id', '=' , $room_id)
            ->get()->first(); 

  if($room){
      $images = Images::where('room_id' , $room_id)->get();
      $full_path_image = $room_img_path . $images;
      $room->images = $images;
    return $room; 
}else{
  return false;
}

}



 public static function MyfavouriteRooms($room_id){        
  $room_img_path = env("BASE_URL")."images/rooms/full/";  

  $room = DB::table('rooms as r')
            ->select('r.*', 'u.id as user_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->where('r.id', '=' , $room_id)
            ->get()->first(); 

  if($room){
     $images = Images::where('room_id' , $room->id)->get();
    foreach ($images as $key => $value) {
        $full_path_image = $room_img_path . $value->image;
      $room->images[] = $full_path_image;
    }

      
     
    return $room; 
}else{
  return false;
}

}


 public static function checkDeleteOldRooms(){
    $last2monthsroom = DB::select("SELECT * FROM rooms WHERE updated_at <= (NOW() - INTERVAL 2 MONTH)");

    if($last2monthsroom){
       foreach ($last2monthsroom as $key => $value) {

        $notify = array();
        $notify['user_id'] = $value->user_id;
        $notify['room_id'] = $value->id;
        $notify['mobile_target_id'] = $value->user_id;
        $notify['type'] = 'notify_owner';
        $notify['is_read'] = '0';
        $notify['message'] = "Please reactivate this post.";
        $notify['content_link'] = url('room/detail/' . $value->user_id);

        $notify_id = Notification::create($notify);

           $room_id = $value->id;
           $room = Room::find($room_id);
           if($room->deleted_at != NULL){
                       $room->delete();
           }
    }
  }else{
    return false;
  }

   

  }



}
