<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\CarEstimatedValues;
use DB;
class EstimatedValuesController extends Controller
{
   

	public function index(){
    	$lang = \App::getLocale();    	
    	$all = CarEstimatedValues::all()->where('status','active');  
        if($all){
            return \Response::json(array(  'error' => false,  'result' => $all ) );
        }else{
            return \Response::json(array(  'error' => true,  'message' => Lang::get('messages.resultnotfound') ) );
        }  	
    	
    }

}
