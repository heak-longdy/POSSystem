@extends('admin::index')
@section('index')
    <div class="container">
        <div class="container-wrapper">
            <div id="sidebar" class="sidebar" x-cloak>
                <script>
                    if (localStorage.getItem("menu") === "0") {
                        document.getElementById("sidebar").classList.add("hide");
                    }
                </script>
                @include('admin::shared.sidebar')
            </div>
            
            <div class="content" id="content" x-data={} >
                @yield('layout')
                {{-- @include('admin::components.confirm-dialog')  --}}
                @include('admin::components.select-option')
                @include('admin::components.logout')
                <div id="jsScroll" class="scroll">
                    <i class='bx bx-up-arrow-alt' ></i>
                </div>      
            </div>
        </div>
    </div>
@stop