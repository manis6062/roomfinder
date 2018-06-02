@extends('adminlte::page')

@section('title', 'Admin : Jagga')

@section('content_header')
   <table id="jagga" class="display">
        <thead>
            <tr>
            <th>Action</th>
                <th>Owner</th>
                <th>Type</th>
                <th>Phone No.</th>
                <th>Longitude</th>
                <th>Latitude</th>
                <th>Address</th>
                <th>Price</th>
                <th>Description</th>
                <th>Sold</th>
            </tr>
        </thead>
        <tbody>
        @foreach($getAllJaggas as $jagga)
            <tr>
            <td><div class="btn-group">
                     <a href="{{url('/admin/jagga-edit/' . $jagga->id)}}"><i class="fa fa-edit" style="padding-right: 10px;"></i></a> 
                       <a href="{{url('/jagga/delete/' . $jagga->id)}}"><i class="fa fa-trash-o" style="padding-right: 10px;"></i></a> 
                <td>{{$jagga->user_id}}</td>
                <td>{{$jagga->type}}</td>
                <td>{{$jagga->phone_no}}</td>
                <td>{{$jagga->loc_lon}}</td>
                <td>{{$jagga->loc_lat}}</td>
                <td>{{$jagga->address}}</td>
                <td>{{$jagga->price}}</td>
                <td>{{$jagga->description}}</td>
                 <td>{{$jagga->sold}}</td>
            </tr>
            @endforeach
            
        </tbody>
    </table>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
              integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
              crossorigin="anonymous"></script>
    <script type="text/javascript">

$(document).ready(function() {
    $('#jagga').DataTable( {
    } );
} );
    </script>
@stop