<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OwnersPayment extends Model
{
    protected $table = 'paid_payments';
    protected $fillable = ['booking_id','omise_transfer_id','description'];    
}
