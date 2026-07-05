@extends('admin::index')
@section('index')
    <div class="login">
        <div class="login-wrapper">
            <div class="form-container">
                @yield('auth')
            </div>
        </div>
        <svg class="login-footer" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill-opacity="1"
                d="M0,96L48,128C96,160,192,224,288,218.7C384,213,480,139,576,122.7C672,107,768,149,864,138.7C960,128,1056,64,1152,42.7C1248,21,1344,43,1392,53.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
            </path>
        </svg>
    </div>
    {{-- <span class="copy-right">Production overwirte by : <a href="https://www.facebook.com/longdy.potter" target="_blank" rel="noopener noreferrer">Longdy Heak</a></span> --}}
@stop
