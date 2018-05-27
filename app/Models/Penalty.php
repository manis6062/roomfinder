<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;
require_once(app_path().'/loadomise.php'); 
class Penalty extends Model
{
    protected $table = 'penalty_charges';
    protected $fillable = ['booking_id','omise_charge_id','amount','description','user_id'];

    public function booking(){
        return $this->belongsTo('App\Models\Booking','booking_id','id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
    public static function chargePenalty($data){

    	$booking = Booking::find($data['booking_id']); 
	    $total_chargeable_amount = ($data['amount'] * 100); // convert into small unit like cent
	    $omise_customer_id = NULL; 
	    if($data['booking_status'] == 'cancelled'){
	    	$omise_customer_id = @$booking->user->card_info->omise_customer_id;
	    }else{
	    	$omise_customer_id = @$booking->car->user->card_info->omise_customer_id;
	    }

	   	try{
		    $charge = \OmiseCharge::create(array(
		      'amount' => $total_chargeable_amount,
		      'description' => $data['description'],
		      'currency' => 'thb',
		      'customer' => $omise_customer_id
		    ));
		    $penalty = new Penalty();
		    $penalty->booking_id = $data['booking_id'];
		    $penalty->amount = $data['amount'];
		    $penalty->omise_charge_id = @$charge['id'];
		    $penalty->description = $data['description'];
		    $penalty->save();
		}catch(\Exception $e){
			return array(  'error' => true, 'message' =>$e->getMessage()); 
		}

	    if(isset($charge['failure_code'])){
	        return array(  'error' => true, 'message' =>$charge['failure_message']);    
	    }else{
	    	return array(  'error' => false, 'result' => $charge);    
	    }        
    }
}
