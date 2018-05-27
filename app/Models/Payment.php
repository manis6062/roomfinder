<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;
use App\Models\Setting; 
use App\Library\CarRentFunctions;
require_once(app_path().'/loadomise.php'); 
class Payment extends Model
{
    protected $table = 'received_payments';
    protected $fillable = ['booking_id','omise_charge_id'];
    public function refund(){
        return $this->hasOne('App\Models\Refund','payment_id');
    }

    public static function chargeCard($booking_id){
    	$setting = Setting::first();
    	$booking = Booking::find($booking_id);
    	$total_chargeable_amount = ($booking->rental_price + $booking->rental_fee + $booking->delivery_fee +  $booking->processing_fee + $booking->tax_amount);
    	$total_chargeable_amount = round($total_chargeable_amount, 2);
    	//dd($total_chargeable_amount);   
	    $total_chargeable_amount = ($total_chargeable_amount * 100); // convert into small unit like cent
	    //dd($booking->user); 
	   	try{
		    $charge = \OmiseCharge::create(array(
		      'amount' => $total_chargeable_amount,
		      'currency' => 'thb',
		      'customer' => $booking->user->card_info->omise_customer_id
		    ));
		}catch(\Exception $e){
			CarRentFunctions::SendSmsMessage($setting->admin_mobile_number,"Issue with charging card for booking id: ".$booking_id,$setting->admin_country_code);  
			return array(  'error' => true, 'message' =>$e->getMessage()); 
		}

	    if(isset($charge['failure_code'])){
	    	CarRentFunctions::SendSmsMessage($setting->admin_mobile_number,"Issue with charging card for booking id: ".$booking_id,$setting->admin_country_code);  
	        return array(  'error' => true, 'message' =>$charge['failure_message']);    
	    }else{
	    	return array(  'error' => false, 'result' => $charge);    
	    }        
    }

}
