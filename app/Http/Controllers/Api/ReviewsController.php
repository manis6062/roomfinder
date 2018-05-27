<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB,Lang;
use App\Models\Review; 
use App\Models\Booking; 
class ReviewsController extends Controller
{
	public function addReview(Request $request){
        $input = $request->all();
        
        $v = \Validator::make($input,   [ 
            'user_id' => 'required|numeric|exists:users,id',
            'booking_id' => 'required|unique:car_reviews,booking_id|numeric|exists:car_bookings,id,user_id,'.$input['user_id'].',status,completed',
            'review' => 'required',
            'rating_given' => 'required|between:1,5',           
            ] );        
        if ($v->fails())
        {   
            $msg = array();
            $messages = $v->errors();           
            foreach ($messages->all() as $message) {
                return \Response::json(array(  'error' => true,  'message' => $message ) );
            }  
        }
        $booking = Booking::find($input['booking_id']);
        
       // Review::where('booking_id',$input['booking_id'])->where('user_id',$input['booking_id'])->first
        Review::create(array(
                'car_id' => $booking->car_id,
                'user_id' => $input['user_id'],
                'booking_id' => $input['booking_id'],
                'review' => $input['review'],
                'rating_given' => $input['rating_given'] )
            );
        return \Response::json(array(  'error' => false, 'message' =>Lang::get('messages.success') ) ); 
        
    }  
   
    
}
