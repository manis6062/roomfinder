<?php namespace App\Http\Controllers\Admin;

  use Illuminate\Http\Request;

  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use App\Library\RoomFinderFunctions;
  use App\Models\Jagga;
  use App\Models\User;
  use App\Models\Images;
  use Lang,DB,Auth;
  class JaggasController extends Controller
  {



             public function lists(Request $request){
 
  $getAllJaggas = Jagga::orderBy('id' , 'desc')->get();
  if($getAllJaggas){
    return view('admin.jaggalist' , compact('getAllJaggas'));
  }else{
    return false;
  }

}


  public function view($id){
    
   $jagga = Jagga::find($id);

   if($jagga){
      $images = Images::where('jagga_id' , $jagga->id)->get();
     $image_path = url('/images/jaggas/thumb');
     $jagga_images = array();
       foreach ($images as $key => $value) {
         $jagga_images[] = $image_path . '/' . $value->image;
       }
     }else{
      return false;
     }

  

       return view('admin.jagga-view', compact('jagga' , 'jagga_images'));

  }

 

  }
