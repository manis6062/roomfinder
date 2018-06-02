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
 
  $getAllJaggas = Jagga::all();
  if($getAllJaggas){
    return view('admin.jaggalist' , compact('getAllJaggas'));
  }else{
    return false;
  }

}


  public function edit($id){
    
    $jagga = Jagga::find($id);
    return view('admin.jagga-edit', compact('jagga'));

  }

 

  }
