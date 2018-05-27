<?php
namespace App\Http\Controllers\Api\V1;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\UserCardInfo;
use App\Models\User;
use App\Library\CarRentFunctions;
use Lang; 
use DB;
use App\Models\Setting; 
require_once(app_path().'/loadomise.php');
class UserCardController extends Controller
{
	public function save(Request $request){
		$data = $request->all();	
		$v = \Validator::make($data,[				
				'user_id' => 'required|numeric|exists:users,id',
				'token_id' => 'required',
				'user_address' => 'required'
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
		$user = User::find($request->user_id); 
		$user->user_address = $request->user_address; 
		$user->save();
		$array['user_id'] = $data['user_id'];
		$card_info = UserCardInfo::where('user_id',"=",$array['user_id'])->first(); 

		try{
			if($card_info){ 
				$customer = \OmiseCustomer::retrieve($card_info->omise_customer_id);

				$card = $customer->getCards()->retrieve($customer['default_card']);
				//dd($card['data']);
				if(!empty($card['data'])){
					$card->destroy();
					if($card->isDestroyed()){
						$customer->update(array(
					  	'card' => $request->token_id
						));
					}
				}else{
					$customer->update(array(
				  	'card' => $request->token_id
					));
				}
				//dd('card tesrt');
				//we need to check the updated card
				$customer = \OmiseCustomer::retrieve($card_info->omise_customer_id);
				$charge = \OmiseCharge::create(array(
				  	'amount' => 2000, // 20 bhat
				  	'currency' => 'thb',
				  	'customer' => $customer['id'],
				  	'description' => 'Charging for card authorization'
				));
				/*$charge['failure_code'] = true; 
				$charge['failure_message'] = 'test'; */
				if(isset($charge['failure_code'])){ 				
				//remove the user card info from user card info table
					User::where('id',$data['user_id'])->update(['payment_info_updated' => 0]);					
					UserCardInfo::where('user_id',$data['user_id'])->delete(); 				
					return \Response::json(array(  'error' => true,  'message' => $charge['failure_message']  ) );
				}else{	
					//check the new card is debit or credit
					$card = $customer->getCards()->retrieve($customer['default_card']);
					$setting = Setting::first();
					if($card['financing'] == "" or $card['financing'] == "debit"){
						CarRentFunctions::SendSmsMessage($setting->admin_mobile_number,"User ID: ".$data['user_id']."entered a card which is not a Credit Card. Please validate",$setting->admin_country_code);
					}
					User::where('id',$data['user_id'])->update(['payment_info_updated' => 1]);
					return \Response::json(array(  'error' => false,  'message' => Lang::get('user.card_info_updated')  ) );
				}
			}else{
				$customer = \OmiseCustomer::create(array(
				  'email' => $user->email,
				  'description' => $user->bio,
				  'card' => $request->token_id
				));
				
				$charge = \OmiseCharge::create(array(
				  	'amount' => 2000, // 20 bhat
				  	'currency' => 'thb',
				  	'customer' => $customer['id'],
				  	'description' => 'Charging for card authorization'
				));
				
				if(isset($charge['failure_code'])){ // if returns failure code, do not store the card
					//echo $customer['failure_message']; die;
					User::where('id',$data['user_id'])->update(['payment_info_updated' => 0]);	
					UserCardInfo::where('user_id',$data['user_id'])->delete(); 		
					return \Response::json(array(  'error' => true,  'message' => $charge['failure_message']  ) );		
				}else{
					$array['omise_customer_id'] = $customer['id']; 
					UserCardInfo::create($array);					
					User::where('id',$data['user_id'])->update(['payment_info_updated' => 1]);
					return \Response::json(array(  'error' => false,  'message' => Lang::get('user.card_info_updated')  ) );	
				}
			}
		}catch(\Exception $e){		
			return \Response::json(array(  'error' => true,  'message' => $e->getMessage()  ) );	
		}
	}

	public function getPaymentInfo(Request $request){
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
		
		$user = User::find($data['user_id']);
		if($user->payment_info_updated == 1){
			try{
				$card_info = UserCardInfo::where('user_id',"=",$data['user_id'])->first(); 
				if($card_info){
	         		$customer = \OmiseCustomer::retrieve($card_info->omise_customer_id);
	         		$card = $customer->getCards()->retrieve($customer['default_card']);
	         		$result = array(
	         			'name' => $card['name'],
	         			'credit_card_number'	=> $card['last_digits'],
	         			'post_code' => $card['postal_code'],
	         			'city'	=> $card['city'],
	         			'exp_month'	=> $card['expiration_month'],
	         			'exp_year' => $card['expiration_year'],
	         			'cvv' => '',
	         			'user_address' => $user->user_address
	         		);
	         	}else{
	         		return \Response::json(array(  'error' => true, 'message' => Lang::get('messages.ccresultnotfound')  ) );
	         	}	 
	         	return \Response::json(array(  'error' => false, 'result' => $result ) );
         	}catch(\Exception $e){
         		return \Response::json(array(  'error' => true, 'message' => $e->getMessage() ) );
         	}
         }else{
         	return \Response::json(array(  'error' => true, 'message' => Lang::get('messages.ccresultnotfound')  ) );
         }
		
	}
	
	public function inform(Request $request){
		dd($request->all());
	}
}
