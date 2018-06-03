<?php namespace App\Http\Controllers\Admin;

  use Illuminate\Http\Request;
   
  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use Auth;
  Use App\Models\Spam;
  class SpamController extends Controller
  {



  public function lists(Request $request){
 
  $getAllSpam = Spam::orderBy('id' , 'desc')->get();
  if($getAllSpam){
    return view('admin.spamlist' , compact('getAllSpam'));
  }else{
    return false;
  }

}


  public function edit($id){
    
    $spam = Spam::find($id);
    return view('admin.spam-edit', compact('spam'));

  }


  public function update($id, Request $request)
{

    $spam = Spam::findOrFail($id);
    $spam->update($request->all());
    return redirect('/admin/spam');
}


  public function destroy($id, Request $request)
{

    $spam = Spam::findOrFail($id);
    $spam->delete($id);
    return true;
}

 

  }
