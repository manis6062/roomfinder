@extends('adminlte::page')

@section('title', 'Admin : Spam')

@section('content_header')

<div class="box box-default" data-widget="box-widget">
  <div class="box-header">
 <h3 class="text-center">Spam</h3>
    <div class="box-tools">
      <!-- This will cause the box to be removed when clicked -->
      <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
      <!-- This will cause the box to collapse when clicked -->
      <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
    </div>

     <table id="spam" class="display">
        <thead>
            <tr>
             <th>S. No.</th>
                <th>Complains</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @php
        $count =1;
        @endphp
        @foreach($getAllSpam as $spam)
              <tr>
              <td>{{$count ++}}</td>
             <td>{{$spam->complains}}</td>
                 <td><div class="btn-group">
                     <a href="{{url('/admin/spam-edit/' . $spam->id)}}"><i class="fa fa-edit" style="padding-right: 10px;"></i></a> 
                       <a href="{{url('/admin/spam-delete/' . $spam->id)}}"><i class="fa fa-trash-o" style="padding-right: 10px;"></i></a> 
                      
                    </div></td>
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
    $('#spam').DataTable( {
      
    });
});
    </script>
@stop
