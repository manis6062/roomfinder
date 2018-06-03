@extends('adminlte::page')

@section('title', 'Admin : Feedback')

@section('content_header')
<div class="box box-default" data-widget="box-widget">
  <div class="box-header">
 <h3 class="text-center">Feedback</h3>
     <div class="box-tools">
      <!-- This will cause the box to be removed when clicked -->
      <button class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
      <!-- This will cause the box to collapse when clicked -->
      <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
    </div>
     <table id="rooms" class="display">
        <thead>
            <tr>
             <th>S.No</th>
                <th>Feedback</th>
                 <th>View</th>
            </tr>
        </thead>
        <tbody>
        @php 
        $count = 1; 
        @endphp
        @foreach($getAllFeedback as $feedback)
              <tr>
             <td>{{$count++}}</td>
                <td>{{$feedback->feedback}}</td>
                 <td><div class="btn-group">
                     <a href="{{url('/admin/feedback-view/' . $feedback->id)}}"><i class="fa fa-eye"></i></a> 
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
    $('#rooms').DataTable( {
      
    });
});
    </script>
@stop
