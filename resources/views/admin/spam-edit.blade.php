@extends('adminlte::page')

@section('title', 'Admin : Edit Spam')

@section('content_header')
<style type="text/css">
    
</style>

 <div class="box box-default" data-widget="box-widget">
  <div class="box-header">
    <h3 class="text-center">Edit Spam</h3>
    <hr>
{!! Form::model($spam, ['route' => ['spam.update', $spam->id] , 'method' => 'PUT']) !!}
<div class="col-sm-6">
  <div class="form-group">
    <label for="exampleFormControlSelect1">User Id</label>
  <select class="form-control" id="exampleFormControlSelect1" disabled="disabled" name="user_id">
      <option value="{{$spam->id}}">{{$spam->id}}</option>
    </select>
  </div>
  <div class="form-group">
    <label for="exampleFormControlTextarea1">Complains</label>

    <textarea class="form-control" id="exampleFormControlTextarea1" rows="8" name="complains">
      {{ $spam->complains }}
    </textarea>
  </div>
  <button type="submit" class="btn btn-info waves-effect waves-light">Submit
                                </button>
{!! Form::close() !!}
</div>
    <div class="box-tools">
      <!-- This will cause the box to be removed when clicked -->
      <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
      <!-- This will cause the box to collapse when clicked -->
      <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
    </div>
</div>


  </div>

@stop
