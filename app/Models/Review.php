<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;
class Review extends Model
{
    //
    protected $table = 'car_reviews';
    protected $fillable = ['review','rating_given','status','booking_id','car_id','user_id'];
    public function car(){
    	return $this->belongsTo('App\Models\Car','car_id','id');
    }
    public function user(){
    	return $this->belongsTo('App\Models\User','user_id','id');
    }
    public function booking(){
    	return $this->belongsTo('App\Models\Booking','booking_id','id');
    }
    public static function canReview($data){
        $res = Review::select('id','car_id')->where('booking_id',$data['booking_id'])->first();
        if($res){
            return false;
        }else{
            return true;
        }
    }
    
}
