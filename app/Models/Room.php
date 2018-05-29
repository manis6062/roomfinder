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


public static function searchAndroid($data){ // for mobile search, ios and android
 /* Log::info('storing Room search requestttt'); 
  Log::info($data); */

  $lang = \App::getLocale();
  $user_img_path = env("BASE_URL")."images/users/full/";
  $car_img_path = env("BASE_URL")."images/cars/thumb/";
  //$user_img_path = "images/users/full/";
  //$car_img_path = "images/cars/full/";
  $limit = $fields = "";
  //first find the available cars in given from date and to date if found in search criteria
  $available_cars = array();
  if(isset($data['from_date']) and isset($data['to_date'])){    
     $car_ids = DB::table('cars')->leftJoin('car_estimated_values','car_estimated_values.id','=','cars.estimated_value_id')->select('cars.id as id','enable_custom_price','custom_price','car_estimated_values.default_price_per_day as dppd')->where('cars.status','listed')->get(); 
     if($car_ids){
        foreach($car_ids as $c){



          if(self::canBook($c->id,$data['from_date'],$data['to_date'])){
            //check if the price is also in the parameter
            if(isset($data['price_per_day']) and $data['price_per_day'] > 0){
              if($c->enable_custom_price == 1){
                $car_price = $c->custom_price;
              }else{
                $car_price = $c->dppd;
              }
              if($car_price > $data['price_per_day']  ){
                
                continue;
              }
            }

            $available_cars[] = $c->id;
          }
        } 
      }
  }

  if($lang == 'en'){
    $fields.="cm.title_eng as make_title,cd.title_eng as model_title,vt.title_eng as vehicle_type_title";
  }else{
    $fields.="cm.title_eng as make_title,cd.title_eng as model_title,vt.title_thai as vehicle_type_title";
  }   

  if( isset($data['per_page']) and isset($data['page_number']) ){

    $offset = $data['per_page'] * ($data['page_number']-1);
    $per_page = $data['per_page'];
    $limit.=" LIMIT {$offset}, {$per_page}";
  }   

  $SQL = "SELECT DISTINCT c.id,c.user_id,c.make_id,c.model_id,c.vehicle_type_id,c.estimated_value_id,c.year_made,c.enable_custom_mileage,cev.default_price_per_day,
  c.loc_lat,c.loc_lon,c.enable_custom_price,c.is_instant_booking_enabled,
  c.custom_price,c.step_completed,c.status,cp.photo as car_image,
  u.first_name as user_first_name,u.last_name as user_last_name,
  u.profile_pic
  as user_profile_pic,"
  .$fields.",
  cev.default_price_per_day as car_estimated_price
  FROM cars c
  inner join users u ON c.user_id = u.id
  left join car_estimated_values cev ON cev.id = c.estimated_value_id
  left join car_make cm ON cm.id = c.make_id        
  left join car_models cd ON cd.id = c.model_id
  left join car_features cf ON c.id = cf.car_id
  left join features f on f.id = cf.feature_id
  left join car_photos cp on cp.car_id = c.id
  left join vehicle_types vt on vt.id = c.vehicle_type_id";

  $cond = "";
  $where= " where c.status = 'listed'";
  if(count($available_cars) > 0){
     $available_cars_string = implode(",",$available_cars);  
     $where.=" and c.id IN ($available_cars_string)";
  }
  if(isset($data['include_status']) and $data['include_status'] == 'all'){

    $where = " where 1";
  }
  $where.=" and c.deleted = 0"; 
  $SQL.=$where;
  $param = array();
  if(isset($data['offers_delivery']) and isset($data['delivery_fee'])){
    $cond.= " and c.offers_delivery=? and c.delivery_fee <= ?";
    $param[] = "1";
    $param[] = "{$data['delivery_fee']}";
  }

  if(isset($data['transmission'])){ 
    $cond.=" and c.transmission IN('".implode("','",$data['transmission'])."')";
  }
  if(isset($data['mileage_limit'])){
    $cond.= " and c.mileage_limit >= ?";
    $param[] = $data['mileage_limit'];
  }

  if(isset($data['vehicle_type_id'])){  
    $cond.=" and c.vehicle_type_id in (".implode(',',$data['vehicle_type_id']).")"; 
  }
  if(isset($data['instant_booking'])){  
    $cond.= " and c.is_instant_booking_enabled=?";
    $param[] = $data['instant_booking'];
  }
  if(isset($data['make_id'])){

    $cond.=" and c.make_id in (".implode(',',$data['make_id']).")"; 
   /* $data['make_id'] = array_map('intval',$data['make_id']); 
    $cond.= " and c.make_id in (?)";
    
    $mk = implode("','",$data['make_id']);
   // $mk = join("','",$data['make_id']);
    $param[] = $mk;       */
  }
  if(isset($data['user_id'])){
             // echo $data['user_id']; die;
    $cond.= " and c.user_id=?";
    $param[] = $data['user_id'];
  }
  if(isset($data['bypass_user_id'])){
    $cond.= " and c.user_id <> ?";
    $param[] = $data['bypass_user_id'];
  }



  if(isset($data['feature_id'])){
    //$fs = implode(',',$data['features']);
    $cond.=" and cf.feature_id in (".implode(',',$data['feature_id']).")"; 
   //$cond.= " and cf.feature_id IN (?)";
    //$param[] = $fs;
  }
  $provided_location_info = false;
  if(isset($data['min_latitude']) and isset($data['max_latitude']) and isset($data['min_longitude']) and isset($data['max_longitude'])){
    $cond.= " and c.loc_lat >= ? and c.loc_lat <= ? and c.loc_lon >= ? and c.loc_lon <= ? ";
    $param[] = $data['min_latitude'];
    $param[] = $data['max_latitude'];
    $param[] = $data['min_longitude'];
    $param[] = $data['max_longitude'];
    $provided_location_info = true;
  }



  $SQL.=$cond;

  $SQL.=" Group by c.id";
  if($provided_location_info){ // if lat and lon is provided, need to sort by nearest first
     $sf = 3.14159 / 180; // scaling factor
     $lat_sf = $data['loc_lat'] * $sf; 
     $lon_sf = $data['loc_lon'] * $sf; 
     $lon = $data['loc_lon'];
     $SQL.=" ORDER BY ACOS(SIN(c.loc_lat*$sf)*SIN($lat_sf) + COS(c.loc_lat*$sf)*COS($lat_sf)*COS((c.loc_lon-$lon)*$sf))";
  }

  $SQL.=$limit;  
 // echo $SQL; 
  //print_r($param); 
 // DB::enableQueryLog();
  $cars = DB::select($SQL,$param);
  //dd(DB::getQueryLog());
  $car_array = array();
  $car_photos = array();
  $final_cars = array();
  //echo "cars"; 
  //dd($cars);

  foreach($cars as $car){
    //echo "die"; die;

    
    $car_price = 0;
    //dd("here");
    if(isset($data['price_per_day']) and $data['price_per_day'] > 0){
      if($car->enable_custom_price == 1){
        $car_price = $car->custom_price;
      }else{
        $car_price = $car->default_price_per_day;
      }
      if($car_price > $data['price_per_day']  ){
        
        //continue;
      }
      else {
        $car->price_per_day = $car_price;
      }
    }
    if($car->car_image!=''){
      $car->car_image = $car_img_path.$car->car_image;
    }else{
      $car->car_image = url('images/global/cars/car_default.jpg');
    }
    // if($car->user_profile_pic!=''){
    //   $car->user_profile_pic = $user_img_path.$car->user_profile_pic;
    // }else{
    //   $car->user_profile_pic = url('images/global/users/default-avatar.png');
    // }


      $user = User::find($car->user_id);


           if($user->override_name_pic == 'n'){
               if($car->user_profile_pic!=''){
      $car->user_profile_pic = $user_img_path.$car->user_profile_pic;
    }else{
      $car->user_profile_pic = url('images/global/users/default-avatar.png');
    }
            }else{
                $setting = \App\Models\Setting::firstOrFail();
                $path = env("BASE_URL")."images/users/thumb/";
              $car->user_profile_pic = $path . $setting->default_profile_pic; 
              $car->user_first_name = $setting->default_first_name;
              $car->user_last_name = $setting->default_last_name;
            }


    unset($car->make_id); 
    unset($car->model_id); 
    unset($car->vehicle_type_id);     
    unset($car->estimated_value_id); 
    unset($car->enable_custom_mileage);  
    unset($car->vehicle_type_title);  
    unset($car->default_price_per_day); 
    $final_cars[] = $car;  
  }
  
  return $final_cars;
}

