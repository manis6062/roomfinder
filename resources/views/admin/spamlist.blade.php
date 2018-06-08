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
    @if(\Session::has('success'))
                            <div class="alert alert-success">
                                <i class="fa fa-times pull-right closeMessage"></i>
                                <p class="text-center">{{\Session::get('success')}}</p>
                            </div>
                        @endif

     <table id="spam" class="display">
        <thead>
            <tr>
             <th>S. No.</th>
                <th>Complains</th>
                <th>Read</th>
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
               @php
                      $value = str_limit($spam->complains, 500, '...');
                       @endphp
                <td>{{ $value }}</td>
            <td>  @if($spam->read == 1)
                  <i class="fa fa-lg fa-check-circle-o"></i>
                 @endif</td>
                 <td><div class="btn-group">
                     <a href="{{url('/admin/spam-edit/' . $spam->id)}}"><i class="fa fa-edit" style="padding-right: 10px;"></i></a> 
                       <a href="javascript:void(0);"><i sid="{{$spam->id}}"  class="fa fa-trash-o delete_spam" style="padding-right: 10px;"></i></a> 
                      
                    </div>
                    </td>
            </tr>
            @endforeach
            
        </tbody>
    </table>
  </div>
      <meta name="csrf-token" content="{{ csrf_token() }}">
</div>

{{-- href="{{url('/admin/spam-delete/' . $spam->id)}}"
 --}}  
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
              integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
              crossorigin="anonymous"></script>
    <script type="text/javascript">


$(document).ready(function() {
    $('#spam').DataTable();

    $('.closeMessage').on('click', function (e) {
            $('.alert').hide();
        })



$('.delete_spam').click(function(){


  if( confirm('Are you sure?') )
  {
    var id = $(this).attr('sid');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
});
     $.ajax(
    {
        url: '/admin/spam-delete/' + id,       
        type: 'DELETE', 
        dataType: "JSON",
        data: {
            "id": id // method and token not needed in data
        },
        success: function (response)
        {
          if(response == 1){
            location.reload();// see the reponse sent
          }else{
             console.log(response);
          }
        },
        error: function(xhr) {
         console.log(xhr.responseText); // this line will save you tons of hours while debugging
        // do something here because of error
       }
    });
    // Make an ajax call to delete the record and pass the id to identify the record
  }
});





} );
    </script>
@stop
