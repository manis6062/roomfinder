<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Message;
use App\Models\Notification;
use DB,Lang;
use App\Library\CarRentFunctions;
class BanksController extends Controller
{
	public function index(Request $request){
		$lang = $request->header('lang');
        $column_name = CarRentFunctions::getColumnName('banks','bank_name',$lang); 		
        $banks = Bank::select('id','bank_brand',$column_name)->get();
        $bank_array = $b = array();
        foreach($banks as $bank){
            $b['id'] = $bank->id; 
            $b['bank_brand'] = $bank->bank_brand;
            
            /*if($lang == 'th'){     

                $b['name'] = $bank->bank_name_thai;
            }else{
                $b['name'] = $bank->bank_name_eng;
            }*/
            $b['name'] = $bank->$column_name; 
            array_push($bank_array,$b);
        }
        return \Response::json(array(  'error' => false,  'result' => $bank_array ) );
    }
   
    
}
