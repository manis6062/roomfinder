<?php

namespace App\Models;



use Illuminate\Foundation\Auth\User as Authenticatable;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes; 


class User extends Authenticatable

{

    /**

     * The attributes that are mass assignable.

     *

     * @var array

     */
    use SoftDeletes;
  protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = [

    'name','email', 'password','profile_pic','email','status'
    ];

    //protected $table = 'tbl_users'; 



    /**

     * The attributes that should be hidden for arrays.

     *

     * @var array

     */

    protected $hidden = [

    'password', 'remember_token',

    ];



  public static function fbstore(array $data)

  {

       // return \Response::json('just before creating user' ); 

        //dd($data); 

     // $user_data['first_name'] = 

      return self::create([

      

        'email'                             =>  $data['email'],    

        'profile_pic'                       =>  @$data['profile_pic'], 

        'status'                            =>  $data['status'],  

        ])->id;



  } 

  public static function setAppSession($data){

    //dd($data); 

    //check if already logged in

    

    /*$SQL = "SELECT access_token from user_sessions WHERE user_id = ? and device_type = ? and device_id = ? and fb_device_token = ? LIMIT 1";

    $param = array($data['user_id'],$data['device_type'],$data['device_id'],$data['fb_device_token']);*/



    $SQL = "SELECT access_token from user_sessions WHERE fb_device_token = ? LIMIT 1";

     $param = array(@$data['fb_device_token']);

    

    $created_at = date("Y-m-d H:i:s",time());  

    $updated_at = date("Y-m-d H:i:s",time());  

    $res = DB::select($SQL,$param);

    if(!$res){

      $SQL = "INSERT INTO user_sessions SET 

              user_id = ?,

              device_type = ?,

              device_id = ?,

              access_token = ?,

              fb_device_token = ?,

              created_at = ?,

              updated_at = ?";


      $param = array($data['user_id'],@$data['device_type'],@$data['device_id'],$data['access_token'],@$data['fb_device_token'],$created_at,$updated_at);       

      DB::insert($SQL,$param);

      return $data['access_token']; 

    }else{

      $SQL = "UPDATE user_sessions SET   

              fb_device_token = ?,              

              updated_at = ?

             WHERE user_id = ? and device_type = ? and device_id = ?";

      $param = array(

          $data['fb_device_token'],

          $updated_at,

          $data['user_id'],

          $data['device_type'],

          $data['device_id'],

        );       

      DB::update($SQL,$param);

      return $res[0]->access_token;

      //return false;

    }

  }



  public static function logout($data){

      $SQL = "DELETE from user_sessions WHERE access_token = ?";

    $param = array($data['access_token']);        

    if(DB::delete($SQL,$param)){

      return true;

    }else{

      return false;

    }

  }




  public static function getProfilePicByUserId($id){

    $user_img_path = env("BASE_URL")."images/users/full/";

    $user = User::find($id);

    $profile_pic = '';

    if($user->profile_pic){

      $profile_pic =  $user_img_path.$user->profile_pic;

    }else{

      $profile_pic = url('images/global/users/default-avatar.png');

    }

    return $profile_pic; 

  }



}

