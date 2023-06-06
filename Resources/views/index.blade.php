@extends('smartcarsnative::layouts.frontend')

@section('title', 'SmartCARSNative')

@section('content')
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {{ config('smartcarsnative.name') }}
    </p>
@endsection
