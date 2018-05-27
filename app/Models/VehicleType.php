<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    //
    protected $table = 'vehicle_types';
    protected $fillable = ['title_eng','title_thai','status','icon'];

    public static function store($data){
    	return self::create([
    		'title_eng'				=>	$data['title_eng'],	
    		'title_thai'				=>	$data['title_thai'],
    		'icon'				=> $data['icon'],
            'status'             =>  $data['status']
    	])->id;
    }

}
