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


/**
 * @SWG\Post(
 *   path="/room/add",
 *   summary="Add Room",
 *   operationId="addRoom",
 *   @SWG\Parameter(
 *     name="access_token",
 *     in="header",
 *     description="Access Token",
 *     required=true,
 *     type="string"
 *   ),
 *   @SWG\Parameter(
 *     name="user_id",
 *     in="formData",
 *     description="User Id",
 *     required=true,
 *     type="integer"
 *   ),
  *   @SWG\Parameter(
 *     name="type",
 *     in="formData",
 *     description="Type",
 *     required=true,
 *     type="string"
 *   ),
  *   @SWG\Parameter(
 *     name="no_of_floor",
 *     in="formData",
 *     description="Number of rooms",
 *     required=true,
 *     type="string"
 *   ),
  *   @SWG\Parameter(
 *     name="no_of_room",
 *     in="formData",
 *     description="Number of floors",
 *     required=true,
 *     type="string",
 *   ),
  *   @SWG\Parameter(
 *     name="kitchen",
 *     in="formData",
 *     description="Kitchen",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="parking",
 *     in="formData",
 *     description="Parking",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="restroom",
 *     in="formData",
 *     description="Restroom",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="phone_no",
 *     in="formData",
 *     description="Phone no.",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="loc_lat",
 *     in="formData",
 *     description=" Location - Longitude",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="loc_lon",
 *     in="formData",
 *     description="Location - Latitude",
 *     required=true,
 *     type="string"
 *   ),
    *   @SWG\Parameter(
 *     name="address",
 *     in="formData",
 *     description="Address",
 *     required=true,
 *     type="string"
 *   ),
    *   @SWG\Parameter(
 *     name="image",
 *     in="formData",
 *     description="Room Image",
 *     required=true,
 *     type="file",
 *   ),
    *   @SWG\Parameter(
 *     name="preference",
 *     in="formData",
 *     description="Preference",
 *     required=true,
 *     type="string"
 *   ),
    *   @SWG\Parameter(
 *     name="price",
 *     in="formData",
 *     description="Price",
 *     required=true,
 *     type="string"
 *   ),
     *   @SWG\Parameter(
 *     name="description",
 *     in="formData",
 *     description="Description",
 *     required=true,
 *     type="string"
 *   ),
     *   @SWG\Parameter(
 *     name="occupied",
 *     in="formData",
 *     description="(0 for occupied or 1 for not occupied)",
 *     required=true,
 *     type="string",
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */



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

      /**
 * @SWG\Post(
 *   path="room/detail",
 *   summary="Room Detail",
 *   operationId="rdetail",
  *   @SWG\Parameter(
 *     name="id",
 *     in="formData",
 *     description="Room Id",
 *     required=true,
 *     type="integer"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */ 

         public function rdetail(Request $request){
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


  /**
 * @SWG\Post(
 *   path="room/update-room",
 *   summary="Update Room",
 *   operationId="updateRoom",
 *   @SWG\Parameter(
 *     name="access_token",
 *     in="header",
 *     description="Access Token",
 *     required=true,
 *     type="string"
 *   ),
 *   @SWG\Parameter(
 *     name="user_id",
 *     in="formData",
 *     description="User Id",
 *     required=true,
 *     type="integer"
 *   ),
  *   @SWG\Parameter(
 *     name="room_id",
 *     in="formData",
 *     description="Room Id",
 *     required=true,
 *     type="integer"
 *   ),
  *   @SWG\Parameter(
 *     name="type",
 *     in="formData",
 *     description="Type",
 *     required=true,
 *     type="string"
 *   ),
  *   @SWG\Parameter(
 *     name="no_of_floor",
 *     in="formData",
 *     description="Number of rooms",
 *     required=true,
 *     type="string"
 *   ),
  *   @SWG\Parameter(
 *     name="no_of_room",
 *     in="formData",
 *     description="Number of floors",
 *     required=true,
 *     type="string",
 *   ),
  *   @SWG\Parameter(
 *     name="kitchen",
 *     in="formData",
 *     description="Kitchen",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="parking",
 *     in="formData",
 *     description="Parking",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="restroom",
 *     in="formData",
 *     description="Restroom",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="phone_no",
 *     in="formData",
 *     description="Phone no.",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="loc_lat",
 *     in="formData",
 *     description=" Location - Longitude",
 *     required=true,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="loc_lon",
 *     in="formData",
 *     description="Location - Latitude",
 *     required=true,
 *     type="string"
 *   ),
    *   @SWG\Parameter(
 *     name="address",
 *     in="formData",
 *     description="Address",
 *     required=true,
 *     type="string"
 *   ),
    *   @SWG\Parameter(
 *     name="image",
 *     in="formData",
 *     description="Room Image",
 *     required=true,
 *     type="file",
 *   ),
    *   @SWG\Parameter(
 *     name="preference",
 *     in="formData",
 *     description="Preference",
 *     required=true,
 *     type="string"
 *   ),
    *   @SWG\Parameter(
 *     name="price",
 *     in="formData",
 *     description="Price",
 *     required=true,
 *     type="string"
 *   ),
     *   @SWG\Parameter(
 *     name="description",
 *     in="formData",
 *     description="Description",
 *     required=true,
 *     type="string"
 *   ),
     *   @SWG\Parameter(
 *     name="occupied",
 *     in="formData",
 *     description="(0 for occupied or 1 for not occupied)",
 *     required=true,
 *     type="string"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */


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


     /**
 * @SWG\Post(
 *   path="room/delete",
 *   summary="Delete Room",
 *   operationId="deleteRoom",
 *   @SWG\Parameter(
 *     name="access_token",
 *     in="header",
 *     description="Access Token",
 *     required=true,
 *     type="string"
 *   ),
 *   @SWG\Parameter(
 *     name="user_id",
 *     in="formData",
 *     description="User Id",
 *     required=true,
 *     type="integer"
 *   ),
  *   @SWG\Parameter(
 *     name="room_id",
 *     in="formData",
 *     description="Room Id",
 *     required=true,
 *     type="integer"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */

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



  }
