@extends('admin::index')
@section('index')
    <div class="container">
        <div class="container-wrapper">
            <div class="sidebar">
                @include('admin::shared.sidebar')
            </div>
            <div class="content" x-data={}>
                @yield('layout')
                @include('admin::components.confirm-dialog')
                @include('admin::components.select-option')
            </div>
        </div>
    </div>
@stop
