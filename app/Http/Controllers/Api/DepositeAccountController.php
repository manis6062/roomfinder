<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\UserEarningAccountInfo;
use App\Models\User;
use Lang,CreditCard;
class DepositeAccountController extends Controller
{
	public function save(Request $request){
		$data = $request->all();			
		$v = \Validator::make($data,[				
				'user_id' => 'required|numeric|exists:users,id',
				'account_name' => 'required',
				'DOB' => 'required|numeric',
				'nationality' => 'required|numeric|exists:countries,id',
				'country' => 'required|numeric|exists:countries,id',
				'post_code' => 'required',
				'address1' => 'required',
				'address2' => 'required',
				'city' =>'required',
				'account_type' => 'required|in:Saving,Current',
				'sort_code'=>'required',
				'account_number' => 'required',
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
		

		$array = array(
			'user_id'=>$data['user_id'],
			'account_name'=>$data['account_name'],
			'account_number' =>$data['account_number'],
			'DOB'=>date('Y-m-d',$data['DOB']),
			'nationality'=>$data['nationality'],
			'country'=>$data['country'],
			'post_code'=>$data['post_code'],
			'address1'=>$data['address1'],
			'address2'=>$data['address2'],
			'city'=>$data['city'],	
			'account_type' => $data['account_type'],
			'sort_code'=>$data['sort_code'],
			'sort_code'=>$data['sort_code'],
			'status'=>'active'
		);
		
		//$user_card = UserCardInfo::where('user_id',$array['user_id'])->first();	
		try{
			
            $user_deposite = UserDepositeAccountInfo::where('user_id',$array['user_id'])->firstOrFail();  
    		$user_deposite->fill($array)->save();
    		
        }catch(ModelNotFoundException  $e){
        	echo "asdf"; die;
    		UserDepositeAccountInfo::create($array);
        }   
		return \Response::json(array(  'error' => false,  'message' => Lang::get('user.deposite_account_info_updated')  ) );				
	}

	public function getDepositeAccountInfo(Request $request){
		$data = $request->all();
		$v = \Validator::make($data,[				
				'user_id' => 'required|numeric|exists:users,id',
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
		try{
            $user_deposite = UserDepositeAccountInfo::where('user_id',$data['user_id'])->firstOrFail();  
            return \Response::json(array(  'error' => false, 'result' => $user_deposite ) );
    		
        }catch(ModelNotFoundException  $e){
    		return \Response::json(array(  'error' => true, 'message' => Lang::get('user.invaliduser')  ) );
        }   
	}
	
}
