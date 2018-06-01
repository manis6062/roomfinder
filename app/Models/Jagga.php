<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Library\RoomFinderFunctions;
use Log; 
use Illuminate\Database\Eloquent\SoftDeletes; 

class Jagga extends Model
{  
    use SoftDeletes;
  protected $dates = ['created_at', 'updated_at', 'deleted_at'];
  protected $table = 'jaggas';
  protected $fillable = array( 
    'user_id','type',
    'phone_no','loc_lat','loc_lon','address',
    'price' , 'description' , 'sold');
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

  $SQL = "SELECT j.* FROM jaggas j inner join users u ON j.user_id = u.id where j.deleted_at IS NULL";

  $cond = "";
  // $where= " where j.deleted_at = NULL";
  // $SQL.=$where;
  $param = array();
  if(isset($data['type'])){
    $cond.= " and j.type = ?";
    $param[] = $data['type'];
  }
   

          if(isset($data['phone_no'])){
    $cond.= " and j.phone_no = ?";
    $param[] = $data['phone_no'];
  }

           if(isset($data['loc_lon'])){
    $cond.= " and j.loc_lon = ?";
    $param[] = $data['loc_lon'];
  }

             if(isset($data['loc_lat'])){
    $cond.= " and j.loc_lat = ?";
    $param[] = $data['loc_lat'];
  }

               if(isset($data['address'])){
    $cond.= " and j.address = ?";
    $param[] = $data['address'];
  }

                     if(isset($data['description'])){
    $cond.= " and j.description = ?";
    $param[] = $data['description'];
  }

                       if(isset($data['sold'])){
    $cond.= " and j.sold = ?";
    $param[] = $data['sold'];
  }

                          if(isset($data['high_price'])){
    $cond.= " and j.price <= ?";
    $param[] = $data['high_price'];
  }

                           if(isset($data['low_price'])){
    $cond.= " and j.price >= ?";
    $param[] = $data['low_price'];
  }

  

  $SQL.=$cond;
  $SQL.=$limit;  




  $jaggas = DB::select($SQL,$param);

  if($jaggas){

     foreach($jaggas as $key => $j){

        $images = Images::where('jagga_id' , $j->id)->get();
       $jagga_image = array();
      foreach ($images as $key => $value) {
      $jagga_image[$key] = url('/public/images/jaggas/full') . '/' . $value->image;
        }
      $j->image[] = $jagga_image;

   }

  return $jaggas;
  }

  else{
    return false;
  }

 
}

 public static function detail($jagga_id){        
  $room_img_path = env("BASE_URL")."images/jaggas/full/";  

  $jagga = DB::table('jaggas as j')
            ->select('j.*', 'u.id as user_id')
            ->leftJoin('users as u', 'u.id', '=', 'j.user_id')
            ->where('j.id', '=' , $jagga_id)
            ->get()->first(); 

  if($jagga){
      $images = Images::where('jagga_id' , $jagga->id)->get();
      $full_path_image = $room_img_path . $images;
      $jagga->images = $images;
    return $jagga; 
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
      $car->profile_pic = url('images/global/users/default-avataj.png');
    }
  }
  return $cars;
}

}
