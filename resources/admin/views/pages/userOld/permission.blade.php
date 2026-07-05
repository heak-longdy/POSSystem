@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-user-save-permission', request('id')) !!}" method="POST">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-user-list', 1) !!}"></i>
                    @lang('permission.title') ( {!! $user->username ? $user->username : 'User' !!} )
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row">
                    <div class="permissionLayoutGp">
                        <div class="headerListPermission">
                            <label class="titlePer">Permission<span>*</span></label>
                            <label for="chk-permissionSelectAll" class="permissionListCheckall">
                                <span class="span">Select All</span>
                                <input type="checkbox" id="chk-permissionSelectAll" class="chk-permissionSelectAll" />
                            </label>
                        </div>
                        @if (isset($ModulPermission))
                            @foreach ($ModulPermission as $modulParent)
                                @if (isset($modulParent->modulePermissionParent->parent_name) &&
                                    $modulParent->modulePermissionParent->parent_name)
                                    <label
                                        class="parentLabel">{{ $modulParent->modulePermissionParent->parent_name }}</label>
                                @endif
                                <div class="permissionLayout">
                                    @if (isset($modulParent->modulePermission))
                                        @foreach ($modulParent->modulePermission as $modul)
                                            <div class="permissionItem">
                                                <div class="permissionHeader arrowPermission">
                                                    <i data-feather="chevron-down"></i>
                                                    <div class="textItem">
                                                        {{ $modul->name }}
                                                    </div>
                                                    <label class="form-check-label"
                                                        for="chk-permission-group-{{ $modul->id }}">
                                                        <div class="inputItem">
                                                            <input type="checkbox"
                                                                id="chk-permission-group-{{ $modul->id }}"
                                                                data-permission-id="{{ $modul->id }}"
                                                                class="role_permission permissionAllitem chk-permission-group chk-permission-group-{{ $modul->id }}"
                                                                @foreach ($modul->permission as $val) @if (in_array($val->id, $permission->pluck('permission_id')->toArray())) checked @endif
                                                                @endforeach/>
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="permissionListItemGpCh">
                                                    @if (isset($modul->permission))
                                                        @foreach ($modul->permission as $action)
                                                            <label class="permissionItemCh"
                                                                for="permission{{ $action->name }}">
                                                                <i data-feather="disc"></i>
                                                                <div class="textItem">
                                                                    {{ $action->display_name }}
                                                                </div>
                                                                <div class="inputItem">
                                                                    <input type="checkbox" value="{{ $action->name }}"
                                                                        class="permission-item-{{ $modul->id }} permissionAllitem"
                                                                        id="permission{{ $action->name }}"
                                                                        data-permission-id="{{ $modul->id }}"
                                                                        name="permission[]"
                                                                        @if (isset($permission)) @if (in_array($action->id, $permission->pluck('permission_id')->toArray())) checked @endif
                                                                        @endif />
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        @endif
                        @error('permission')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
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
@section('script')
    <script>
        let arrow = document.querySelectorAll('.arrowPermission');
        for (var i = 0; i < arrow.length; i++) {
            arrow[i].addEventListener('click', (e) => {
                let arrowParent = e.target.parentElement.parentElement;
                arrowParent.classList.toggle('showMenu');
            });
        }

        var data = [];
        $(document).ready(function() {
            $('.chk-permission-group').iCheck({
                checkboxClass: 'icheckbox_square-blue',
            });
            $('.chk-permissionSelectAll').iCheck({
                checkboxClass: 'icheckbox_square-blue',
            });

            //checkAll
            $('.chk-permissionSelectAll').on('ifChecked', function() {
                $('.permissionAllitem').each(function() {
                    $(this).iCheck('check');
                });
            });

            $('.chk-permissionSelectAll').on('ifUnchecked', function() {
                $('.permissionAllitem').each(function() {
                    $(this).iCheck('uncheck');
                });
            });
            //endCheckAll

            //checkByGroup
            $('.chk-permission-group').on('ifChecked', function() {
                $('.permission-item-' + $(this).attr('data-permission-id')).each(function() {
                    $(this).iCheck('check');
                });
            });

            $('.chk-permission-group').on('ifUnchecked', function() {
                $('.permission-item-' + $(this).attr('data-permission-id')).each(function() {
                    $(this).iCheck('uncheck');
                });
            });
            //endCheckBYGroup
        });
    </script>
@stop
