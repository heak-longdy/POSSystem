@extends('index')
@section('index')
    <div class="login">
        <div class="login-wrapper">
            <div class="login-header">
                <img class="logo" src="{!! asset('images/logo/logo.png') !!}" alt="">
                @if (session('locale') == 'km')
                    <a class="language" href="{{ route('change-locale', 'en') }}">
                        <img src="{!! asset('images/logo/EN.png') !!}" alt="">
                        <span>EN</span>
                    </a>
                @else
                    <a class="language" href="{{ route('change-locale', 'km') }}">
                        <img src="{!! asset('images/logo/KM.png') !!}" alt="">
                        <span>KH</span>
                    </a>
                @endif
            </div>
            <div class="form-container animate-fade">
                <form class="form" id="login-form" action="{!! route('login-post') !!}" method="post">
                    {{ csrf_field() }}
                    <h2>@lang('login.forgot_password')</h2>
                    <p class="form-des">@lang('login.introduce')</p>
                    <div class="form-wrapper">
                        <div class="form-row">
                            <input name="email" placeholder="@lang('login.form.email.placeholder')" type="text"
                                error-message="@lang('login.form.email.error')">
                        </div>
                        <div class="option no-remember">
                            <a class="link-label" href="{{ route('admin-login') }}">
                                <span>@lang('login.form.has_account')</span>
                            </a>
                        </div>
                        <button color="primary" class="btn-submit-form" type="submit">
                            <span>@lang('login.form.send_reset')</span>
                        </button>
                    </div>
                    @if (Session::has('status'))
                        <p class="q-label-error">
                            @lang('login.form.error_login')
                        </p>
                    @endif
                </form>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script>
        $(document).ready(function() {

            let validate = {
                email: {
                    required: true,
                    email: true
                }
            };
            $validator("#login-form", validate, {
                border: true,
                message: false
            });
        });
    </script>
@stop
