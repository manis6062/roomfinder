@extends('adminlte::page')

@section('title', 'Admin : Room')

@section('content_header')
<div class="box box-default" data-widget="box-widget">
  <div class="box-header">
 <h3 class="text-center">Rooms</h3>
    <div class="box-tools">
      <!-- This will cause the box to be removed when clicked -->
      <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
      <!-- This will cause the box to collapse when clicked -->
      <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
    </div>

   <table id="rooms" class="display">
        <thead>
            <tr>
             <th>View</th>
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
                     <a href="{{url('/admin/room-view/' . $room->id)}}"><i class="fa fa-eye"></i></a> 
                      
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
                  @php
                      $value = str_limit($room->description, 30, '...');
                       @endphp
                <td>{{ $value }}</td>
                 <td>{{$room->occupied}}</td>


            </tr>
            @endforeach
            
        </tbody>
    </table>
  </div>
</div>


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
