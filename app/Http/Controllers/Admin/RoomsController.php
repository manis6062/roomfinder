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
 
  $getAllRooms = Room::orderBy('id' , 'desc')->get();
  if($getAllRooms){
    return view('admin.roomlist' , compact('getAllRooms'));
  }else{
    return false;
  }

}


  public function view($id){

    
    $room = Room::find($id);

    $images = Images::where('room_id' , $room->id)->get();



     $image_path = url('/images/rooms/thumb');
     $room_images = array();
       foreach ($images as $key => $value) {
         $room_images[] = $image_path . '/' . $value->image;
       }

    return view('admin.room-view', compact('room' , 'room_images'));

  }




  }
 