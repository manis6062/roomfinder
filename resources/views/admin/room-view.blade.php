@extends('adminlte::page')

@section('title', 'Admin : View Room')

@section('content_header')
<style type="text/css">
    
</style>

 <div class="box box-default" data-widget="box-widget">
  <div class="box-header">
    <h3 class="text-center">View Room</h3>
    <hr>
    <div class="box-tools">
      <!-- This will cause the box to be removed when clicked -->
      <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
      <!-- This will cause the box to collapse when clicked -->
      <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
    </div>
    <div class="row">
        <fieldset class="for-panel">
        @if($room)
        @php
        $r = $room;
        @endphp
          <div class="row">
            <div class="col-sm-6">
              <div class="form-horizontal">  
              <label class="col-xs-5 control-label">User:</label>
                  <p class="form-control-static">{{$r->user_id}}</p>                
                  <label class="col-xs-5 control-label">Type:</label>
                  <p class="form-control-static">{{$r->type}}</p>               
                    <label class="col-xs-5 control-label">No of Floor: </label>
                    <p class="form-control-static">{{$r->no_of_floor}}</p>         
                      <label class="col-xs-5 control-label">No of Room: </label>
                    <p class="form-control-static">{{$r->no_of_room}}</p>         
                      <label class="col-xs-5 control-label">Address: </label>
                    <p class="form-control-static">{{$r->address}}</p>     
                     <label class="col-xs-5 control-label">Latitude: </label>
                    <p class="form-control-static">{{$r->loc_lat}}</p>     
                     <label class="col-xs-5 control-label">Longitude: </label>
                    <p class="form-control-static">{{$r->loc_lon}}</p>         
                                          <label class="col-xs-5 control-label">Phone No: </label>
                    <p class="form-control-static">{{$r->phone_no}}</p>         
                       
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-horizontal">               
                  <label class="col-xs-4 control-label">Kitchen: </label>
                 <p class="form-control-static">{{$r->kitchen}}</p>         
                  <label class="col-xs-4 control-label">Parking:</label>
                 <p class="form-control-static">{{$r->parking}}</p>         
                  <label class="col-xs-4 control-label">Restroom:</label>
                 <p class="form-control-static">{{$r->restroom}}</p>         
                  <label class="col-xs-4 control-label">Preference:</label>
                 <p class="form-control-static">{{$r->preference}}</p>         
                  <label class="col-xs-4 control-label">Price:</label>
                 <p class="form-control-static">{{$r->price}}</p>         
                     <label class="col-xs-4 control-label">Description:</label>
                 <p class="form-control-static">{{$r->description}}</p>         
                   <label class="col-xs-4 control-label">Occupied:</label>
                 <p class="form-control-static">{{$r->occupied}}</p>     
                
              </div>
            </div>
          </div>
          @endif
        </fieldset>
    </div>
</div>


<div class="box-header">
    <h3 class="text-center">Images</h3>
    <hr>
    <div class="row">
    <div class="col-sm-12"> 
           @if($room_images)
                 @foreach($room_images as $image)
               <div class="col-sm-4" style="border:1px solid snow; padding-top: 20px;">   
               <img class="img-responsive" src="{{$image}}">  </div> 
                 @endforeach  
                 @endif    
                
    </div>
    </div>
</div>




  </div>


@stop
