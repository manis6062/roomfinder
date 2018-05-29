<?php namespace App\Http\Controllers\Api;
  use Illuminate\Http\Request;

  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use App\Library\RoomFinderFunctions;
  use App\Models\Room;
  use App\Models\User;
  use App\Models\Images;
  use Lang,DB,Auth;
  class RoomsController extends Controller
  {


       public function addRoom(Request $request){
        $input = $request->all();
        $details = array();
        $v = \Validator::make($input,   [ 
                //about
         'user_id' => 'required|numeric|exists:users,id', 
         'type' => 'required',
         'no_of_floor' =>'required',
         'no_of_room' =>'required',
          'kitchen' =>'required',
          'parking' =>'required',
           'restroom' =>'required',
            'phone_no' =>'required',
             'loc_lat' =>'required',
              'loc_lon' =>'required',
               'address' =>'required',
                'image' =>'required',
                'preference' =>'required',
                'price' =>'required',
                'description' => 'required',
                 'occupied' => 'required'
         ] );        
        if ($v->fails())
        {   
          $msg = array();
          $messages = $v->errors();           
          foreach ($messages->all() as $message) {
            return \Response::json(array(  'error' => true,  'message' => $message ) );
          }  
        }

        if(!is_array($request->image)){
              $message = "";
              return \Response::json(array(  'error' => true,  'message' => Lang::get('messages.image_array') ) );
        }


        $user = User::find($input['user_id']);
         $array['user_id'] = $input['user_id'];
         $array['type'] = $input['type'];
         $array['no_of_floor'] = $input['no_of_floor'];
          $array['no_of_room'] = $input['no_of_room'];
          $array['kitchen'] = $input['kitchen'];
         $array['parking'] = $input['parking'];
         $array['restroom'] = $input['restroom'];
         $array['phone_no'] = $input['phone_no'];
         $array['loc_lat'] = $input['loc_lat'];
         $array['loc_lon'] = $input['loc_lon'];
         $array['description'] = $input['description'];
          $array['address'] = $input['address'];
           $array['preference'] = $input['preference'];
            $array['price'] = $input['price'];
             $array['occupied'] = $input['occupied'];
          $room_id = Room::create($array)->id;

          if(count($request->image) > 1){
               $this->uploadMultipleImages($request , $room_id);
          }else{
            $path_to_save = base_path() . '/public/images/rooms/';      
    $input_field_name = 'image';        
    $image = app('App\Http\Controllers\Api\GalleryController')->saveSingleImage($request,$path_to_save,$input_field_name);
          DB::table('images')->insert(['room_id' => $room_id, 'image' => $image]);

          }
         
         return \Response::json(array(  'error' => false,  'room_id' => $room_id , 'created_at' =>date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')) );   
       }



         public function detail(Request $request){
   $input = $request->all();

   $details = array();
   $v = \Validator::make($input,   [ 
    'id' => 'required|numeric|exists:jaggas,id',                 
    ] );
   if ($v->fails())
   {   
    $msg = array();
    $messages = $v->errors();           
    foreach ($messages->all() as $message) {
      return \Response::json(array(  'error' => true,  'message' => $message ) );
    }  
  }   
  $details = Room::detail($input['id']);
  if($details){
    return \Response::json(array(  'error' => false,   'result' => $details  ) );
  }else{
    return \Response::json(array(  'error' => true,   'message' => Lang::get('messages.resultnotfound')  ) );
  }
  }


         public function updateRoom(Request $request){

    $input = $request->all();    
    
    $v = \Validator::make($input,   [ 
                //about
      'user_id' => 'required|numeric|exists:users,id', 
         'type' => 'required',
         'no_of_floor' =>'required',
         'no_of_room' =>'required',
          'kitchen' =>'required',
          'parking' =>'required',
           'restroom' =>'required',
            'phone_no' =>'required',
             'loc_lat' =>'required',
              'loc_lon' =>'required',
               'address' =>'required',
                'preference' =>'required',
                'price' =>'required',
                'description' => 'required',
                 'occupied' => 'required',
                 'room_id' => 'required'

     ] );
  if ($v->fails())
  {   
    $msg = array();
    $messages = $v->errors();           
    foreach ($messages->all() as $message) {
      return \Response::json(array(  'error' => true,  'message' => $message ) );
    }  
  } 

  $room = Room::find($input['room_id']);
  $room_image_path = base_path() . '/public/images/rooms/';

  if($room){  
     unset($input['room_id']);
     unset($input['image']);
  $room->where('id',$room->id)->update($input);
   $images = Images::where('room_id' , $room->id);

  if($request->image){
    foreach ($images->get() as $key => $value) {
    $full_path = $room_image_path.'full/'.$value->image;
    $mid_path = $room_image_path.'mid/'.$value->image; 
    $thumb_path = $room_image_path.'thumb/'.$value->image; 
      @unlink($full_path);
       @unlink($mid_path); 
      @unlink($thumb_path); 
    }
    $delete = $images->delete();

        if(count($request->image) > 1){
               $this->uploadMultipleImages($request , $request->room_id);
          }else{
    $input_field_name = 'image';        
    $image = app('App\Http\Controllers\Api\GalleryController')->saveSingleImage($request,$room_image_path,$input_field_name);
          DB::table('images')->insert(['room_id' => $request->room_id, 'image' => $image]);

          }

  }


      return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );
  }
  else{
    return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.invalid_room_id') ) );
  }
 


      }



       public function uploadMultipleImages(Request $request , $id){
          $input = $request->all();  
      $files = count($request->image) - 1;

      if(count($request->image) == 0){
        return array(  'error' => true,  'message' => Lang::get('messages.photo_is_required') ) ; 
      }
      foreach(range(0, $files) as $index) {

          $rules['image.' . $index] = 'required|image|max:5120';
      }
      $v = \Validator::make($input, $rules);        
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return array(  'error' => true,  'message' => $message );  
        }  
      }

      $data['path_to_save'] = base_path() . '/public/images/rooms/';   
      $data['input_field_name'] = 'image';       
      $data['request'] = $request; 

      $images = app('App\Http\Controllers\Api\GalleryController')->saveImages($data);

      foreach($images as $image){   
       DB::table('images')->insert(['room_id' => $id, 'image' => $image , 'created_at' =>date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')]);
             }  
  }



    public function search(Request $request){
     $input = $request->all();     
     $v = \Validator::make($input,[    
      'user_id' =>'numeric|exists:users,id',  
      'from_date' => 'numeric',
      'to_date' => 'numeric',
      'per_page' =>'numeric',
      'page_number' =>'numeric',
      'delivery_fee' => 'required_if:offers_delivery,"true"'              
      ]);
     if ($v->fails())
     {   
      $msg = array();
      $messages = $v->errors();           
      foreach ($messages->all() as $message) {
                     // array_push($msg,$message);

        return \Response::json(array(  'error' => true,  'message' => $message ) );
      }               

    }  
    if(isset($input['to_date']) and isset($input['from_date'])){
      if($input['to_date'] < $input['from_date']){
        return \Response::json(array(  'error' => true,  'message' => "invalid date range" ) );
      }
    } 
    if(isset($input['loc_lat']) and isset($input['loc_lon'])){
      $lat_lon_values = RoomFinderFunctions::get_nearby($input['loc_lat'],$input['loc_lon']);
      $input['max_latitude'] = $lat_lon_values['max_latitude'];
      $input['min_latitude'] = $lat_lon_values['min_latitude'];
      $input['max_longitude'] = $lat_lon_values['max_longitude'];
      $input['min_longitude'] = $lat_lon_values['min_longitude'];
    }

    if($request->from_android and $request->from_android == 1){
    
      $result = Car::searchAndroid($input);
    }else{
      //echo "in ios"; die;
      $result = Car::search($input);  
    }
    //$result = Car::searchAndroid($input);
    if($result){
      if(!isset($input['page_number'])){
        $input['page_number'] = 1;
      }
      return \Response::json(array(  'error' => false, 'page_number' => ($input['page_number']+1), 'result' => $result  ) );
    }else{
                  //echo "jere"; die;
     return \Response::json(array(  'error' => true,   'message' => Lang::get('messages.resultnotfound')  ) );
   }
  }




 
    public function changeStatus(Request $request){
      $input = $request->all();
      $uid = $input['user_id'];
      $details = array();
      $v = \Validator::make($input,   [ 
                //about
       'user_id' => 'required|exists:users,id',
       'car_id' => 'required|numeric|exists:cars,id,user_id,'.$uid, 
       'status' =>'required|in:listed,notlisted'
       ] );        
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      } 

      $car = Car::where('id',$input['car_id'])->where('user_id',$input['user_id'])->first();
      if($car){
        if($car->status == 'listed' or $car->status == 'notlisted'){
          $car->where('id',$car->id)->update(array('status' => $input['status']));
        }
        return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );
      }else{
        return \Response::json(array(  'error' => true,  'message' => Lang::get('messages.error') ) );
      }   

    }
    public function getDeliveryPricingList(Request $request){
      
      return \Response::json(array(  'error' => false,  'result' => RoomFinderFunctions::getDeliveryPriceList() ) );
        
    }

    public function deleteCarPhotos(Request $request){
      $input = $request->all();
      $v = \Validator::make($input,   [ 
                //about
       'user_id' => 'required|exists:cars,user_id',
       'car_id' => 'required|numeric|exists:cars,id', 
       'photo_id' => 'required|numeric', 

       ] );        
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }
      $total_images = CarImagesModel::where('car_id',$input['car_id'])->count();
      if($total_images > 1){     
        CarImagesModel::find($input['photo_id'])->delete();
        return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );  
      }else{
        return \Response::json(array(  'error' => true,  'message' => Lang::get('You cannot delete all images') ) );
      }        
    }
    public function updateCarDeliveryOption(Request $request){

      $input = $request->all();
      $v = \Validator::make($input,   [ 
                //about
       'user_id' => 'required|exists:cars,user_id',
       'car_id' => 'required|numeric|exists:cars,id', 
       'offers_delivery' =>'required|in:0,1',
       'delivery_fee' =>'required_if:offers_delivery,1|numeric',

       ] );        
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }

      $car = Car::find($input['car_id']);
      $car->offers_delivery = $input['offers_delivery'];
      if($car->offers_delivery == 1){
        $car->delivery_fee = $input['delivery_fee'];
      }else{
        $car->delivery_fee = 0;
      }
      $car->save();
      return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );

    }

     public function deleteRoom(Request $request){
      $input = $request->all();
      $v = \Validator::make($input,   [ 
                //about
       'user_id' => 'required|exists:users,id',    
       'room_id' => 'required|numeric|exists:rooms,id',
       ] );        
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }

      $room = Room::find($request->room_id);
      $room_image_path = base_path() . '/public/images/rooms/';   

      if($room){
              $room->delete();
                 $images = Images::where('room_id' , $room->id);

  if($images){
    foreach ($images->get() as $key => $value) {
    $full_path = $room_image_path.'full/'.$value->image;
    $mid_path = $room_image_path.'mid/'.$value->image; 
    $thumb_path = $room_image_path.'thumb/'.$value->image; 
      @unlink($full_path);
       @unlink($mid_path); 
      @unlink($thumb_path); 
    }
    $delete = $images->delete();

      return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );
    }
      }else{
         return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.invalid_room_id') ) );
      }

   
}

    public function updateMileageOption(Request $request){

      $input = $request->all();
      $v = \Validator::make($input,   [ 
                //about
       'user_id' => 'required|exists:cars,user_id',
       'car_id' => 'required|numeric|exists:cars,id', 
       'enable_custom_mileage' =>'required|in:0,1',
       'mileage_limit' =>'required_if:enable_custom_mileage,1|numeric',
       'mileage_limit_week' =>'required_if:enable_custom_mileage,1|numeric',
       'mileage_limit_month' =>'required_if:enable_custom_mileage,1|numeric',

       ] );        
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }

      $car = Car::find($input['car_id']);
      $car->enable_custom_mileage = $input['enable_custom_mileage'];
      if($car->enable_custom_mileage == 1){
        $car->mileage_limit = $input['mileage_limit'];
        $car->mileage_limit_week = $input['mileage_limit_week'];
        $car->mileage_limit_month = $input['mileage_limit_month'];        
      }else{
        $car->enable_custom_mileage = 0;
        $car->mileage_limit = 0;
        $car->mileage_limit_week = 0;
        $car->mileage_limit_month = 0;
      }
      $car->save();
      return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );

    }

    public function addCarPhoto(Request $request){
      $input = $request->all();
      $v = \Validator::make($input,   [ 
                //about
       'user_id' => 'required|exists:cars,user_id',
       'car_id' => 'required|numeric|exists:cars,id', 
       'photo' =>'image|max:5120'
       ] );        
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }
      $total_photos = DB::table('car_photos')->where('car_id',$input['car_id'])->count();
      if($total_photos >= 16){
        return \Response::json(array(  'error' => true,  'message' => Lang::get('messages.nomorephotos') ) );
      }
      $path_to_save = base_path() . '/images/cars/';    
      $input_field_name = 'photo';       
      $data['car_id'] = $input['car_id'];
      $image = app('App\Http\Controllers\Api\V1\GalleryController')->saveImage($request,$path_to_save,$input_field_name);
      $time = date("Y-m-d H:i:s");
      $uploaded_image_path = env("BASE_URL")."images/cars/full/".$image;
      // $getting_id = DB::table('car_photos')->insertGetId(['car_id' => $data['car_id'], 'photo' => $image,'created_at' => $time, 'updated_at' => $time]);

 
          $message = Lang::get('messages.car_pic_updated_admin');
     $setting = Setting::first(); 
       RoomFinderFunctions::SendSmsMessage($setting->admin_mobile_number,$message,$setting->admin_country_code);
       DB::table('car_photos')->insert(['car_id' => $data['car_id'], 'temp_photo' => $image,'created_at' => $time, 'updated_at' => $time , 'photo_approved' =>'n']);
       return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.car_pic_updated') ) );
      
    }
    public function getCarPhotos(Request $request){
      $input = $request->all();
      $v = \Validator::make($input,[ 
       'car_id' => 'required|numeric|exists:cars,id', 
       ] );  
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }       
      $car_img_path = env("BASE_URL")."images/cars/full/";  
      $car_photos = DB::table('car_photos')->select('id','photo','ordering')->where('car_id',$input['car_id'])->where('photo_approved','y')->get();
      foreach($car_photos as $photo){
        $photo->photo = $car_img_path.$photo->photo; 
      }
      return \Response::json(array(  'error' => false,  'result' => $car_photos ) );
    }


    public function reapplyCarListing(Request $request){

     $input = $request->all();   
     
     $v = \Validator::make($input,[ 
      'user_id' => 'required|numeric|exists:users,id',
      'id' => 'required|numeric|exists:cars,id,user_id,'.$request->user_id,'status','rejected'
      
      ] );
     if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }
      $car_id = $request->id;        
      $car = Car::find($car_id); 
      $car->status = 'pending';
      
     
     
      
      /*******************************************send email to site admin*****************************************************/
      $car_full_name = $car->make->title_eng.' '.$car->model->title_eng.'('.$car->year_made.')';
      $owner_full_name = $car->user->first_name;
      $setting = Setting::first();
      $lang = 'en';
      $replace_array["owner_full_name"] = $owner_full_name;
      $replace_array["car_full_name"] = $car_full_name;      
      $slug = 'car-reapply-request-received';
     
      if($replaced_content = RoomFinderFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){

        $content = $replaced_content['content']; 
        $subject = $replaced_content['subject'];

        if($setting){   

          $email_array['to_email'] = $setting->site_email;
         // $email_array['to_email'] = 'es.pradeeparyal@gmail.com'; 
          $email_array['to_name'] = 'Admin';
          $email_array['subject'] = $subject;
          $email_array['message'] = $content;
          RoomFinderFunctions::sendEmail($email_array);
        } 
      }

      //send sms to site admin
      if($setting){
        $admin_mobile_number = $setting->admin_mobile_number;
        $message = "User (id:{$car->user->id}) has reapplied new car. Please review";
        $country_code = $setting->admin_country_code;
        RoomFinderFunctions::SendSmsMessage($admin_mobile_number,$message,$country_code);
      }
      //insert notification to the admin for dashboard notification view
      $car_detail_link = url('admin/cars/'.$car->id);
      $owner_full_name = $car->user->first_name;
      AdminNotification::create([
        'notification_type' => 'user',
        'user_id' => $car->user->id,
        'content' => "User {$owner_full_name} (id:{$car->user->id}) has reapplied for car. Please review",
        'content_link' => $car_detail_link,
        'is_read' => 0
      ]);


    $car->save(); 
    

      return \Response::json(array(  'error' => false,  'result' => Lang::get('messages.car_reapplied') ) );   
    }

    public function getCardAndInstantBookingStatus(Request $request){
      $input = $request->all();
      $v = \Validator::make($input,[ 
       'without_car_id' => 'required|in:0,1',
       'car_id' => 'required_if:without_car_id,0|numeric|exists:cars,id,user_id,'.$input['user_id'], 
       'user_id' => 'required|numeric|exists:users,id',

       ]);  
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }
      $user = User::find($input['user_id']);
      $result = []; 
      if($input['without_car_id'] == 0){       
        $res = Car::where('id',$input['car_id'])->first();
        $result['is_instant_booking_enabled'] = $res->is_instant_booking_enabled; 
      }else{
        $result['is_instant_booking_enabled'] = 0; 
      }
      $result['is_blacklisted'] = $user->is_blacklisted; 
      $result['payment_info_updated'] = $user->payment_info_updated; 
      return \Response::json(array(  'error' => false,  'result' => $result ) );
    }

    public function updateInstantBookingStatus(Request $request){
      $input = $request->all();
      $v = \Validator::make($input,[ 
       'car_id' => 'required|numeric|exists:cars,id,user_id,'.$input['user_id'], 
       'user_id' => 'required|numeric|exists:users,id',
       'is_instant_booking_enabled' => 'required|in:0,1'
       ]);  
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      } 
      $user = User::find($input['user_id']); 
      if($user->payment_info_updated!=1){

        return \Response::json(array(  'error' => true,  'message' => Lang::get('formlabels.payment_info_not_updated_for_instant_booking') ) );
      }      
      $res = Car::where('id',$input['car_id'])->update(['is_instant_booking_enabled' => $input['is_instant_booking_enabled']]);     
      return \Response::json(array(  'error' => false,  'result' => Lang::get('messages.success') ) );
    }


  }