public static function search($data){
  $lang = \App::getLocale();
  $user_img_path = env("BASE_URL")."images/users/full/";
  $car_img_path = env("BASE_URL")."images/cars/full/";
  $limit = $fields = "";
  //first find the available cars in given from date and to date if found in search criteria
  $available_cars = array();
  if(isset($data['from_date']) and isset($data['to_date'])){    
     $car_ids = DB::table('cars')->select('id')->where('status','listed')->get(); 
     if($car_ids){
        foreach($car_ids as $c){
          if(self::canBook($c->id,$data['from_date'],$data['to_date'])){
            $available_cars[] = $c->id;
          }
        } 
      }
  }

  if($lang == 'en'){
    $fields.="cm.title_eng as make_title,cd.title_eng as model_title,vt.title_eng as vehicle_type_title";
  }else{
    $fields.="cm.title_eng as make_title,cd.title_eng as model_title,vt.title_thai as vehicle_type_title";
  }   

  if( isset($data['per_page']) and isset($data['page_number']) ){

    $offset = $data['per_page'] * ($data['page_number']-1);
    $per_page = $data['per_page'];
    $limit.=" LIMIT {$offset}, {$per_page}";
  }   

  $SQL = "SELECT DISTINCT c.id,c.user_id,c.make_id,c.model_id,c.car_plate_number,c.vehicle_type_id,c.estimated_value_id,c.is_instant_booking_enabled,
  c.total_seats,c.year_made,c.enable_custom_mileage,c.mileage_limit,c.mileage_limit_week,c.mileage_limit_month,
  c.mileage_used,c.transmission,c.loc_lat,c.loc_lon,c.address,c.description,c.enable_custom_price,
  c.custom_price,c.custom_price_week,c.custom_price_month,c.offers_delivery,c.delivery_fee,c.pickup_instruction,
  c.availability_type,c.step_completed,c.status,c.rejection_reason,u.first_name,u.last_name,cp.photo as car_image,
  u.first_name as user_first_name,u.last_name as user_last_name,cm.car_make_url,cd.car_model_url,
  u.profile_pic
  as user_profile_pic,"
  .$fields.",
  cev.default_price_per_day as car_estimated_price,cev.title as car_estimated_price_title
  FROM cars c
  inner join users u ON c.user_id = u.id
  left join car_estimated_values cev ON cev.id = c.estimated_value_id
  left join car_make cm ON cm.id = c.make_id        
  left join car_models cd ON cd.id = c.model_id
  left join car_features cf ON c.id = cf.car_id
  left join features f on f.id = cf.feature_id
  left join car_photos cp on cp.car_id = c.id
  left join vehicle_types vt on vt.id = c.vehicle_type_id";

  $cond = "";
  $where= " where c.status = 'listed'";
  if(count($available_cars) > 0){
     $available_cars_string = implode(",",$available_cars);  
     $where.=" and c.id IN ($available_cars_string)";
  }
  if(isset($data['include_status']) and $data['include_status'] == 'all'){
    $where = " where 1";
  }
  $where.=" and c.deleted = 0"; 
  $SQL.=$where;
  $param = array();
  if(isset($data['offers_delivery']) and isset($data['delivery_fee'])){
    $cond.= " and c.offers_delivery=? and c.delivery_fee <= ?";
    $param[] = "1";
    $param[] = "{$data['delivery_fee']}";
  }

  if(isset($data['transmission'])){ 
    $cond.=" and c.transmission IN('".implode("','",$data['transmission'])."')";
  }
  if(isset($data['mileage_limit'])){
    $cond.= " and c.mileage_limit >= ?";
    $param[] = $data['mileage_limit'];
  }
  if(isset($data['vehicle_type_id'])){  
    $cond.=" and c.vehicle_type_id in (".implode(',',$data['vehicle_type_id']).")"; 
  }
  if(isset($data['make_id'])){
    $cond.=" and c.make_id in (".implode(',',$data['make_id']).")"; 
   /* $cond.= " and c.make_id in (?)";
    $mk = implode(',',$data['make_id']);
    $param[] = $mk;    */   
  }
  if(isset($data['user_id'])){
             // echo $data['user_id']; die;
    $cond.= " and c.user_id=?";
    $param[] = $data['user_id'];
  }
  if(isset($data['bypass_user_id'])){
    $cond.= " and c.user_id <> ?";
    $param[] = $data['bypass_user_id'];
  }

  if(isset($data['feature_id'])){
    $cond.=" and cf.feature_id in (".implode(',',$data['feature_id']).")"; 
    /*$fs = implode(',',$data['features']);
    $cond.= " and cf.feature_id IN (?)";
    $param[] = $fs;*/
  }
  if(isset($data['min_latitude']) and isset($data['max_latitude']) and isset($data['min_longitude']) and isset($data['max_longitude'])){
    $cond.= " and c.loc_lat >= ? and c.loc_lat <= ? and c.loc_lon >= ? and c.loc_lon <= ? ";
    $param[] = $data['min_latitude'];
    $param[] = $data['max_latitude'];
    $param[] = $data['min_longitude'];
    $param[] = $data['max_longitude'];
  }
  $SQL.=$cond;
  $SQL.=" Group by c.id";
  $SQL.=$limit;  
 
  $cars = DB::select($SQL,$param);

  $car_array = array();
  $car_photos = array();
  $final_cars = array();
  foreach($cars as $car){
    if(isset($data['price_per_day']) and $data['price_per_day'] > 0){
      if($car->enable_custom_price == 1){
        $car_price = $car->custom_price;
      }else{
        $car_price = $car->car_estimated_price;
      }
      if($car_price > $data['price_per_day']  ){        
        continue;
      }
      else {
        $car->price_per_day = $car_price;
      }
    }

    $car->total_rentals = self::getTotalRentals($car->id);  
    $car->rating_given = self::getTotalRatings($car->id); 

    if($car->car_image!=''){
      $car->car_image = $car_img_path.$car->car_image;
    }else{
      $car->car_image = url('images/global/cars/car_default.jpg');
    }


      $user = User::find($car->user_id);


           if($user->override_name_pic == 'n'){
               if($car->user_profile_pic!=''){
      $car->user_profile_pic = $user_img_path.$car->user_profile_pic;
    }else{
      $car->user_profile_pic = url('images/global/users/default-avatar.png');
    }
            }else{
                $setting = \App\Models\Setting::firstOrFail();
                $path = env("BASE_URL")."images/users/thumb/";
              $car->user_profile_pic = $path . $setting->default_profile_pic; 
                 $car->user_first_name = $setting->default_first_name;
              $car->user_last_name = $setting->default_last_name;
            }




    $final_cars[] = $car; 
  }
  return $final_cars;
}

