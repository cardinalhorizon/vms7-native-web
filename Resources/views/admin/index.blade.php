@extends('smartcarsnative::layouts.admin')

@section('title', 'SmartCARSNative')
@section('actions')
    <li>
        <a href="{{ url('/smartcarsnative/admin/create') }}">
            <i class="ti-plus"></i>
            Add New</a>
    </li>
@endsection
@section('content')
    <div class="card border-blue-bottom">
        <div class="header"><h4 class="title">Admin Scaffold!</h4></div>
        <div class="content">
            <p>This view is loaded from module: {{ config('smartcarsnative.name') }}</p>
        </div>
    </div>
@endsection
