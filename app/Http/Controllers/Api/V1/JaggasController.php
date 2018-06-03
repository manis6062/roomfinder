<?php namespace App\Http\Controllers\Api\V1;

  use Illuminate\Http\Request;

  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use App\Library\RoomFinderFunctions;
  use App\Models\Jagga;
  use App\Models\User;
  use App\Models\Images;
  use Lang,DB,Auth;
  class JaggasController extends Controller
  {


    /**
 * @SWG\Post(
 *   path="/jagga/add",
 *   summary="Add Jagga",
 *   operationId="addJagga",
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
 *     name="image[]",
 *     in="formData",
 *     description="Jagga Image",
 *     required=true,
 *     type="file",
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
 *     name="sold",
 *     in="formData",
 *     description="(0 for unsold or 1 for sold)",
 *     required=true,
 *     type="string"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */


       public function addJagga(Request $request){
        $input = $request->all();
        $details = array();
        $v = \Validator::make($input,   [ 
                //about
         'user_id' => 'required|numeric|exists:users,id', 
         'type' => 'required',
            'phone_no' =>'required',
             'loc_lat' =>'required|numeric',
              'loc_lon' =>'required|numeric',
               'address' =>'required',
                'price' =>'required|numeric',
                'description' => 'required',
                 'sold' => 'required|numeric',
                 'image' =>'required|max:50000',
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
              $message = "The image field must be an array.";
              return \Response::json(array(  'error' => true,  'message' => Lang::get('messages.image_array') ) );
        }


        $user = User::find($input['user_id']);
         $array['user_id'] = $input['user_id'];
         $array['type'] = $input['type'];
         $array['phone_no'] = $input['phone_no'];
         $array['loc_lat'] = $input['loc_lat'];
         $array['loc_lon'] = $input['loc_lon'];
         $array['description'] = $input['description'];
          $array['address'] = $input['address'];
            $array['price'] = $input['price'];
             $array['sold'] = $input['sold'];
          $jagga_id = Jagga::create($array)->id;

          if(count($request->image) > 1){
               $this->uploadMultipleImages($request , $jagga_id);
          }else{
            $path_to_save = base_path() . '/public/images/jaggas/';      
    $input_field_name = 'image';        
    $image = app('App\Http\Controllers\Api\V1\GalleryController')->saveSingleImage($request,$path_to_save,$input_field_name);
          DB::table('images')->insert(['jagga_id' => $jagga_id, 'image' => $image]);

          }
         
         return \Response::json(array(  'error' => false,  'jagga_id' => $jagga_id , 'created_at' =>date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')) );   
       }





  /**
 * @SWG\Get(
 *   path="/jagga/search-jagga",
 *   summary="Search Jagga",
 *   operationId="searchJagga",
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
 *     required=false,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="phone_no",
 *     in="formData",
 *     description="Phone no.",
 *     required=false,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="loc_lat",
 *     in="formData",
 *     description=" Location - Longitude",
 *     required=false,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="loc_lon",
 *     in="formData",
 *     description="Location - Latitude",
 *     required=false,
 *     type="string"
 *   ),
    *   @SWG\Parameter(
 *     name="address",
 *     in="formData",
 *     description="Address",
 *     required=false,
 *     type="string"
 *   ),
    *   @SWG\Parameter(
 *     name="image",
 *     in="formData",
 *     description="Room Image",
 *     required=false,
 *     type="file",
 *   ),
    *   @SWG\Parameter(
 *     name="price",
 *     in="formData",
 *     description="Price",
 *     required=false,
 *     type="string"
 *   ),
     *   @SWG\Parameter(
 *     name="description",
 *     in="formData",
 *     description="Description",
 *     required=false,
 *     type="string"
 *   ),
     *   @SWG\Parameter(
 *     name="sold",
 *     in="formData",
 *     description="(0 for unsold or 1 for sold)",
 *     required=false,
 *     type="string"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */


    public function searchJagga(Request $request){
     $input = $request->all();     
     $v = \Validator::make($input,[    
      'per_page' =>'numeric',
      'page_number' =>'numeric',
      "user_id" => 'numeric',
      'sold'=>'numeric',
      'high_price'=>'numeric',
      'low_price'=>'numeric'
      ]);
     if ($v->fails())
     {   
      $msg = array();
      $messages = $v->errors();           
      foreach ($messages->all() as $message) {
        return \Response::json(array(  'error' => true,  'message' => $message ) );
      }               

    }  

      $result = Jagga::search($input);  
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









       /**
 * @SWG\Patch(
 *   path="/jagga/update-jagga",
 *   summary="Update Jagga",
 *   operationId="updateJagga",
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
 *     name="jagga_id",
 *     in="formData",
 *     description="Jagga Id",
 *     required=true,
 *     type="integer"
 *   ),
  *   @SWG\Parameter(
 *     name="type",
 *     in="formData",
 *     description="Type",
 *     required=false,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="phone_no",
 *     in="formData",
 *     description="Phone no.",
 *     required=false,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="loc_lat",
 *     in="formData",
 *     description=" Location - Longitude",
 *     required=false,
 *     type="string"
 *   ),
   *   @SWG\Parameter(
 *     name="loc_lon",
 *     in="formData",
 *     description="Location - Latitude",
 *     required=false,
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
 *     required=false,
 *     type="file",
 *   ),
    *   @SWG\Parameter(
 *     name="price",
 *     in="formData",
 *     description="Price",
 *     required=false,
 *     type="string"
 *   ),
     *   @SWG\Parameter(
 *     name="description",
 *     in="formData",
 *     description="(0 for unsold or 1 for sold)",
 *     required=false,
 *     type="string"
 *   ),
     *   @SWG\Parameter(
 *     name="sold",
 *     in="formData",
 *     description="Sold",
 *     required=false,
 *     type="string"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */  



         public function updateJagga(Request $request){

    $input = $request->all();    
    
    $v = \Validator::make($input,   [ 
                //about
     'type' => 'required',
            'phone_no' =>'numeric',
             'loc_lat' =>'numeric',
              'loc_lon' =>'numeric',
                'price' =>'numeric',
                 'sold' => 'numeric',
                 'jagga_id' => 'numeric',
                 'user_id' => 'numeric',

     ] );
  if ($v->fails())
  {   
    $msg = array();
    $messages = $v->errors();           
    foreach ($messages->all() as $message) {
      return \Response::json(array(  'error' => true,  'message' => $message ) );
    }  
  } 

  $jagga = Jagga::find($input['jagga_id']);
    $room_image_path = base_path() . '/public/images/jaggas/';

  if($jagga){  
     unset($input['jagga_id']);
     unset($input['image']);
  $jagga->where('id',$jagga->id)->update($input);
   $images = Images::where('jagga_id' , $jagga->id);

  if($request->image){
    foreach ($images->get() as $key => $value) {
    $full_path = $room_image_path.'full/'.$value->image;
    $mid_path = $jagga_image_path.'mid/'.$value->image; 
    $thumb_path = $jagga_image_path.'thumb/'.$value->image; 
      @unlink($full_path);
       @unlink($mid_path); 
      @unlink($thumb_path); 
    }
    $delete = $images->delete();

        if(count($request->image) > 1){
               $this->uploadMultipleImages($request , $request->jagga_id);
          }else{
    $input_field_name = 'image';        
    $image = app('App\Http\Controllers\Api\V1\GalleryController')->saveSingleImage($request,$room_image_path,$input_field_name);
          DB::table('images')->insert(['jagga_id' => $request->jagga_id, 'image' => $image]);

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

      $data['path_to_save'] = base_path() . '/public/images/jaggas/';   
      $data['input_field_name'] = 'image';       
      $data['request'] = $request; 

      $images = app('App\Http\Controllers\Api\V1\GalleryController')->saveImages($data);

      foreach($images as $image){   
       DB::table('images')->insert(['jagga_id' => $id, 'image' => $image , 'created_at' =>date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')]);
             }  
  }



       /**
 * @SWG\Delete(
 *   path="/jagga/delete",
 *   summary="Delete Jagga",
 *   operationId="deleteJagga",
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
 *     name="jagga_id",
 *     in="formData",
 *     description="Jagga Id",
 *     required=true,
 *     type="integer"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */  

     public function deleteJagga(Request $request){
      $input = $request->all();
      $v = \Validator::make($input,   [ 
                //about
       'user_id' => 'required|exists:users,id',    
       'jagga_id' => 'required|numeric|exists:jaggas,id',
       ] );        
      if ($v->fails())
      {   
        $msg = array();
        $messages = $v->errors();           
        foreach ($messages->all() as $message) {
          return \Response::json(array(  'error' => true,  'message' => $message ) );
        }  
      }
      $jagga = Jagga::find($request->jagga_id);
      $jagga_image_path = base_path() . '/public/images/jaggas/';   


      if($jagga){
              $jagga->delete();
                 $images = Images::where('jagga_id' , $jagga->id);

  if($images){
    foreach ($images->get() as $key => $value) {
    $full_path = $jagga_image_path.'full/'.$value->image;
    $mid_path = $jagga_image_path.'mid/'.$value->image; 
    $thumb_path = $jagga_image_path.'thumb/'.$value->image; 
      @unlink($full_path);
       @unlink($mid_path); 
      @unlink($thumb_path); 
    }
    $delete = $images->delete();

      return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );
    }
      }else{
         return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.invalid_jagga_id') ) );
      }

   
}


      /**
 * @SWG\Get(
 *   path="/jagga/detail",
 *   summary="Jagga Detail",
 *   operationId="jdetail",
  *   @SWG\Parameter(
 *     name="id",
 *     in="formData",
 *     description="Jagga Id",
 *     required=true,
 *     type="integer"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */    

  public function jdetail(Request $request){
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
  $details = Jagga::detail($input['id']);
  if($details){
    return \Response::json(array(  'error' => false,   'result' => $details  ) );
  }else{
    return \Response::json(array(  'error' => true,   'message' => Lang::get('messages.resultnotfound')  ) );
  }
  }



  }