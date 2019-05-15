@extends('layouts.app')

@section('content')
    <div class="container narrow-container">
        @if (! empty(session('success_message')))
            <div class="mb-3 alert alert-success">
                {{ session('success_message') }}
            </div>
        @endif
        @if (! empty(session('error_message')))
            <div class="mb-3 alert alert-danger">
                {{ session('error_message') }}
            </div>
        @endif
        @yield('main')
    </div>
@endsection
