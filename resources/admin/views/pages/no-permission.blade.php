@extends('admin::shared.layout')
@section('layout')
    <div class="no-permission-container">
        <div class="no-permission-wrapper">
            <div class="image">
                <img src="{!! asset('images/logo/403.gif') !!}" alt="">
            </div>
            <div class="message">
                {{-- <h1>403</h1> --}}
                <span class="title">@lang('permission.no_permission.title')</span>
                <span class="des">@lang('permission.no_permission.description')</span>
                <button s-click-fn="$onConfirmMessage(
                                        '{!! route('admin-sign-out') !!}',
                                        '@lang('dialog.msg.sign_out')',
                                        {
                                            confirm: '@lang('dialog.button.sign_out')',
                                            cancel: '@lang('dialog.button.cancel')'
                                        }
                                    );">
                    <span>@lang('setting.sign_out')</span>
                </button>
            </div>
        </div>
    </div>
@stop
