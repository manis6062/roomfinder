<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarFeature extends Model
{
    protected $table = 'features';
    protected $fillable = [
    'title_eng', 'title_thai','updated_at'
    ];
    public static function store($data){
    	self::create([
    		'title_eng' 		=> $data['title_eng'],
    		'title_thai' 		=> $data['title_thai']
    	]);
    }
    
}
