<?php namespace App\Http\Controllers\Admin;

  use Illuminate\Http\Request;
   
  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use Auth;
  use App\Models\Spam;
  use Session; 
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
    $data['read'] = '1';
    $spam->update($data);
    return view('admin.spam-edit', compact('spam'));
  }


  public function update($id, Request $request)
{

    $spam = Spam::findOrFail($id);
    $spam->update($request->all());
    return redirect('/admin/spam')->with('success', 'Spam has been updated successfully.  !');
    // return redirect('/admin/spam');
}


  public function destroy($id, Request $request)
{

    $spam = Spam::findOrFail($id);
    if($spam->delete($id)){
      Session(['success' => 'Spam has been deleted successfully.']);
      return 1;
    }
    return 0;
}

 

  }
