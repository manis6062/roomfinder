<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarEstimatedValues extends Model
{
    //
    protected $table = 'car_estimated_values'; 
    protected $fillable = ['title','value','default_price_per_day','status'];

    public static function store($data){
    	$ev =  self::create([
    		'title' 						=> $data['estimated_value'],
    		'value' 						=> $data['estimated_value'],
    		'default_price_per_day' 		=> ($data['estimated_value']/1000)
    	]);
        return $ev->id; 
    }
}
