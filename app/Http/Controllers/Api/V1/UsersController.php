<?php
namespace App\Http\Controllers\Api\V1;


use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use App\Models\MyFavourite;
use DB,File;
use App\Library\RoomFinderFunctions;
use App\Models\Logs; 
use Lang;
class UsersController extends Controller
{


/**
 * @SWG\Post(
 *   path="/users/fblogin",
 *   summary="Facebook Login",
 *   operationId="fblogin",
 *   @SWG\Parameter(
 *     name="device_type",
 *     in="formData",
 *     description="Device Type. (ios or android)",
 *     required=true,
 *     type="string"
 *   ),
  *   @SWG\Parameter(
 *     name="device_id",
 *     in="formData",
 *     description="Device ID.",
 *     required=true,
 *     type="string"
 *   ),
  *   @SWG\Parameter(
 *     name="fb_device_token",
 *     in="formData",
 *     description="Facebook Access Token.",
 *     required=true,
 *     type="string"
 *   ),
  *   @SWG\Parameter(
 *     name="email",
 *     in="formData",
 *     description="Email Address",
 *     required=true,
 *     type="string",
 *   ),
  *   @SWG\Parameter(
 *     name="profile_pic",
 *     in="formData",
 *     description="Profile Pic .",
 *     required=true,
 *     type="file"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */




	public function fblogin(Request $request){
		try{
			$input = $request->all();
			//dd($input); 
			 \Log::warning( 'data received for device_type : ' . $input['device_type'].' of email '.@$input['email']);
			$v = \Validator::make($input,[	
				'device_type' =>'required|in:ios,android',	
				'device_id' =>'required',	
				'fb_device_token' => 'required',				
				'email' =>'required',	
				'profile_pic' =>'required|image|max:50000',	
				]);
			if ($v->fails())
			{	
		 $messages = array();
		 $messages = $v->errors();	
		foreach ($messages->all() as $mess) {

		  $message['detail'] = $mess;
          $message['type'] = 'validation';
          $message['context'] = 'login';
          $mesg = RoomFinderFunctions::getMessage($message);  

					return \Response::json(array(  'error' => true,  'message' => $mesg ) );
				}
			}
			$path_to_save = base_path() . '/public/images/users/';					
			$user1 = User::where('email',$input['email'])->first();
			if($user1){ // if no profile pic found, store it
				$user_data['email'] = $input['email'];
				//remove old images if new one is uploaded
				if($user1->profile_pic and $request->profile_pic){
					@File::delete($path_to_save.'full/'.$user1->profile_pic);
					@File::delete($path_to_save.'thumb/'.$user1->profile_pic);
				}

            $path_to_save = base_path() . '/public/images/users/';			
            $input_field_name = 'profile_pic';				
			$profile_pic = app('App\Http\Controllers\Api\V1\GalleryController')->saveProfileImage($request,$path_to_save,$input_field_name);
			$user_data['profile_pic'] = $profile_pic;


				$user1->update($user_data);
			}
			else {
				//  register 
				$input['status'] = 'active';
				$input['profile_pic'] = (isset($input['profile_pic'])) ? $input['profile_pic']:'';
				if($retid = User::fbstore($input)) {   
					try{ 	
						$user = User::findOrFail($retid);
						if($request->profile_pic){
            $path_to_save = base_path() . '/public/images/users/';	
            $input_field_name = 'profile_pic';				
			$image = app('App\Http\Controllers\Api\V1\GalleryController')->saveProfileImage($request,$path_to_save,$input_field_name);

							if($image){
								$user->fill(['profile_pic' => $image])->save();
							}
						}
						$token = RoomFinderFunctions::generateApiToken($user->id);
						$input['user_id'] = $user->id;						
						$input['access_token'] = $token;
						$access_token = User::setAppSession($input);			 		
				
						Logs::storeLog($input);
							return \Response::json( array( 'error' => false, 'message' => Lang::get('user.loggedin'), 'user' => $user,'access_token' =>$access_token) );
						}
					catch(ModelNotFoundException $e) {
						return \Response::json(array(  'error' => true,   'message' => array(Lang::get('user.invaliduser') ) ) );
					}
				}
				return \Response::json(array(  'error' => true,    'message' => array(Lang::get('messages.error' ) )));
			}
			$token = RoomFinderFunctions::generateApiToken($user1->id);			
			$input['user_id'] = $user1->id;						
			$input['access_token'] = $token;
			$user1->profile_pic = url('/public/images/users/thumb') . '/' .  $user1->profile_pic;
			unset($user1['name']);
			unset($user1['deleted_at']);
			Logs::storeLog($input);
			if($tkn = User::setAppSession($input)){	
			 $message = array();
          $message['detail'] = Lang::get('user.loggedin' );
           $message['type'] = 'create/update';
          $message['context'] = 'login';
          $message = RoomFinderFunctions::getMessage($message);		
				return \Response::json(array(  'error' => false,   'message' => $message,'data'=>$user1,'access_token' =>$tkn  )  );
			}


			else{
					 $message = array();
          $message['detail'] = Lang::get('user.alreadyloggedin' );
           $message['type'] = 'create/update';
          $message['context'] = 'login';
          $message = RoomFinderFunctions::getMessage($message);	
				return \Response::json(array(  'error' => true,    'message' => $message ));
			}
		}catch(ModelNotFoundException $e) {
			 $message = array();
          $message['detail'] = Lang::get('user.logininvalid' );
          $message['type'] = 'create/update';
          $message['context'] = 'login';
          $message = RoomFinderFunctions::getMessage($message);	
			return \Response::json(array(  'error' => true,    'message' => $message ));
		}
	}



