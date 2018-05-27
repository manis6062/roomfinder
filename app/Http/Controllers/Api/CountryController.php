<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Country;

class CountryController extends Controller
{
    public function index(){
    	//echo "here"; die;
    	$countries = Country::select(array('id','nicename as name'))->orderBy('ordering','asc')->get();
    	if($countries){
    		return \Response::json(array(  'error' => false,   'result' => $countries  ) );
    	}else{
			return \Response::json(array(  'error' => true,   'message' => Lang::get('messages.resultnotfound')  ) );
    	}
    }
}
