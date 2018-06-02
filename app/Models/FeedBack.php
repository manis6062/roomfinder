<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FeedBack extends Model
{
    protected $table = 'feedback';

   protected $fillable = array( 
    'user_id','feeback','read');
}
