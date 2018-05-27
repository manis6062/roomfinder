<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $table = 'car_models';
    protected $fillable = [
    'make_id','title_eng','status','car_model_url','car_model_featured'
    ];

    public function make(){
        return $this->belongsTo('App\Models\MakeModel','make_id');
    }
    public static function store($data){
        //var_dump($data); die;
    	return self::create([
    		'make_id'				=>	$data['make_id'],	
    		'title_eng'				=>	$data['title_eng'],	
            'car_model_url'         =>  $data['car_model_url'],            
    		'status'				=>	$data['status']
    	])->id;
    }
}
