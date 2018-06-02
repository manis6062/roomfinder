<?php namespace App\Http\Controllers\Admin;

  use Illuminate\Http\Request;
   
  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use Auth;
  Use App\Models\Spam;
  class SpamController extends Controller
  {



  public function lists(Request $request){
 
  $getAllSpam = Spam::all();
  if($getAllSpam){
    return view('admin.spamlist' , compact('getAllSpam'));
  }else{
    return false;
  }

}


  public function edit($id){
    
    $jagga = Jagga::find($id);
    return view('admin.spam-edit', compact('spam'));

  }

 

  }
