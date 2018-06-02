@extends('adminlte::page')

@section('title', 'Admin : Dashboard')

@section('content_header')
    <table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>User</th>
                <th>Type</th>
                <th>No of Floor</th>
                <th>No of Room</th>
                <th>Kitchen</th>
                <th>Parking</th>
                <th>Restroom</th>
                <th>Phone No.</th>
                <th>Longitude</th>
                <th>Latitude</th>
                <th>Address</th>
                <th>Preference</th>
                <th>Price</th>
                <th>Description</th>
                <th>Occupied</th>
            </tr>
        </thead>
        <tbody>
        <tr>
	        <td>
	        	sdfsdfsdfsdf
	        </td>

	        <td>
	        	sdfsdfsdfsdf
	        </td>

	        <td>
	        	sdfsdfsdfsdf
	        </td>

	        <td>
	        	sdfsdfsdfsdf
	        </td>

	        <td>
	        	sdfsdfsdfsdf
	        </td>
        </tr>
        	
        </tbody>
    </table>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
              integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
              crossorigin="anonymous"></script>
    <script type="text/javascript">

    	$(document).ready(function() {
    $('#example').DataTable();
} );
    </script>
@stop

@section('content')
    <p>You are logged in!</p>
@stop