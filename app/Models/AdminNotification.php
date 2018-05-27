<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';
    protected $fillable = ['notification_type','user_id','content','is_read','content_link'];
    
}
