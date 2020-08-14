@extends('layouts.app')
@section('title', 'Error')

@section('content')
<div class="card">
    <div class="card-header">Error</div>
    <div class="card-body text-center">
        <h1>{{ $msg }}</h1>
        <a href="{{ route('root') }}" class="btn btn-primary">Return to home page</a>
    </div>
</div>
@endsection