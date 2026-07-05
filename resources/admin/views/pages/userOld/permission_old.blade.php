@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-user-save-permission', request('id')) !!}" method="POST">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-user-list', 1) !!}"></i>
                    @lang('permission.title') ( {!! $user->name !!} )
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="permission-list">
                    @foreach ($modules as $module)
                        <div class="permission-item">
                            <div class="title">
                                <span>@lang("permission.module.".$module->name."")</span>
                            </div>
                            <div class="option-list">
                                @foreach ($module->permissions as $permission)
                                    <div class="option-item">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="{{ $permission->name }}" {!! $user->hasPermissionTo($permission->id) ? 'checked' : '' !!} name="permission[]"
                                                value="{{ $permission->name }}" />
                                            <label class="form-check-label"
                                                for="{{ $permission->name }}">@lang("permission.permission.".$permission->name."")</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>@lang('permission.button.update')</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-user-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>@lang('permission.button.cancel')</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop
