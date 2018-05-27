<?php

namespace App\Http\Controllers\Admin;



use Illuminate\Http\Request;



use App\Http\Requests;

use App\Http\Controllers\Controller;

//use App\Licence;

use App\Models\User;

use App\Models\Country;

use App\Library\CarRentFunctions;

use App\Models\Notification;

use App\Models\Licence;

use DB,File;



class LicenceController extends Controller

{

	public function index()

	{

		$licences = DB::table('user_licences as l')

		->select('l.*','u.first_name','u.last_name','u.id as user_id','u.licence_verified','c.nicename AS licence_country','cc.nicename AS passport_country')

		->join('countries as c','c.id','=','l.licence_country')

		->join('users as u','u.id','=','l.user_id')

		->leftJoin('countries as cc','l.passport_country','=','cc.id')

		->orderBy('l.id','desc')

		->paginate(50);

		return view('admin.licences.index',compact('licences'));

	}

	public function edit(User $user){	

		$countries = Country::select(array('id','nicename'))->get();

		return view('admin.licences.edit',compact('user','countries'));

	}



	public function deleteLicence(Request $request,$id){

		$data['id'] = $id;

		$v = \Validator::make($data,[			

			'id' => 'required|exists:user_licences,id',	

			]

			);

		if($v->fails()){

			return redirect()->back()->withInput()->withErrors($v->errors());

		}

		$licence_image_path = base_path() . '/images/licences/';	

		$licence = Licence::find($id);

		

		

		if($licence->licence_front_image!=''){

			$full_path = $licence_image_path.'full/'.$licence->licence_front_image; 

			$thumb_path = $licence_image_path.'thumb/'.$licence->licence_front_image; 

			@unlink($full_path); 

			@unlink($thumb_path); 

		}

		$user = DB::table('users')->where('id',$licence->user_id)->update(['licence_verified'=>0]); 

		

		

		$licence->delete(); 

		\Session::flash('flash_success', 'User licence successfully deleted.');       

		return redirect('admin/licences'); 

	}





	public function update(Request $request,$user_id){

		$data = $request->all();

		$data = array_filter($data, 'strlen');	

		$data['user_id'] = $user_id;		

		$v = \Validator::make($data,[			

			'user_id' => 'required|exists:users,id',		

			'is_thai_licence' =>'in:0,1',

			'licence_number' => 'required',

			'licence_country' => 'required_if:is_thai_licence,0|numeric|exists:countries,id',

			'first_licence_date' =>'date_format:Y-m-d',

			'licence_expire' =>'date_format:Y-m-d',

			'passport_number' =>'required_if:is_thai_licence,0',					

			'passport_country' => 'required_if:is_thai_licence,0|numeric|exists:countries,id',

			'licence_front_image' =>'image|max:5120',

			'licence_back_image' =>'image|max:5120',

			'licence_verified' => 'required|in:0,1,2,3',  

			'rejection_reason' => 'required_if:licence_verified,3', 

			'DOB' =>'date_format:Y-m-d',



			]

			);

		if($v->fails()){

			return redirect()->back()->withInput()->withErrors($v->errors());

		}

		$user_array = array();

		$licence_array = array();

		$user = User::find($user_id);

		$set = "";

		if(isset($data['is_thai_licence'])){

			$licence_array[] = $data['is_thai_licence'];

			$set.=" is_thai_licence = ?";

		}

		if(isset($data['licence_country'])){

			$licence_array[] = $data['licence_country'];

			$set.=" ,licence_country = ?";

		}

		if(isset($data['first_licence_date'])){

			$licence_array[] = $data['first_licence_date'];

			$set.=" ,first_licence_date = ?";

		}

		if(isset($data['expire_date'])){

			$licence_array[] = $data['expire_date'];

			$set.=" ,expire_date = ?";

		}

		if(isset($data['licence_number'])){

			$licence_array[] = $data['licence_number'];

			$set.=" ,licence_number = ?";

		}

		if(isset($data['passport_number'])){

			$licence_array[] = $data['passport_number'];

			$set.=" ,passport_number = ?";

		}

		if(isset($data['passport_country'])){

			$licence_array[] = $data['passport_country'];

			$set.=" ,passport_country = ?";

		}	



		$path_to_save = base_path() . '/images/licences/';		

		$front_image = $back_image = null;		

		if($request->licence_front_image){

			$input_field_name = 'licence_front_image';					

			$front_image = app('App\Http\Controllers\Admin\GalleryController')->saveImage($request,$path_to_save,$input_field_name);

			$set.=" ,licence_front_image = ?";

			$licence_array[] = $front_image;

		}	

		if($request->licence_back_image){

			$input_field_name = 'licence_back_image';

			$back_image = app('App\Http\Controllers\Admin\GalleryController')->saveImage($request,$path_to_save,$input_field_name);

			$set.=" ,licence_back_image = ?";

			$licence_array[] = $back_image;

		}





		if( $request->licence_verified == 3 ){

            //if licece is put to not verified or is rejected status, cancel the booking that is in pending status for this user

			DB::table("car_bookings")->where("user_id",$user_id)->where("status","pending")->update(['status'=>'cancelled','cancellation_reason' => 'Auto Cancelled after licence is rejected']);  

		}

		/*echo $request->licence_verified; 

		echo "<br>";

		echo $user->licence_verified; 

		die;*/



        if( ($request->licence_verified == 1 or $request->licence_verified == 3) and $user->licence_verified == 2 ){ 

        /*******if currenlty pending and admin approves/rejects it, we need to inform user by sending sms/email and dashboard notification****/



        	

        	if($request->licence_verified == 1){

         		$slug = 'user-licence-approved';  

         	}else{

         		$slug = 'user-licence-rejected';  

         	}           

            $user_full_name = $user->first_name . ' ' . $user->last_name;
           

            $replace_array["user_full_name"] = $user_full_name;

            $lang = ($user->lang)?$user->lang:'en';           

         	

         

            if($replaced_content = CarRentFunctions::getEmailContentSubject(['lang' => $lang,'slug' => $slug,'replace_array' => $replace_array])){                

         		

         		$subject = $replaced_content['subject'];

                $content = $replaced_content['content'];  

                //$email_array['to_email'] = 'es.pradeeparyal@gmail.com';

	     		$email_array['to_email'] = $user->email;

	     		$email_array['to_name'] = $user_full_name;

	     		$email_array['subject'] = $subject;

	     		$email_array['message'] = $content;

	     		CarRentFunctions::sendEmail($email_array);

     			

            	/****************insert notification to the user for dashboard notification view***************/



     			$content_link = '#';          

	     		/*$noti_id = Notification::create([

	     			'notification_type' => 'system',

	     			'user_id' => $user->id,

	     			'content_eng' => $se,

	     			'content_thai' => $st,

	     			'content_link' => '#',

	     			'mobile_target' => $mobile_target,

	                'mobile_target_id' => $user->id,

	     			'is_read' => 0

	     			])->id;*/



	     		$mobile_target = 'user_profile';     



                $noti_params['user_id'] = $user->id;

                $noti_params['mobile_target'] = $mobile_target;

                $noti_params['mobile_target_id'] = $user->id;    

                $noti_params['slug'] = $slug;    

                $noti_params['notification_type'] = 'system';           

                $noti_params['replace_array'] = $replace_array; 

                $noti_id = Notification::createNotification($noti_params);

                



	     		$user->licence_verified = $request->licence_verified; 

	     		$user->save();

	            $device_tokens_ios = CarRentFunctions::getGoogleDeviceTokens($user->id,'ios');

	            $device_tokens_andriod = CarRentFunctions::getGoogleDeviceTokens($user->id,'android');

	            if(!empty($device_tokens_ios)){

	                $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_ios,$noti_id,'ios'); 

	            }

	            if(!empty($device_tokens_andriod)){

	                $noti_result = CarRentFunctions::createAndSendNotification($device_tokens_andriod,$noti_id,'android');     

	            }	           

	            CarRentFunctions::SendSmsMessage($user->mobile_number,$subject,$user->mobile_country_code); 

        	}

    	}



        $res = DB::table('user_licences')->where('user_id', $user_id)->first();		

		if($res){ 

			// if already exists, update it, else insert in user_licences table



			//remove old images if new one is uploaded

			if($front_image and $res->licence_front_image){

				File::delete($path_to_save.'full/'.$res->licence_front_image);

				File::delete($path_to_save.'thumb/'.$res->licence_front_image);

			}

			if($back_image and $res->licence_back_image){

				File::delete($path_to_save.'full/'.$res->licence_back_image);

				File::delete($path_to_save.'thumb/'.$res->licence_back_image);

			}



			$SQL = "UPDATE user_licences SET ".$set." where user_id = ? ";	 





			$licence_array[] = $user_id;

			//var_dump($licence_array); die;

			//if admin is approving the licence, then send email/sms and dashboard notification to user

			DB::update($SQL,$licence_array);



		}else{

			$set.=" ,user_id = ?";

			$licence_array[] = $user_id;				

			$SQL = "INSERT INTO user_licences SET ".$set;				

			DB::insert($SQL,$licence_array);

		}

		//update the user information about licence

		$user_array['licence_verified'] = $request->licence_verified;

		$user_array['rejection_reason'] = $request->rejection_reason;

		$user->fill($user_array)->save();



		\Session::flash('flash_success', 'User licence successfully edited.');       

		return redirect('admin/users/'.$user_id.'/licence'); 

	}

}

