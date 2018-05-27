<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting'; 
    protected $fillable = array('first_more_sth','second_more_sth','third_more_sth','more_more_sth','first_less_sth','second_less_sth','third_less_sth','more_less_sth','admin_second_mobile_number','admin_second_mobile_country_code','processing_fee','booking_rejection_charge','insurance_percent','mileage_limit_per_day','mileage_limit_per_week','mileage_limit_per_month','site_email','admin_country_code','rental_fee_percent','tax_percent','admin_mobile_number','extra_charge_per_mile' , 'bio_restriction_words' , 'car_info_restriction_words' , 'pickup_instruction_restriction' , 'default_first_name' , 'default_last_name' , 'default_profile_pic');    
}
