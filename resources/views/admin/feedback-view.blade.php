@extends('adminlte::page')

@section('title', 'Admin : View Feedback')

@section('content_header')
<style type="text/css">
    
</style>

 <div class="box box-default" data-widget="box-widget">
  <div class="box-header">
    <h3 class="text-center">View Feedback</h3>
    <hr>
    <div class="box-tools">
      <!-- This will cause the box to be removed when clicked -->
      <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
      <!-- This will cause the box to collapse when clicked -->
      <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
    </div>
    <div class="row">
        <fieldset class="for-panel">
        @if($feedback)
          <div class="row">
            <div class="col-sm-10">
              <div class="form-horizontal">  
                  <p class="text-center">{{$feedback->feedback}}</p>                
              </div>
            </div>
       
          </div>
          @endif
        </fieldset>
    </div>
</div>


  </div>

@stop
