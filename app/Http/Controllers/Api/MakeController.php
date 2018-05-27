<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\MakeModel;
use DB;
class MakeController extends Controller
{
    //
    protected $table = 'vehicle_types';
    /*public function index(){
    	$all = MakeModel::all()->where('status','active');
    	return \Response::json(array(  'error' => false,  'make_list' => $all ) );
    }*/

	public function index(){
    	$lang = \App::getLocale();
    	$fields = "";        
        $fields.="cm.title_eng as title";
    	$SQL = "SELECT id, {$fields} from car_make cm where status='active' order by title_eng";
    	//$all = VehicleType::all()->where('status','active');
    	$all = DB::select($SQL);
    	return \Response::json(array(  'error' => false,  'car_make' => $all ) );
    }

}
