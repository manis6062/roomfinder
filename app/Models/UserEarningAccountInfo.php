<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UserEarningAccountInfo extends Model
{
    protected $table = 'user_earning_accounts'; 
    protected $fillable = array('user_id','omise_recipient_id','account_type','status');    
}
