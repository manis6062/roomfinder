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
 * @SWG\Get(
 *   path="/customer/{customerId}/rate",
 *   summary="List customer rates",
 *   operationId="getCustomerRates",
 *   @SWG\Parameter(
 *     name="customerId",
 *     in="path",
 *     description="Target customer.",
 *     required=true,
 *     type="integer"
 *   ),
 *   @SWG\Parameter(
 *     name="filter",
 *     in="query",
 *     description="Filter results based on query string value.",
 *     required=false,
 *     enum={"active", "expired", "scheduled"},
 *     type="string"
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
				'profile_pic' =>'required|image',	
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
			$profile_pic = app('App\Http\Controllers\Api\GalleryController')->saveImage($request,$path_to_save,$input_field_name);
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
			$image = app('App\Http\Controllers\Api\GalleryController')->saveImage($request,$path_to_save,$input_field_name);

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
			$user1->profile_pic = url('/images/users/thumb') . '/' .  $user1->profile_pic;
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

	public function getCurrentUser(Request $request) {
		$input = $request->all(); 			
		$input['access_token'] = $request->header('access-token');
		
		if(User::logout($input)){
			return \Response::json( array ( 'error' => false , 'message' => Lang::get('user.loggedout') ) );	
		}else{
			return \Response::json( array ( 'error' => true, 'message' => array(Lang::get('user.invaliduser' )  )) );
		}
		
	}


	public function userDetail(Request $request){
		$data = $request->all(); 
		$v = \Validator::make($data,[
			'user_id' => 'required|numeric|exists:users,id'
			]
			);
		if ($v->fails())
		{	
			$msg = array();
			$messages = $v->errors();			
			foreach ($messages->all() as $message) {
				return \Response::json(array(  'error' => true,  'message' => $message ) );
			}				
			
		}	
		$user = User::details($data['user_id']);
		    
		if($user){
			return \Response::json(array(  'error' => false,   'result' => $user  ) );
		}else{
			return \Response::json(array(  'error' => true,   'message' => Lang::get('messages.resultnotfound')  ) );
		}
	}


	
	

		public function editProfile(Request $request){

			$data = $request->all();	
			//return \Response::json(array(  'error' => true,  'request' => $data ) );
			$v = \Validator::make($data,[
				'user_id' =>'required|numeric|exists:users,id',
				'first_name' => 'required',
				'mobile_country_code' => 'required',
				'mobile_number' => 'required',
				'last_name' => 'required',
				'email' => 'required|email|unique:users,email,'.$data['user_id'],
				'profile_pic' =>'image',

				'bio' => 'required',

				]
				);
			if ($v->fails())
			{	
				$msg = array();
				$messages = $v->errors();			
				foreach ($messages->all() as $message) {
					return \Response::json(array(  'error' => true,  'message' => $message ) );
				}
			}


	    if($request->bio){
          
          $setting = Setting::first();
          $bio_restriction = strtolower($setting->bio_restriction_words);
          $bio_restrictions = explode(',', $bio_restriction);

          $requested_text = explode(' ', strtolower($request->bio));

				foreach($requested_text as $value){
				if(!in_array($value, $bio_restrictions)){
				$query[] = $value;
				}
				} 

				$query = implode(" ", $query);

				 $pattern = "/[a-zA-Z]*[:\/\/]*[A-Za-z0-9\-_]+\.+[A-Za-z0-9\.\/%&=\?\-_]+/i";
       $replacement = "";
      $data['bio'] =  preg_replace($pattern, $replacement, $query);

         }

			

			$user = User::find($data['user_id']);
			//return \Response::json(array(  'error' => true,  'message' => $data ) );
			
				//if profile pic needs to be updated,

			// if($request->profile_pic){
			// 	$path_to_save = base_path() . '/public/images/users/';	

			// 	$input_field_name = 'profile_pic';
			// //	return \Response::json(array(  'error' => true,  'message' => $data ) );
			// 	$back_image = app('App\Http\Controllers\Api\V1\GalleryController')->saveImage($request,$path_to_save,$input_field_name);
			// 	if($user->profile_pic){ // if there is old profile pic, delete that from file system
			// 		@File::delete($path_to_save.'full/'.$user->profile_pic);
			// 		@File::delete($path_to_save.'thumb/'.$user->profile_pic);
			// 	}
			// 	$user->fill(['profile_pic' => $back_image])->save();
			// }


                  if($user->user_type == 'n'){

        	if($request->profile_pic){
			$input_field_name = 'profile_pic';
			$path_to_save = base_path() . '/public/images/users/';						
			$profile_pic = app('App\Http\Controllers\Front\GalleryController')->saveImage($request,$path_to_save,$input_field_name);
			$user->profile_pic = $profile_pic; 
		    }
        }else{
        	if($request->profile_pic){
			$input_field_name = 'profile_pic';
			$path_to_save = base_path() . '/public/images/users/';						
			$profile_pic = app('App\Http\Controllers\Front\GalleryController')->saveImage($request,$path_to_save,$input_field_name);
			$user->temp_profile_pic = $profile_pic;
			$user->profile_pic_approved = 'n'; 

				//send sms to admin
           $message = Lang::get('messages.profile_pic_updated');
             $setting = Setting::first(); 
			 RoomFinderFunctions::SendSmsMessage($setting->admin_mobile_number,$message,$setting->admin_country_code);
		    }
        }

			$user->fill([
				'first_name' => $data['first_name'], 
				'last_name' => $data['last_name'],
				'email' => $data['email'],
				'bio' => $data['bio'],
				])->save();
			if( ($user->mobile_country_code != $data['mobile_country_code']) or ($user->mobile_number != $data['mobile_number'] )){
				//$user->fill(['mobile_verified' => 0])->save();
				$return  = $this->verifyMobile($request);
				return $return; 
			}else{

                
              if($user->user_type == 'c' && $request->profile_pic){

				return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.user_profile_pic_updated') ) );

              }else{



				return \Response::json(array(  'error' => false,  'message' => Lang::get('messages.success') ) );

              }

			}

		}




		public function feedback(Request $request){
			$input = $request->all();
			$v = \Validator::make($input,[
				'user_id' =>'required|numeric|exists:users,id',
				'email' => 'required|email',
				'subject' => 'required',
				'feedback' => 'required'
				]
				);
			if ($v->fails())
			{	
				$msg = array();
				$messages = $v->errors();			
				foreach ($messages->all() as $message) {
					return \Response::json(array(  'error' => true,  'message' => $message ) );
				}
			}
			$setting = Setting::first();	
						
			$email_array['to_email'] = $setting->site_email; 	
			//$email_array['to_email'] = 'es.pradeeparyal@gmail.com';			
			$email_array['to_name'] = 'Admin';
			$email_array['subject'] = 'You have received an enquiry/feedback from RCC with subject :'.$input['subject']; 
			$email_array['message'] = 'Hi Admin, Please find the feedback below from user: <br><br>'.$input['feedback']."<br><br>My Email is: ".$input['email']; 	

			RoomFinderFunctions::sendEmail($email_array);
			$result = User::saveFeedback($input);
			
			return \Response::json(array(  'error' => $result['error'],  'message' => $result['message'] ) );
		}

		public function checkLicenceStatus(Request $request){
   	    $input = $request->all();
	    $v = \Validator::make($input,[
				'user_id' =>'required|numeric|exists:users,id',
				]
				);
		if ($v->fails())
			{	
				$msg = array();
				$messages = $v->errors();			
				foreach ($messages->all() as $message) {
					return \Response::json(array(  'error' => true,  'message' => $message ) );
				}
			}
	    $user = User::findOrFail($request->user_id);
		if($user){
					return \Response::json( array ( 'error' => false , 'licence_status' => $user->licence_verified ) );
               }
              else{
		return \Response::json( array ( 'error' => true , 'message' => "Invalid User ID." ) );
			}
	

             }


		


	}
