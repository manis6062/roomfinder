@extends('adminlte::page')

@section('title', 'Admin : Jagga')

@section('content_header')

<div class="box box-default" data-widget="box-widget">
  <div class="box-header">
 <h3 class="text-center">Jaggas</h3>
    <div class="box-tools">
      <!-- This will cause the box to be removed when clicked -->
      <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
      <!-- This will cause the box to collapse when clicked -->
      <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
    </div>
       <table id="jagga" class="display">
        <thead>
            <tr>
            <th>View</th>
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
                     <a href="{{url('/admin/jagga-view/' . $jagga->id)}}"><i class="fa fa-eye"></i></a> 
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
  </div>
</div>


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