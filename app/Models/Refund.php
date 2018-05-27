<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;
use App\Models\Booking;
use App\Models\Refund;
require_once(app_path().'/loadomise.php'); 
class Refund extends Model
{
    protected $table = 'refunded_payments';
    protected $fillable = ['omise_refund_id','payment_id','refund_charges','description','amount_refunded','status'];

    public static function createRefund($booking_id,$is_rejected = false,$desc = NULL){
        try{
            $booking = Booking::find($booking_id); 
            $payment = Payment::select('id','omise_charge_id')->where('booking_id',$booking->id)->first(); 
            $charge = \OmiseCharge::retrieve($payment->omise_charge_id);
            if(isset($charge->failure_code)){
                return array('error' => true,'message' => $charge->failure_code); 

            }
           // dd($charge); die;
                       
            $paid_amount = ($booking->rental_price + $booking->rental_fee + $booking->tax_amount + $booking->delivery_fee + $booking->processing_fee); 
            $refund_amount = ceil(($paid_amount * 100)); 
            
           
            $refund = $charge->refunds()->create(array('amount' => $refund_amount));

            $description = 'Cancelled by car booking user';

            if($is_rejected){
                $description = 'Booking rejected by car owner'; 
            }
            if($desc){
                $description = $desc; 
            }
            Refund::create(array(
                    'payment_id' => $payment->id,                   
                    'omise_refund_id' => $refund['id'],
                    'description' => $description,
                    'status' => 'completed'
                ));
            return array('error' => false);     

        }catch(\Exception $e){
            return array('error' => true,'message' => $e->getMessage()); 
        }
    	
    }
}
