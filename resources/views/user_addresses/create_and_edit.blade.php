@extends('layouts.app')
@section('title', ($address->id ? 'Edit the' : 'Add new' ) . ' address')

@section('content')
    <div class="row">
        <div class="col-md-10 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">
                        {{ $address->id ? 'Edit the' : 'Add new' }} address
                    </h2>
                </div>
                <div class="card-body">
                    <!-- Error alarms -->
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <h4>Error occurs</h4>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li><i class="glyphicon glyphicon-remove"></i> {{ $error }}</li>
                                @endforeach
                            </ul>    
                        </div>
                        
                    @endif
                    
                    <!-- Check if to add new address or update an address -->
                    @if ($address->id)
                    <form class="form-horizontal" role="form" action="{{ route('user_addresses.update', ['user_address' => $address->id]) }}" method="post">
                        {{ method_field('PUT') }}
                    @else
                    <form class="form-horizontal" role="form" action="{{ route('user_addresses.store') }}" method="post">
                    @endif
                        <!-- csrf token -->
                        {{ csrf_field() }}
                        <div class="form-group row">
                            <label class="col-form-label text-md-right col-sm-2">Address</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="address" value="{{ old('address', $address->address) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label text-md-right col-sm-2">Suburb</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="suburb" value="{{ old('suburb', $address->state) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label text-md-right col-sm-2">State</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="state" value="{{ old('state', $address->state) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label text-md-right col-sm-2">Postcode</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="postcode" value="{{ old('postcode', $address->postcode) }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label text-md-right col-sm-2">Contact name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $address->contact_name) }}">
                            </div>
                        </div>
                                                <div class="form-group row">
                            <label class="col-form-label text-md-right col-sm-2">Contact phone</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $address->contact_phone) }}">
                            </div>
                        </div>
                        <div class="from-group row text-center">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">{{ $address->id ? 'Save' : 'Submit' }}</button>
                            </div>
                        </div>
                    </form>
                   
                </div>
            </div>
        </div>
    </div>
@endsection