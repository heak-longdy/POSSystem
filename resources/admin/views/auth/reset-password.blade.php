<form class="form" id="login-form" action="{!! route('login-post') !!}" method="post">
    {{ csrf_field() }}
    <h2>@lang('login.reset_password')</h2>
    <div class="form-wrapper">
        <div class="form-row">
            <input name="email" placeholder="@lang('login.form.email.placeholder')" type="text"
                error-message="@lang('login.form.email.error')">
        </div>
        <p class="link-label">
            <span>@lang('login.form.has_account')</span>
        </p>
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