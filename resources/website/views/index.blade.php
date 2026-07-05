<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISEA @yield('admin_title')</title>
    <link rel="shortcut icon" href="{!! asset('images/logo/ISEA_webicon.png') !!}"/>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    {!! HTML::style('admin-public/css/select2.min.css') !!}
    {!! HTML::style('website/css/app.css') !!}
    {!! HTML::style('css/iziToast.css') !!}
    
    {!! HTML::script('js/iziToast.js') !!}
    {!! HTML::script('website/js/app.js') !!}
    {!! HTML::script('admin-public/js/select2.min.js') !!}
    {!! HTML::script('admin-public/js/owl.carousel.min.js') !!}

</head>

<body>
    @yield('index')
    @include('admin::components.iziToast')
    @yield('script')
    {!! HTML::script('website/js/body.js') !!}
</body>

</html>
