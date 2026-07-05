@extends('website::index')
@section('index')
    <div class="wb-container">
        {{-- <div class="headerBg"><div class="Bg"></div></div> --}}
        <div class="bodyBg">
            <div class="Bg"></div>
        </div>

        <div class="container-wrapper">
            <div class="content" id="content" x-data={}>
                @include('website::shared.header', ['header_name' => ''])
                @yield('layout')
                @include('website::shared.footer')
            </div>
        </div>
    </div>
@stop
