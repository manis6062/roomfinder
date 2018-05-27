<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    protected $fillable = ['user_id', 'feedback','email', 'subject'];

    public function user(){
      return $this->belongsTo('App\Models\User','user_id');
    }
}
