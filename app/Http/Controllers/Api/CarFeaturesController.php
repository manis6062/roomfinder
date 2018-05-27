<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\CarFeature;
use DB;
use App\Library\CarRentFunctions;
class CarFeaturesController extends Controller
{
   
    public function index(){
    	$lang = \App::getLocale();
        $column_name = CarRentFunctions::getColumnName('features','title',$lang);      
    	$fields = "f.$column_name as title";
       
            
        

    	$SQL = "SELECT id, {$fields} from features f where status='active'";
    	//$all = VehicleType::all()->where('status','active');
    	$all = DB::select($SQL);
    	if($all){
    		return \Response::json(array(  'error' => false,  'car_features' => $all ) );	
    	}else{
    		return \Response::json(array(  'error' => true, 'message' =>Lang::get('messages.resultnotfound' ) ) );	
    	}
    	
    }
}
