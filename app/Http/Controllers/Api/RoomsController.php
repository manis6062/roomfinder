<?php namespace App\Http\Controllers\Api;
  use Illuminate\Http\Request;

  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use App\Library\RoomFinderFunctions;
  use App\Models\Room;
  use App\Models\User;
  use App\Models\RoomModel;
  use App\Models\RoomImagesModel;
  use Lang,DB,Auth;
  class RoomsController extends Controller
  {


       public function addRooms(Request $request){
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
               $this->uploadMultipleImages($request);
          }else{
            $path_to_save = base_path() . '/public/images/rooms/';      
    $input_field_name = 'image';        
    $image = app('App\Http\Controllers\Api\GalleryController')->saveImage($request,$path_to_save,$input_field_name);
          DB::table('images')->insert(['room_id' => $room_id, 'image' => $image]);

          }
         
         return \Response::json(array(  'error' => false,  'room_id' => $room_id ) );   
       }



       public function uploadMultipleImages(Request $request){
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

      $data['path_to_save'] = base_path() . '/images/rooms/';    
      $data['input_field_name'] = 'image';       
      $data['request'] = $request; 

      $images = app('App\Http\Controllers\Api\GalleryController')->saveImages($data);

      foreach($images as $image){   
       DB::table('images')->insert(['room_id' => $room_id, 'image' => $image]);
             }
      $uploaded_image_path = env("BASE_URL")."images/cars/full/".$images[0];

        return $uploaded_image_path ;
      
    
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

  public function detail(Request $request){
   $input = $request->all();

   $details = array();
   $v = \Validator::make($input,   [ 
    'id' => 'required|numeric|exists:cars,id',                 
    ] );
   if ($v->fails())
   {   
    $msg = array();
    $messages = $v->errors();           
    foreach ($messages->all() as $message) {
      return \Response::json(array(  'error' => true,  'message' => $message ) );
    }  
  }   
  $details = Car::detail($input['id']);
  if($details){
    return \Response::json(array(  'error' => false,   'result' => $details  ) );
  }else{
    return \Response::json(array(  'error' => true,   'message' => Lang::get('messages.resultnotfound')  ) );
  }
  }


  public function uploadMultiplePhotos(Request $request){
      $input = $request->all();  
      $user = User::find($input['user_id']);
      $rules = [
       'user_id' => 'required|exists:cars,user_id',
       'car_id' => 'required|numeric|exists:cars,id', 

      ];

      $files = count($request->photo) - 1;

      if(count($request->photo) == 0){
        return array(  'error' => true,  'message' => Lang::get('messages.photo_is_required') ) ; 
      }
      foreach(range(0, $files) as $index) {
          $rules['photo.' . $index] = 'required|image|max:5120';
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
      $total_photos = DB::table('car_photos')->where('car_id',$input['car_id'])->count();
      if($total_photos >= 16){
        return array(  'error' => true,  'message' => Lang::get('messages.nomorephotos') ) ; 
      }
    
      $data['car_id'] = $input['car_id'];

      $data['path_to_save'] = base_path() . '/images/cars/';    
      $data['input_field_name'] = 'photo';       
      $data['request'] = $request; 

      $images = app('App\Http\Controllers\Api\V1\GalleryController')->saveImages($data);         
      foreach($images as $image){   
 DB::table('car_photos')->insert(['car_id' => $data['car_id'], 'temp_photo' => $image , 'photo_approved' =>'n' ]);
             }
      $uploaded_image_path = env("BASE_URL")."images/cars/full/".$images[0];
      
      return array(  'error' => false,  'message' => $uploaded_image_path );  
  }

  public function updateRoom(Request $request){

    $input = $request->all();    
    
    $v = \Validator::make($input,   [ 
                //about
     'step' => 'required|numeric',
     'user_id' => 'required|numeric|exists:users,id', 
     'car_id' => 'required|numeric|exists:cars,id', 
     'year_made' =>'required_if:step,1|date_format:Y',
     'make_id' =>'required_if:step,1|exists:car_make,id',
     'model_id' =>'required_if:step,1|exists:car_models,id',
     'mileage_used' =>'required_if:step,1',
     'total_seats' =>'required_if:step,1|numeric',

     //'estimated_value_id'=>'required_if:step,1|exists:car_estimated_values,id',
     'transmission' =>'required_if:step,1|in:Manual,Automatic',
     'vehicle_type_id' =>'required_if:step,1|exists:vehicle_types,id',
               //location
     'address' => 'required_if:step,2',
     'loc_lat' => 'required_if:step,2',
     'loc_lon' => 'required_if:step,2',
     'car_plate_number' => 'required_if:step,2',
     'pickup_instruction' => 'required_if:step,2',
               //details
     'description' => 'required_if:step,3',
     'feature_id' => 'required_if:step,3',
               //availability
     'availability_type' => 'required_if:step,4|in:Everyday,Weekends,Weekdays',
               //pricing
     'enable_custom_price' =>'required_if:step,5|in:0,1',
     'custom_price' => 'required_if:enable_custom_price,1|min:1|numeric',
     'custom_price_week' => 'required_if:enable_custom_price,1|min:1|numeric',
     'custom_price_month' => 'required_if:enable_custom_price,1|min:1|numeric',
     ] );
  $steps = array(1,2,3,4,5,6);
  if ($v->fails())
  {   
    $msg = array();
    $messages = $v->errors();           
    foreach ($messages->all() as $message) {
      return \Response::json(array(  'error' => true,  'message' => $message ) );
    }  
  } 
  if(!in_array($input['step'],$steps)){
    return \Response::json(array(  'error' => true,  'message' => 'invalid step' ) );
  }
  $car = Car::find($input['car_id']);

  switch($input['step']){

    case 1:

      $output = Car::getRentalFeeUsingAPI($input);  
      $message = "Error Occurred. Please try again";
      if(isset($output['data']->cargroup) and $output['data']->cargroup == ""){
         $message = Lang::get('formlabels.car_not_eligible_for_insurance_description');
      }
      if($output['success'] and $output['data']->cargroup > 1 ){
          //return \Response::json(array(  'error' => false ) );
      }else{
          return \Response::json(array(  'error' => true,  'message' => $message ) );
      }

      $car->make_id = $input['make_id'];
      $car->model_id = $input['model_id'];
      $car->mileage_used = $input['mileage_used'];
                   
      $car->total_seats = $input['total_seats'];                 
      $car->transmission = $input['transmission'];
      $car->vehicle_type_id = $input['vehicle_type_id'];
      $car->save();
      break;
      case 2:
        $car->address = $input['address'];
        $car->loc_lat = $input['loc_lat'];
        $car->loc_lon = $input['loc_lon'];
        $car->car_plate_number = $input['car_plate_number'];


         if($input['pickup_instruction']){
          
          $setting = Setting::first();
          $pickup_instruction_restriction = strtolower($setting->pickup_instruction_restriction);
          $pickup_instruction_restrictions = explode(',', $pickup_instruction_restriction);

          $requested_text = explode(' ', strtolower($input['pickup_instruction']));

        foreach($requested_text as $value){
        if(!in_array($value, $pickup_instruction_restrictions)){
        $query[] = $value;
        }
        } 

        $query = implode(" ", $query);

       $car->pickup_instruction =  $query; 

         }

        $car->save(); 
        break;
      case 3:
           if($input['description']){
          
          $setting = Setting::first();
          $car_info_restriction = strtolower($setting->car_info_restriction_words);
          $car_info_restrictions = explode(',', $car_info_restriction);

          $requested_text = explode(' ', strtolower($input['description']));

        foreach($requested_text as $value){
        if(!in_array($value, $car_info_restrictions)){
        $query[] = $value;
        }
        } 

        $query = implode(" ", $query);

        $pattern = "/[a-zA-Z]*[:\/\/]*[A-Za-z0-9\-_]+\.+[A-Za-z0-9\.\/%&=\?\-_]+/i";
       $replacement = "";
       $car->description =  preg_replace($pattern, $replacement, $query);

         }



        $car->save();                
        DB::table('car_features')->where('car_id', $car->id)->delete(); //delete old features and insert new ones
        foreach($input['feature_id'] as $f){          
          DB::table('car_features')->insert(['car_id' => $car->id, 'feature_id' => $f,'created_at' =>date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s') ]);
        }
        break;
      case 4:
        $array['availability_type'] = $input['availability_type'];
        $array['car_id'] = $input['car_id'];               
        Car::updateDisabledDates($array);                
        break;
      case 5:
        if($input['enable_custom_price'] == 1 ){
           $car->custom_price = $input['custom_price'];
           $car->custom_price_week = $input['custom_price_week'];
           $car->custom_price_month = $input['custom_price_month'];
        }                
        $car->enable_custom_price = $input['enable_custom_price'];               
        $car->save(); 
        break;

      case 6:
        $ret_val =  $this->uploadMultiplePhotos($request); 
         $message = Lang::get('messages.car_pic_updated_admin');
             $setting = Setting::first(); 
       RoomFinderFunctions::SendSmsMessage($setting->admin_mobile_number,$message,$setting->admin_country_code);
        if($ret_val['error']){
           return \Response::json(array(  'error' => true,  'message' => $ret_val['message'] ) );
        }
   
        $path_to_save = base_path() . '/images/cars/';       
        if($ret_val['error'] == false){                 
          $uploaded_image_path = $ret_val['message'];
        }   
        break;
    }
    if($car->step_completed < 6 and $input['step'] == 6){ //if this is the first time car info is being added, then send sms and email to admin
   
      $slug = 'car-entry-request-received';
      $car_full_name = '';     
      $setting = Setting::first();
      /***********************************send email to site admin***************************************************/
    
      if($template = RoomFinderFunctions::getEmailTemplate($slug)){
        $search_array = array("{owner_full_name}","{car_full_name}");
        $content = $template->content_eng; 
        $subject =$subject_en =  $template->subject_eng;
        $car_full_name = $car->make->title_eng.' '.$car->model->title_eng.'('.$car->year_made.')';       
        $owner_full_name = $car->user->first_name;

        $replace_array = array($owner_full_name,$car_full_name);
        $content = str_replace($search_array,$replace_array,$content);

        if($setting){                 
          $email_array['to_email'] = $setting->site_email;
          $email_array['to_name'] = 'Admin';
          $email_array['subject'] = $subject_en;
          $email_array['message'] = $content;
          RoomFinderFunctions::sendEmail($email_array);
        } 
      }

      /*******************************************send email to car user *************************************************/
      $slug = 'email-to-user-when-car-submitted'; 
      $owner_full_name = $car->user->first_name;
      $car_full_name = $car->make->title_eng.' '.$car->model->title_eng.'('.$car->year_made.')';
      $replace_array["owner_full_name"] = $owner_full_name;
      $replace_array["car_full_name"] = $car_full_name;
      $lang = ($car->user->lang)?$car->user->lang:'en';
      //$lang = 'chn';
      if($replaced_content = RoomFinderFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){
       
        $content = $replaced_content['content']; 
        $subject = $replaced_content['subject']; 
                      
        $email_array['to_email'] = $car->user->email;
        //$email_array['to_email'] = 'es.pradeeparyal@gmail.com';
        $email_array['to_name'] = $owner_full_name;
        $email_array['subject'] = $subject;
        $email_array['message'] = $content;
        RoomFinderFunctions::sendEmail($email_array);
      }

      /***************************send sms to site admin **********************************************************************/
      if($setting){
        $admin_mobile_number = $setting->admin_mobile_number;
        $message = "User (id:{$car->user->id}) has added new car. Please review";
        $country_code = $setting->admin_country_code;
        RoomFinderFunctions::SendSmsMessage($admin_mobile_number,$message,$country_code);
      }
      /*****************************************************insert notification to the admin for dashboard notification view****************************/
      $car_detail_link = url('admin/cars/'.$car->id);
      $owner_full_name = $car->user->first_name;
      AdminNotification::create([
        'notification_type' => 'user',
        'user_id' => $car->user->id,
        'content' => "User {$owner_full_name} (id:{$car->user->id}) has added new car. Please review",
        'content_link' => $car_detail_link,
        'is_read' => 0
      ]);

      $car->status = 'pending';
      $car->save(); 
    }

    $car->where('step_completed','<',$input['step'])->where('id',$car->id)->update(['step_completed' =>$input['step']]);
    //Car::where('step_completed','<',$input['step'])->where('id',$car->id)->update(['step_completed' =>$input['step']]);
    if($input['step'] == 6){
      return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.car_pic_updated') ) );
    }else{
      return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );
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

    public function deleteCar(Request $request){
      $input = $request->all();
      $v = \Validator::make($input,   [ 
                //about
       'user_id' => 'required|exists:cars,user_id',    
       'car_id' => 'required|numeric|exists:cars,id,user_id,'.$input['user_id'],
       ] );        
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }
      $car = Car::find($request->car_id);
      $car->deleted = 1;
      $car->save();
      return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );
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