public static function advancesearch($data){
//dd($data);
  //  $path = $_SERVER['DOCUMENT_ROOT'].'/'.'wp-content';
  // dd($path);
  $setting = DB::table('setting')->select('mileage_limit_per_day')->first();
  //dd($setting); 
  $lang = \App::getLocale();
  $user_img_path = env("BASE_URL")."images/users/full/";
  $car_img_path = env("BASE_URL")."images/cars/thumb/";
  $available_cars = array();
  if(isset($data['from_date']) and isset($data['to_date'])){    
     $car_ids = DB::table('cars')->select('id')->where('status','listed')->get(); 
     if($car_ids){
        foreach($car_ids as $c){
          if(self::canBook($c->id,$data['from_date'],$data['to_date'])){
            $available_cars[] = $c->id;
          }
        } 
      }
  }
$fields = "";
  if($lang == 'en'){
    $fields.="vt.title_eng as vehicle_type_title";
  }else{
    $fields="vt.title_thai as vehicle_type_title";
  }
  //DB::enableQueryLog();


   $sf = 3.14159 / 180; // scaling factor
   $lat_sf = $data['loc_lat'] * $sf; 
   $lon_sf = $data['loc_lon'] * $sf; 
   $lon = $data['loc_lon'];
   
  
  $cars = Car::from('cars as c')
  //->distinct('c.id')
  ->where(function($q) use ($data) {
      $q->whereBetween('c.loc_lat', [$data['min_latitude'] , $data['max_latitude']]);
      $q->whereBetween('c.loc_lon', [$data['min_longitude'], $data['max_longitude']]);
    })  
  ->select(DB::raw("ACOS(SIN(c.loc_lat*$sf)*SIN($lat_sf)+COS(c.loc_lat*$sf)*COS($lat_sf)*COS((c.loc_lon-$lon)*$sf)) as distance ,c.id,c.user_id,c.make_id,c.model_id,c.car_plate_number,c.vehicle_type_id,c.estimated_value_id,c.total_seats,c.year_made,c.is_instant_booking_enabled,
    c.enable_custom_mileage,c.mileage_limit,c.mileage_limit_week,c.mileage_limit_month,c.mileage_used,c.transmission,c.loc_lat,c.loc_lon,
    c.address,c.description,c.enable_custom_price,c.custom_price,c.custom_price_week,c.custom_price_month,c.offers_delivery,c.delivery_fee,
    c.pickup_instruction,c.availability_type,c.step_completed,c.status,c.rejection_reason,u.first_name,u.last_name,u.first_name,cp.photo,
    u.profile_pic,cm.car_make_url,cd.car_model_url,cev.default_price_per_day as car_estimated_price,cev.title as car_estimated_price_title,
    cm.title_eng as make_title,cd.title_eng as model_title"))
        ->join('users as u','u.id','=','c.user_id') 
        ->join('car_estimated_values as cev','cev.id','=','c.estimated_value_id')
        ->join('car_make as cm','cm.id','=','c.make_id')
        ->join('car_features as cf','cf.car_id','=','c.id')
        
        ->join('car_models as cd','cd.id','=','c.model_id') 
        ->join('car_photos as cp','cp.car_id','=','c.id')    
        ->where(function($q) use ($data,$available_cars,$setting) {
                 //$q->where('cp.photo')->first();
                 $q->Where('c.status','=',  'listed' ); 
                 $q->Where('c.deleted','=',  0 ); 
                 $ids = array();
                if(isset($data['features'])){
                    foreach($data['features'] as $key => $val){
                      $ids[] = (int)$val; 
                    }
                    $q->WhereIn('cf.feature_id', $ids );
                  }
                if(isset($data['make_id'])){
                    $q->WhereIn('c.make_id', $data['make_id'] );      
                  }
                  if(isset($data['instant_booking'])){
                    $q->Where('c.is_instant_booking_enabled', $data['instant_booking'] );      
                  }
                if(isset($data['vehicle_type_id'])){

                    $q->WhereIn('c.vehicle_type_id', $data['vehicle_type_id'] );
                  }

                if(isset($data['offers_delivery']) and $data['delivery_fee'] > 0){
                  $q->Where('offers_delivery',$data['offers_delivery'])->where('delivery_fee','<=',$data['delivery_fee']);
                }
                $mileage_limit  = $setting->mileage_limit_per_day; 
                if(isset($data['mileage_limit']) and $data['mileage_limit'] > $mileage_limit){
                    $mileage_limit = $data['mileage_limit'];                     
                    $q->Where('c.mileage_limit', '>=', $data['mileage_limit'] );                  
                  }
                if(isset($data['transmission'])){ 
                    $q->WhereIn('c.transmission', $data['transmission'] );
                }                
                /*$q->Where(function($q) use ($data,$available_cars) {
                  if(isset($data['price']) and 'c.enable_custom_price' == 1){
                    $q->OrWhere('c.custom_price', '<',$data['price'] );
                  }
                  if(isset($data['price']) and 'c.enable_custom_price' == 0){
                    $q->OrWhere('cev.default_price_per_day', '<',$data['price'] );
                  }
                });*/

                if(count($available_cars) > 0){
                     $q->WhereIn('c.id', $available_cars );
                }
               
            })
        ->groupBy('c.id')->orderBy('distance','asc')->groupBy('c.id')->distinct()->paginate(100);
       $queries = DB::getQueryLog();
    //   dd($cars);
     //dd($queries);
  $car_array = array();
  $car_photos = array();
  $final_Room = array();
  foreach($cars as $car){

    
    $car->distance = RoomFinderFunctions::getDistanceDifference(
        [
          'source_lat' => $car->loc_lat,
          'source_lon' => $car->loc_lon,
          'desti_lat' => $data['loc_lat'],
          'desti_lon' => $data['loc_lon'],
        ],false
      );
    if(isset($data['price_per_day']) and $data['price_per_day']!=0){
    
      $car_price = 0; 
      if($car->enable_custom_price == 1){
        $car_price = $car->custom_price;
      }else{
        $car_price = $car->car_estimated_price;
      }      
      if($car_price > $data['price_per_day']){
        continue;           
      }
    }


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
    $final_car[] = $car; 

  }
  //die;

  //dd($cars); 
  return $final_car;
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
