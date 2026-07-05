@extends('admin::index')
@section('index')
    <div class="login">
        <div class="login-wrapper">
            <div class="login-header">
                <img class="logo" src="{!! asset('images/logo/logo.png') !!}" alt="">
            </div>
            <div class="form-container animate-fade">
                <form class="form" id="login-form" action="{!! route('admin-login-post') !!}" method="post">
                    @csrf
                    <input type="hidden" name="returnUrl" value={{ request()->returnUrl }}>
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
                                <input name="remember" id="remember" type="checkbox" value="true">
                                <label for="remember">@lang('login.form.remember')</label>
                            </div>
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
                inputClass: "error-input",
                messageClass: "error",
                showMessage: false
            });
        });
    </script>
@stop
