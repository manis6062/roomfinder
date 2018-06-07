<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyFavourite extends Model
{
   protected $table = 'userfavourites';

   protected $fillable = array( 
    'room_id','jagga_id','user_id');
}
