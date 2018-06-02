<?php namespace App\Http\Controllers\Admin;

  use Illuminate\Http\Request;
   
  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use Auth;
  Use App\Models\FeedBack;
  class FeedbackController extends Controller
  {



             public function lists(Request $request){
 
  $getAllFeedback = FeedBack::all();
  if($getAllFeedback){
    return view('admin.feedbacklist' , compact('getAllFeedback'));
  }else{
    return false;
  }

}


  public function edit($id){
    
    $jagga = Jagga::find($id);
    return view('admin.feedback-edit', compact('feedback'));

  }

 

  }
