@extends('index')
@section('index')
    <div class="login">
        <div class="login-wrapper">
            <div class="login-header">
                <img class="logo" src="{!! asset('images/logo/logo.png') !!}" alt="">
                @if (session('locale') == 'km')
                    <div class="language" s-click-link="{{ route('change-locale', 'en') }}">
                        <img src="{!! asset('images/logo/EN.png') !!}" alt="">
                        <span>EN</span>
                    </div>
                @else
                    <div class="language" s-click-link="{{ route('change-locale', 'km') }}">
                        <img src="{!! asset('images/logo/KM.png') !!}" alt="">
                        <span>KH</span>
                    </div>
                @endif
            </div>
            <div class="form-container animate-fade">
                <form class="form" id="login-form" action="{!! route('login-post') !!}" method="post">
                    {{ csrf_field() }}
                    <h2>@lang('login.title')</h2>
                    <p class="form-des">@lang('login.introduce')</p>
                    <div class="form-wrapper">
                        <div class="form-row">
                            <input name="email" placeholder="@lang('login.form.email.placeholder')" type="text"
                                error-message="@lang('login.form.email.error')">
                        </div>
                        <div class="form-row">
                            <input name="password" placeholder="@lang('login.form.password.placeholder')" type="password"
                                error-message="@lang('login.form.password.error')">
                        </div>
                        <div class="option">
                            <div class="remember-me">
                                <input name="remember" id="remember" type="checkbox" value="1"
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                <label for="remember">@lang('login.form.remember')</label>
                            </div>
                            <p class="link-label" s-click-link="{{ route('forgot-page') }}">
                                <span>@lang('login.form.reset')</span>
                            </p>
                        </div>
                        <button color="primary" class="btn-submit-form" type="submit">
                            <span>@lang('login.form.button')</span>
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
                },
                password: {
                    required: true,
                    minLength: 6,
                    maxLength: 20
                }
            };
            $validator("#login-form", validate, {
                border: true,
                message: false
            });
        });
    </script>
@stop
