<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\UserEarningAccountInfo;
use App\Models\User;
use App\Models\Bank;
use Lang,CreditCard,DB;

require_once(app_path().'/loadomise.php'); 
class EarningAccountController extends Controller
{

	public function save(Request $request){
		$data = $request->all();	
		

		$v = \Validator::make($data,[				
				'user_id' => 'required|numeric|exists:users,id',
				'account_name' => 'required',
				'email' => 'required|email',
				'account_type' => 'required|in:individual,corporation',				
				'account_number' => 'required',
				'bank_brand' => 'required'
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
		$recipient_array['name'] = $request->account_name;
		$recipient_array['email'] = $request->email;
		$recipient_array['type'] = $request->account_type;
		if($request->tax_id){
			$recipient_array['tax_id'] = $request->tax_id;
		}	
		if($request->description){
			$recipient_array['description'] = $request->description;
		}
		$bank_account_array['brand'] = $request->bank_brand; 
		$bank_account_array['number'] = $request->account_number;
		$bank_account_array['name'] = $request->account_name; 
		$recipient_array['bank_account'] = $bank_account_array; 
		
		$user_deposite = UserEarningAccountInfo::where('user_id',$data['user_id'])->first();  
		if($user_deposite){
			$recipient = \OmiseRecipient::retrieve($user_deposite->omise_recipient_id);
			$recipient->update($recipient_array);
		}else{
			$recipient = \OmiseRecipient::create($recipient_array);
			//dd($recipient);
			
			$array = array(
				'user_id'=>$data['user_id'],
				'omise_recipient_id'=>$recipient['id'],
				'status'=>'active'
			);
			UserEarningAccountInfo::create($array);
			$user = User::find($request->user_id); 
			$user->deposite_info_updated = 1;
			$user->save();
		}
		return \Response::json(array(  'error' => false,  'message' => Lang::get('user.deposite_account_info_updated')  ) );				
	}

	public function getDepositeAccountInfo(Request $request){
		$data = $request->all();		
		$lang = \App::getLocale();

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
		
		$field = "b.bank_name_eng as bank_title";
		if($lang == 'th'){
			$field = "b.bank_name_thai as bank_title";
		}

       
         $user_deposite = UserEarningAccountInfo::where('user_id',$request->user_id)->first(); 

         if($user_deposite){

         	$recipient = \OmiseRecipient::retrieve($user_deposite->omise_recipient_id);
         	
         	
         	$bank = Bank::where('bank_brand',$recipient['bank_account']['brand'])->first();
         	
         	
         	$bank_name = $bank->bank_name_eng;
         	if($lang == 'th'){
         		$bank_name = $bank->bank_name_thai;
         	}
         	$result = array(
         		'bank_name' => $bank_name,
         		'bank_id'	=> $bank->id,
         		'account_number' => $recipient['bank_account']['last_digits'],
         		'bank_brand'	=> $recipient['bank_account']['brand'],
         		'name'	=> $recipient['bank_account']['name'],
         		'tax_id' => (is_null($recipient['tax_id'])) ? '' : $recipient['tax_id'],
         		'email' => $recipient['email'],
         		'description' => (is_null($recipient['description'])) ? '' : $recipient['description'],
         		'account_type' => $recipient['type']
         		); 
         	return \Response::json(array(  'error' => false, 'result' => $result ) );
         }else{
         	return \Response::json(array(  'error' => true, 'message' => Lang::get('messages.resultnotfound')  ) );
         }
            

    		
       
	}
	
}
