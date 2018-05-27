<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Lang;
class SettingController extends Controller
{
    public function index(){
    	try{
    		$setting = Setting::select('rental_fee_percent',
    			'tax_percent',
    			'mileage_limit_per_day',
    			'mileage_limit_per_week',
    			'mileage_limit_per_month',
    			'extra_charge_per_mile'
    			)->firstOrFail();	
    		return \Response::json(array(  'error' => false,  'result' => $setting  ) );	
    	}catch(ModelNotFoundException $e){
    		return \Response::json(array(  'error' => true,  'message' => Lang::get('messages.resultnotfound')  ) );	
    	}
    }

}