	/**
 * @SWG\Post(
 *   path="/users/logout",
 *   summary="Users Logout Login",
 *   operationId="logout",
 *   @SWG\Parameter(
 *     name="access_token",
 *     in="header",
 *     description="Access token",
 *     required=true,
 *     type="string"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */

	public function logout(Request $request) {
		$input = $request->all(); 			
		$input['access_token'] = $request->header('access-token');
		
		if(User::logout($input)){
			// unset cookies
			if (isset($_SERVER['HTTP_COOKIE'])) {
			    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
			    foreach($cookies as $cookie) {
			        $parts = explode('=', $cookie);
			        $name = trim($parts[0]);
			        setcookie($name, '', time()-1000);
			        setcookie($name, '', time()-1000, '/');
			    }
			}
			 $message = array();
          $message['detail'] = Lang::get('user.loggedout');
          $message['context'] = 'logout';
          $message = RoomFinderFunctions::getMessage($message);
			return \Response::json( array ( 'error' => false , 'message' => $message ) );	
		}else{
	      $message = array();
          $message['detail'] = Lang::get('user.invaliduser');
          $message['context'] = 'logout';
          $message = RoomFinderFunctions::getMessage($message);
			return \Response::json( array ( 'error' => false , 'message' => $message ) );	

		}
		
	}

/**
 * @SWG\Post(
 *   path="/fav/add-to-favourites",
 *   summary="Add to favourite",
 *   operationId="AddToFavourite",
  *   @SWG\Parameter(
 *     name="access_token",
 *     in="header",
 *     description="Access token",
 *     required=true,
 *     type="string"
 *   ),
  *   @SWG\Parameter(
 *     name="id",
 *     in="formData",
 *     description="User Id",
 *     required=true,
 *     type="integer"
 *   ),
   *   @SWG\Parameter(
 *     name="id",
 *     in="formData",
 *     description="Room Id",
 *     required=false,
 *     type="integer"
 *   ),
   *   @SWG\Parameter(
 *     name="id",
 *     in="formData",
 *     description="Jagga Id",
 *     required=false,
 *     type="integer"
 *   ),
 *   @SWG\Response(response=200, description="successful operation"),
 *   @SWG\Response(response=406, description="not acceptable"),
 *   @SWG\Response(response=500, description="internal server error")
 * )
 *
 */ 

         public function AddToFavourite(Request $request){

   $input = $request->all();
   $details = array();
   $v = \Validator::make($input,   [ 
    'user_id' => 'required|numeric|exists:users,id',
     'room_id' => 'numeric|exists:rooms,id',
      'jagga_id' => 'numeric|exists:jaggas,id',                 
    ] );
     if ($v->fails())
      { 
     $messages = array();
     $messages = $v->errors();  
    foreach ($messages->all() as $mess) {

         $message['detail'] = $mess;
          $message['type'] = 'validation';
        $message['context'] = 'Room/Jagga';
          $mesg = RoomFinderFunctions::getMessage($message);  

          return \Response::json(array(  'error' => true,  'message' => $mesg ) );
        }
      } 

      if(!empty($input['room_id'])  || !empty($input['jagga_id'])){

      	$data = array();
      	$data['created_at'] = date('Y-m-d H:i:s');
      	$data['deleted_at'] = date('Y-m-d H:i:s');
      	$data['user_id'] = $input['user_id'];
      	if(!empty($input['room_id'])){
      		       $prev_room = MyFavourite::where('user_id' , $input['user_id'])->where('room_id' , $input['room_id'])->get();
      		      	$data['room_id'] = $input['room_id'];


         if($prev_room->isNotEmpty()){
            $message = array();
          $message['detail'] = Lang::get('messages.already_add_to_fav');
          $message['type'] = 'create';
        $message['context'] = 'Room/Jagga';
          $message = RoomFinderFunctions::getMessage($message);
          return \Response::json(array(  'error' => false,  'message' =>  $message) );
        }else{
        	$user_fav_id = MyFavourite::create($data)->id;
        }

      	}
      	if(!empty($input['jagga_id'])){
      		$prev_jagga = MyFavourite::where('user_id' , $input['user_id'])->where('jagga_id' , $input['jagga_id'])->get();
      		      	$data['jagga_id'] = $input['jagga_id'];

      		      	if($prev_jagga->isNotEmpty()){
        	 $message = array();
          $message['detail'] = Lang::get('messages.already_add_to_fav');
          $message['type'] = 'create';
        $message['context'] = 'Room/Jagga';
          $message = RoomFinderFunctions::getMessage($message);
          return \Response::json(array(  'error' => false,  'message' =>  $message) );

        }else{
        	$user_fav_id = MyFavourite::create($data)->id;
        }
      	}

      	
      }else{
      	 $message = array();
          $message['detail'] = Lang::get('messages.room_or_jagga');
          $message['type'] = 'create';
        $message['context'] = 'Room/Jagga';
          $message = RoomFinderFunctions::getMessage($message);
    return \Response::json(array(  'error' => false,  'message' =>  $message) );
      }
  
  if($user_fav_id){
  	$user_favourite = MyFavourite::where('id' , $user_fav_id)->get();
  	 $message['detail'] = Lang::get('messages.added_to_fav');
         $message['type'] = 'create';
        $message['context'] = 'Room/Jagga';
          $mesg = RoomFinderFunctions::getMessage($message);  

    return \Response::json(array(  'error' => false,   'data' => $user_favourite , 'message' => $message ) );
  }else{
     $message = array();
          $message['detail'] = Lang::get('messages.resultnotfound');
          $message['type'] = 'create';
        $message['context'] = 'Room/Jagga';
          $message = RoomFinderFunctions::getMessage($message);
    return \Response::json(array(  'error' => false,  'message' =>  $message) );

  }
  }




	}
