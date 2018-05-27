<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';
    protected $fillable = ['bank_name_eng','bank_name_thai','status','bank_brand'];

    public static function store($data){
    	return self::create([
    		'bank_name_eng'				=>	$data['bank_name_eng'],	
    		'bank_name_thai'				=>	$data['bank_name_thai'],
            'bank_brand'             => $data['bank_brand'],
            'status'             =>  $data['status']
    	])->id;
    }

}
