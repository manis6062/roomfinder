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

                   if(isset($data['price'])){
    $cond.= " and r.price = ?";
    $param[] = $data['price'];
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

      foreach ($images as $key => $value) {
      $room_image[$key] = url('/public/images/room/full') . '/' . $value->image;
      $room->image = $room_image;
        }

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
      $images = Images::where('room_id' , 3)->get();
      $full_path_image = $room_img_path . $images;
      $room->images = $images;
    return $room; 
}else{
  return false;
}

}





  public static function searchbyplace($data){
  $lang = \App::getLocale(); 
  $user_img_path = env("BASE_URL")."images/users/full/";
  $car_img_path = env("BASE_URL")."images/cars/full/";
  $limit = $fields = "";
  $available_cars = array(); 
  $fields = "";
  if($lang == 'en'){
   // $fields.="vt.title_eng as vehicle_type_title";
  }else{
   // $fields.="vt.title_thai as vehicle_type_title";
  }
  //DB::connection()->enableQueryLog();
  $cars = Car::from('cars as c')
  ->distinct()
  ->select('c.id','c.user_id','c.make_id','c.model_id','c.car_plate_number','c.is_instant_booking_enabled','c.vehicle_type_id','c.estimated_value_id','c.total_seats','c.year_made','c.enable_custom_mileage','c.mileage_limit','c.mileage_limit_week','c.mileage_limit_month','c.mileage_used','c.transmission','c.loc_lat','c.loc_lon','c.address','c.description','c.enable_custom_price','c.custom_price','c.custom_price_week','c.custom_price_month','c.offers_delivery','c.delivery_fee','c.pickup_instruction','c.availability_type','c.step_completed','c.status','c.rejection_reason','u.first_name','u.last_name','u.first_name','cp.photo','u.profile_pic','cm.car_make_url','cd.car_model_url','cm.title_eng as make_title','cd.title_eng as model_title'
  )
        ->join('users as u','u.id','=','c.user_id') 
        ->join('car_estimated_values as cev','cev.id','=','c.estimated_value_id')
        ->join('car_make as cm','cm.id','=','c.make_id')
        ->join('car_features as cf','cf.car_id','=','c.id')
        
        ->join('car_models as cd','cd.id','=','c.model_id') 
        ->join('car_photos as cp','cp.car_id','=','c.id')    
        ->where(function($q) use ($data,$available_cars) {
              $q->Where('c.status','=',  'listed' ); 
              $q->Where('c.deleted','=',  0 ); 
               if(isset($data['min_latitude']) and isset($data['max_latitude']) and isset($data['min_longitude']) and isset($data['max_longitude'])){
                    $q->Where('c.loc_lat','>=', $data['min_latitude'] );
                    $q->Where('c.loc_lat','<=', $data['max_latitude'] );
                    $q->Where('c.loc_lon','>=',  $data['min_longitude'] ); 
                    $q->Where('c.loc_lon','<=',  $data['max_longitude'] ); 
                    }
            })
        ->groupBy('c.id')
        ->orderBy('c.id','desc')
        ->paginate(21);
  $car_array = array();
  $car_photos = array();
  foreach($cars as $car){
    $car->total_rentals = self::getTotalRentals($car->id);  
    $car->rating_given = self::getTotalRatings($car->id); 

    if($car->photo!=''){
      $car->photo = $car_img_path.$car->photo;
    }else{
      $car->photo = url('images/global/cars/car_default.jpg');
    }
    if($car->profile_pic!=''){
      $car->profile_pic = $user_img_path.$car->profile_pic;
    }else{
      $car->profile_pic = url('images/global/users/default-avatar.png');
    }
  }
  return $cars;
}

}
