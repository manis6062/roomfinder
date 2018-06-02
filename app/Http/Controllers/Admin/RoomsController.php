<?php namespace App\Http\Controllers\Admin;

  use Illuminate\Http\Request;

  use App\Http\Requests;
  use App\Http\Controllers\Controller;
  use App\Library\RoomFinderFunctions;
  use App\Models\Room;
  use App\Models\User;
  use App\Models\Images;
  use Lang,DB,Auth;
  use Illuminate\Support\Facades\Input;
  class RoomsController extends Controller
  {


             public function lists(Request $request){
 
  $getAllRooms = Room::all();
  if($getAllRooms){
    return view('admin.roomlist' , compact('getAllRooms'));
  }else{
    return false;
  }

}


  public function edit($id){
    
    $room = Room::find($id);
    return view('admin.room-edit', compact('room'));

  }




  }
 