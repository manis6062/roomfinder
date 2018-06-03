<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spam extends Model
{
   protected $table = 'spam';

   protected $fillable = array( 
    'user_id','room_id','read' , 'jagga_id' , 'complains');
}
