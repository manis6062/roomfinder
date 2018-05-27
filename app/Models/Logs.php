<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = 'logs';
    protected $fillable = ['user_id','loc_lat','loc_lon','device_type','device_id'];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }
    
    public static function storeLog(array $data)
    {
      self::create(array(
                        'user_id' => $data['user_id'],
                        'loc_lat' => @$data['loc_lat'],
                        'loc_lon' => @$data['loc_lon'],
                        'device_type' => @$data['device_type'],
                        'device_id' => @$data['device_id']
                    ));
    } 
}
