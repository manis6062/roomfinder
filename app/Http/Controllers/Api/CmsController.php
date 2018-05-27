<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Cms;
use Lang;
use App\Library\CarRentFunctions; 
class CmsController extends Controller
{
	public function getPage(Request $request){
		$data = $request->all();			
		$v = \Validator::make($data,[				
				'slug' => 'required|exists:cms,slug',
			]
		);
		if ($v->fails())
		{	
			$msg = array();
			$messages = $v->errors();			
			foreach ($messages->all() as $message) {
				return \Response::json(array(  'error' => true,  'message' => $message ) );
			}
		}
		$lang = \App::getLocale();
		$column_name = CarRentFunctions::getColumnName('cms','content',$lang);

		$page_details = Cms::select($column_name)->where('slug',$data['slug'])->first();
		
		$result['content'] = $page_details->$column_name;

		/*if($lang == 'th'){
			$result['content'] = $page_details->content_thai;
		}*/

		$result['a_content'] = trim($result['content']);
		$result['a_content'] = preg_replace("/\r\n|\r|\n/",'',$result['a_content']);
		$result['a_content'] = preg_replace("/&nbsp;/",'',$result['a_content']);

		$result['content'] = trim(strip_tags($result['content']));
		$result['content'] = preg_replace("/&nbsp;/",'',$result['content']);
		$result['content'] = preg_replace("/\r\n|\r|\n/",'',$result['content']);
		//$result['a_content'] = trim(preg_replace('/\s\s+/', ' ', $result['a_content']));

		return \Response::json(array(  'error' => false,  'result' => $result  ) );				
	}

	
	
}
