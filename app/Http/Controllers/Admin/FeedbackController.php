<?php namespace App\Http\Controllers\Admin;

  use Illuminate\Http\Request;
   
  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use Auth;
  Use App\Models\FeedBack;
  class FeedbackController extends Controller
  {



             public function lists(Request $request){
 
  $getAllFeedback = FeedBack::orderBy('id' , 'desc')->get();;
  if($getAllFeedback){
    return view('admin.feedbacklist' , compact('getAllFeedback'));
  }else{
    return false;
  }

}


  public function view($id){
    $feedback = FeedBack::find($id);
    return view('admin.feedback-view', compact('feedback'));
  }

 

  }
