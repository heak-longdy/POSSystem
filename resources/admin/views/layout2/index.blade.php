<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN</title>
    {!! HTML::style('admin-public/css/app.css') !!}
    {!! HTML::style('admin-public/css/materialIcon.css') !!}
    {!! HTML::style('admin-public/css/select2.min.css') !!}
    {!! HTML::style('admin-public/css/Material_Symbols.css') !!}
    {!! HTML::style('plugin/toastr.min.css') !!}
    {!! HTML::style('admin-public/css/multiple-select.css') !!}

    

</head>

<body>
    @yield('index')

    <div class="container">
        <div class="container-wrapper">
            <div class="sidebar" id="sidebar" class="sidebar">
                @include('admin::shared.sidebar')
            </div>
            <div class="content" id="content">
                @yield('layout')
            </div>
        </div>
    </div>



    {!! HTML::script('admin-public/js/sliderBar.js') !!}
    @yield('script')
</body>

</html>
