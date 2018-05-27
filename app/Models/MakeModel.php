<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MakeModel extends Model
{
    protected $table = 'car_make';
    protected $fillable = [
    'title_eng','car_make_url','car_make_logo','car_make_featured','car_make_heading_en','car_make_heading_th','car_make_details_en','car_make_details_th','status'
    ];
    public static function store($data){
    	self::create([
    		'title_eng' 		=> $data['title_eng'],    	
    		'car_make_url' 		=> $data['car_make_url'],   
            'car_make_logo'     => $data['photo']   		
    	]);
    }

}
