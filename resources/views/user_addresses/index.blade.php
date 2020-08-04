@extends('layouts.app')
@section('title', 'Address List')

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card panel-default">
                <div class="card-header">
                    Address list
                    <a href="{{ route('user_addresses.create') }}" class="float-right">Add new address</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Contact name</th>
                                <th>Address</th>
                                <th>Postcode</th>
                                <th>Contact phone</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($addresses as $address)
                                <tr>
                                    <td>{{ $address->contact_name }}</td>
                                    <td>{{ $address->full_address }}</td>
                                    <td>{{ $address->postcode }}</td>
                                    <td>{{ $address->contact_phone }}</td>
                                    <td>
                                        <a href="{{ route('user_addresses.edit', ['user_address' => $address->id]) }}" class="btn btn-primary">Edit</a>
                                        <!--                                         
                                        <form action="{{ route('user_addresses.destroy', ['user_address' => $address->id]) }}" method="post" style="display: inline-block;">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <button class="btn btn-danger" type="submit">Delete</button>
                                        </form>
                                         -->
                                        <button class="btn btn-danger btn-del-address" type="button" data-id="{{ $address->id }}">Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @section('scriptsAfterJs')
    <script>
        $(document).ready(function(){
            //When delete button is clicked
            $('.btn-del-address').click(function(){
                //get the value of 'data-id' on delete button
                var id = $(this).data('id');
                //call sweetalert
                swal({
                    title: "Do you want to delete this address?",
                    icon: "warning",
                    buttons: ['cancel', 'yes'],
                    dangerMode:true,
                }).then(function(willDelete){
                    //If user click on yes, willDelete will be true
                    //Otherwise, willDelete will be false
                    if(!willDelete){
                        return;
                    }
                    //Send delete request
                    //If you use "axios" to send ajax request, you do not need to add csrf token manually, axios will do it for you
                    //the related code for csrf token is in "resources/assets/js/bootstrap.js"
                    axios.delete('/user_addresses/' + id).then(function(){
                        //Display "successfully deleted" message
                        swal({
                            title: "Deleted!",
                            icon:"success",
                            text:"Your selected address has been deleted."
                        }).then(function(){
                            //after the request being successfully processed, reload the page
                            location.reload();
                        });
                        
                    });
                });  
            });
        });
    </script>
    @endsection