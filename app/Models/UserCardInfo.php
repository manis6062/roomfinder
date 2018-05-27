<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCardInfo extends Model
{
    protected $table = 'user_payment_accounts'; 
    protected $fillable = array('user_id','name','DOB','nationality','country','post_code','address1',
    	'address2','city','credit_card_number','exp_month','exp_year','cvv','status','omise_customer_id');
    
    public function user(){
    	return $this->belongsTo('App\Models\User','user_id','id');
    }
    
}
