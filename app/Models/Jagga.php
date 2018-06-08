<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Library\RoomFinderFunctions;
use Log; 
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Support\Str;

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


 public static function Myfavourite($jagga_id){        
  $room_img_path = env("BASE_URL")."images/jaggas/full/";  

  $jagga = DB::table('jaggas as j')
            ->select('j.*', 'u.id as user_id')
            ->leftJoin('users as u', 'u.id', '=', 'j.user_id')
            ->where('j.id', '=' , $jagga_id)
            ->get()->first(); 

  if($jagga){
     $images = Images::where('jagga_id' , $jagga->id)->get();
    foreach ($images as $key => $value) {
        $full_path_image = $room_img_path . $value->image;
      $jagga->images[] = $full_path_image;
    }

      
     
    return $jagga; 
}else{
  return false;
}

}


 public static function checkDeleteOldJaggas(){
    $last2monthsjagga = DB::select("SELECT * FROM jaggas WHERE updated_at <= (NOW() - INTERVAL 2 MONTH)");

    if($last2monthsjagga){
       foreach ($last2monthsjagga as $key => $value) {
        $notify = array();
        $notify['user_id'] = $value->user_id;
        $notify['jagga_id'] = $value->id;
        $notify['mobile_target_id'] = $value->user_id;
        $notify['type'] = 'notify_owner';
        $notify['is_read'] = '0';
        $notify['message'] = "Please reactivate this post.";
        $notify['content_link'] = url('room/detail/' . $value->user_id);

        $notify_id = Notification::create($notify);


           $jagga_id = $value->id;
           $jagga = Jagga::find($jagga_id);
           if($jagga->deleted_at != NULL){
             $jagga->delete();
           }
          
    }
  }else{
    return false;
  }

   

  }



}
