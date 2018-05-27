<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Lang;
class ModelsController extends Controller
{
    //
    public function index(Request $request){
    	$input = $request->all();
    	$cond = '';
    	$param = array();
    	$SQL = "SELECT id,make_id,title_eng as title FROM car_models";
    	if(isset($input['make_id'])){
    		$cond = " WHERE make_id = ?";
    		$param[] = $input['make_id'];
    	}
        $cond.=" order by title_eng";
		$v = \Validator::make($input, 	[ 
			'make_id' => 'numeric', 				
		] );
		
		$SQL.=$cond; 
    	$res = DB::select($SQL,$param);
    	if($res){

            return \Response::json(array(  'error' => false,  'model_list' => $res ) );
    	}else{
    		return \Response::json(array(  'error' => true, 'message' =>Lang::get('messages.resultnotfound') ) );	
    	}
    	
    }
}
