@extends('adminlte::page')

@section('title', 'Admin : Room')

@section('content_header')
   <table id="rooms" class="display">
        <thead>
            <tr>
             <th>Action</th>
                <th>User</th>
                <th>Type</th>
                <th>No of Floor</th>
                <th>No of Room</th>
                <th>Kitchen</th>
                <th>Parking</th>
                <th>Restroom</th>
                <th>Phone No.</th>
                <th>Longitude</th>
                <th>Latitude</th>
                <th>Address</th>
                <th>Preference</th>
                <th>Price</th>
                <th>Description</th>
                <th>Occupied</th>
            </tr>
        </thead>
        <tbody>
        @foreach($getAllRooms as $room)
            <tr>

{{--                 <td>{{\App\Models\User::where('id' , 1)->first()->email}}</td>
 --}}           
  <td><div class="btn-group">
                     <a href="{{url('/admin/room-edit/' . $room->id)}}"><i class="fa fa-edit" style="padding-right: 10px;"></i></a> 
                       <a href="{{url('/room/delete/' . $room->id)}}"><i class="fa fa-trash-o" style="padding-right: 10px;"></i></a> 
                      
                    </div></td>
  <td>{{$room->user_id}}</td>
                <td>{{$room->type}}</td>
                <td>{{$room->no_of_floor}}</td>
                <td>{{$room->no_of_room}}</td>
                <td>{{$room->kitchen}}</td>
                <td>{{$room->parking}}</td>
                <td>{{$room->restroom}}</td>
                <td>{{$room->phone_no}}</td>
                <td>{{$room->loc_lon}}</td>
                <td>{{$room->loc_lat}}</td>
                <td>{{$room->address}}</td>
                <td>{{$room->preference}}</td>
                <td>{{$room->price}}</td>
                <td>{{$room->description}}</td>
                 <td>{{$room->occupied}}</td>


            </tr>
            @endforeach
            
        </tbody>
    </table>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
              integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
              crossorigin="anonymous"></script>
    <script type="text/javascript">

$(document).ready(function() {
    $('#rooms').DataTable( {
        "scrollX": true
    } );
} );
    </script>
@stop
