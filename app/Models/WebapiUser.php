<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB,Lang;
//use App\Models\Feedback;
class WebapiUser extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'webapi_users';
    protected $fillable = [
    'full_name', 'email', 'password','mobile_number','website_url','api_key','created_at','updated_at','website_name','status'
    ];
    
    public static function store(array $data)
    {

      return self::create([

        'full_name'                         => $data['full_name'],
        'email'                             => $data['email'],
        'password'                          => bcrypt($data['password']), 
        'mobile_number'                     => $data['mobile_number'],
        'website_name'                      => $data['website_name'],
        'website_url'                       => $data['website_url'], 
        'status'                            => $data['status'],    
        ])->id;
      

    } 
}
