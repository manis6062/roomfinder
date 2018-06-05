@extends('adminlte::page')

@section('title', 'Admin : View Jagga')

@section('content_header')
<style type="text/css">
    
</style>

 <div class="box box-default" data-widget="box-widget">
  <div class="box-header">
    <h3 class="text-center">View Jagga</h3>
    <hr>
    <div class="box-tools">
      <!-- This will cause the box to be removed when clicked -->
      <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
      <!-- This will cause the box to collapse when clicked -->
      <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
    </div>
    <div class="row">
        <fieldset class="for-panel">
        @if($jagga)
        @php
        $j = $jagga;
        @endphp
          <div class="row">
            <div class="col-sm-6">
              <div class="form-horizontal">  
              <label class="col-xs-5 control-label">User:</label>
                  <p class="form-control-static">{{$j->user_id}}</p>                
                  <label class="col-xs-5 control-label">Type:</label>
                  <p class="form-control-static">{{$j->type}}</p>               
                      <label class="col-xs-5 control-label">Address: </label>
                    <p class="form-control-static">{{$j->address}}</p>     
                     <label class="col-xs-5 control-label">Latitude: </label>
                    <p class="form-control-static">{{$j->loc_lat}}</p>     
                     <label class="col-xs-5 control-label">Longitude: </label>
                    <p class="form-control-static">{{$j->loc_lon}}</p>         
                                          <label class="col-xs-5 control-label">Phone No: </label>
                    <p class="form-control-static">{{$j->phone_no}}</p>         
                       
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-horizontal">               
                  <label class="col-xs-4 control-label">Price:</label>
                 <p class="form-control-static">{{$j->price}}</p>         
                     <label class="col-xs-4 control-label">Description:</label>
                 <p class="form-control-static">{{$j->description}}</p>         
                   <label class="col-xs-4 control-label">Sold:</label>
                 <p class="form-control-static">{{$j->sold}}</p>     
                
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
           @if($jagga_images)
                 @foreach($jagga_images as $image)
               <div class="col-sm-4" style="border:1px solid snow; padding-top: 10px;">   
               <img class="img-responsive" src="{{$image}}">  </div> 
                 @endforeach  
                 @endif    
                
    </div>
    </div>
</div>




  </div>


@stop
