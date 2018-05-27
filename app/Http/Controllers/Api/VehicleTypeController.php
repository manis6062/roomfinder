<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use App\Library\CarRentFunctions;

use DB;
class VehicleTypeController extends Controller
{
    //
    public function index(){
    	$lang = \App::getLocale();
    	$fields = "vt.icon";
        $title_name = CarRentFunctions::getColumnName('vehicle_types', 'title', $lang);
        $fields.= ",vt.$title_name as title";
    	$SQL = "SELECT id, {$fields} from vehicle_types vt where status='active' order by title";
    	$vehicle_type_img = env("BASE_URL")."images/vehicle_type/thumb/";
    	$types = DB::select($SQL);
        foreach($types as $type){
           $type->icon =  $vehicle_type_img.$type->icon;
        }
    	return \Response::json(array(  'error' => false,  'vehicle_types' => $types ) );
    }
}
