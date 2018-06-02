@extends('adminlte::page')

@section('title', 'Admin : Spam')

@section('content_header')
   <table id="rooms" class="display">
        <thead>
            <tr>
             <th>User Id</th>
                <th>Room Id</th>
                <th>Jagga Id</th>
            </tr>
        </thead>
        <tbody>
        @foreach($getAllSpam as $spam)
              <tr>
             <td>{{$feedback->user_id}}</td>
                <td>{{$feedback->room_id}}</td>
                 <td>{{$feedback->jagga_id}}</td>
                 <td><div class="btn-group">
                     <a href="{{url('/admin/feedback-edit/' . $feedback->id)}}"><i class="fa fa-edit" style="padding-right: 10px;"></i></a> 
                       <a href="{{url('/admin/feedback-delete/' . $feedback->id)}}"><i class="fa fa-trash-o" style="padding-right: 10px;"></i></a> 
                      
                    </div></td>
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
      
    });
});
    </script>
@stop
