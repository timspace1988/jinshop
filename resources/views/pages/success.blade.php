@extends('layouts.app')
@section('title', 'Operation succeeds')

@section('content')
<div class="card">
    <div class="card-header">Operation succeeds</div>
    <div class="card-body text-center">
        <h1>{{ $msg }}</h1>
        <a href="{{ route('root') }}" class="btn btn-primary">Home page</a>
    </div>
</div>
@endsection
