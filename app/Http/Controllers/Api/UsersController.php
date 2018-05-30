<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
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
				'profile_pic' =>'required|image|max:500000',	
				]);
			if ($v->fails())
			{	
				$msg = array();
				$messages = $v->errors();			
				foreach ($messages->all() as $message) {
					return \Response::json(array(  'error' => true,  'message' => $message ) );
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
			$profile_pic = app('App\Http\Controllers\Api\GalleryController')->saveProfileImage($request,$path_to_save,$input_field_name);
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
			$image = app('App\Http\Controllers\Api\GalleryController')->saveProfileImage($request,$path_to_save,$input_field_name);

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
			Logs::storeLog($input);
			if($tkn = User::setAppSession($input)){			
				return \Response::json(array(  'error' => false,   'message' => Lang::get('user.loggedin' ),'user'=>$user1,'access_token' =>$tkn  )  );
			}else{
				return \Response::json(array(  'error' => true,    'message' => array(Lang::get('user.alreadyloggedin' ) )));
			}
		}catch(ModelNotFoundException $e) {
			return \Response::json(array(  'error' => true,   'message' => array(Lang::get('user.logininvalid' )  )  ));
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
			return \Response::json( array ( 'error' => false , 'message' => Lang::get('user.loggedout') ) );	
		}else{
			return \Response::json( array ( 'error' => true, 'message' => array(Lang::get('user.invaliduser' )  )) );
		}
		
	}




	}
