<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 


class Notification extends Model
{
     use SoftDeletes;
  protected $dates = ['created_at', 'updated_at', 'deleted_at'];

  protected $table = 'notifications';

  protected $fillable = array( 
    'user_id', 'mobile_target_id' , 'type' , 'is_read' , 'message');
}
