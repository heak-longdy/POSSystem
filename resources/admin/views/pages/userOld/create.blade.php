@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-user-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-user-list', 1) !!}"></i>
                    {!! request('id') ? __('user.form.title.update', ['name' => __('user.name')]) : __('user.form.title.create', ['name' => __('user.name')]) !!}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>@lang('user.form.name.label')<span>*</span> </label>
                        <input type="text" name="name" value="{!! request('id') ? $data->username : old('username') !!}"
                            placeholder="@lang('user.form.name.placeholder')">
                    </div>
                    <div class="form-row">
                        <label>@lang('user.form.phone.label')</label>
                        <input name="phone" placeholder="@lang('user.form.phone.placeholder')" type="text"
                            value="{!! request('id') ? $data->phone : old('phone') !!}">
                        @error('phone')
                            <span class="error">@lang("message.".$message)</span>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>@lang('user.form.email.label')<span>*</span> </label>
                        <input type="text" name="email" value="{!! request('id') ? $data->email : old('email') !!}" data-old="{!! request('id') ? $data->email : old('email') !!}"
                            placeholder="@lang('user.form.email.placeholder')" autocomplete="off">
                        @error('email')
                            <span class="error">@lang("message.".$message)</span>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>@lang('user.form.status.label')<span>*</span></label>
                        <select name="status">
                            <option value="1" {!! (request('id') && $data->status == 1) || old('status') == 1 ? 'selected' : '' !!}>@lang('user.form.status.active')</option>
                            <option value="2" {!! (request('id') && $data->status == 2) || old('status') == 2 ? 'selected' : '' !!}>@lang('user.form.status.disable')</option>
                        </select>
                    </div>
                </div>
                {{-- <div class="row-2">
                    <div class="form-row">
                        <label>@lang('user.form.role.label')<span>*</span></label>
                        <select name="role">
                            @foreach (config('dummy.user.role') as $key => $role)
                                @if ($key != 'super_admin')
                                    <option value="{{ $key }}" @if ($data && $key == $data->role) selected @endif>
                                        {{ $role }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div> --}}
                @if (!request('id'))
                    <div class="row-2">
                        <div class="form-row">
                            <label>@lang('user.form.password.label')<span>*</span> </label>
                            <input type="password" name="password" placeholder="@lang('user.form.password.placeholder')" autocomplete="new-password">
                        </div>
                        <div class="form-row">
                            <label>@lang('user.form.password_confirmation.label')<span>*</span> </label>
                            <input type="password" name="confirm_password"
                                placeholder="@lang('user.form.password_confirmation.placeholder')">
                        </div>
                    </div>
                @endif
                <div class="row-2">
                    <div class="form-row">
                        <label>@lang('user.form.profile.label')</label>
                        <div class="form-select-photo image">
                            <div class="select-photo {!! request('id') && isset($data) && $data->profile != null ? 'active' : '' !!}">
                                <div class="icon">
                                    <i data-feather="image"></i>
                                </div>
                                <div class="title">
                                    <span>@lang('user.form.profile.placeholder')</span>
                                </div>
                            </div>
                            <div class="image-view {!! request('id') && isset($data) && $data->profile != null ? 'active' : '' !!}" >
                                <img src="{!! request('id') && isset($data) && $data->profile != null ? asset('file_manager' . $data->profile) : null !!}"  onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                            <input type="text" name="image" s-click-fn="selectImage(event)" autocomplete="off"
                                role="presentation">
                            <input type="hidden" name="tmp_file" value="{!! request('id') && isset($data) && $data->profile != null ? $data->profile : '' !!}">
                        </div>
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>@lang('user.form.button.submit')</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-user-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>@lang('user.form.button.cancel')</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
    @include('admin::file-manager.popup')
@stop

@section('script')
    <script lang="ts">
        $(document).ready(function() {
            $validator("#form", {
                name: {
                    required: true,
                },
                email: {
                    required: true,
                    email: true,
                },
                @if (!request('id'))
                    password: {
                        required: true,
                    },
                    confirm_password: {
                        required: true,
                        match: "password"
                    },
                @endif
                status: {
                    required: true,
                },
            });
        });

        function selectImage(e) {
            fileManager({
                multiple: false,
                afterClose: (data, basePath) => {
                    if (data?.length > 0) {
                        const parent = e.target.closest('.form-select-photo');
                        e.target.value = data[0].path;
                        parent
                            .querySelector('.select-photo')
                            .classList.add('active');
                        parent
                            .querySelector('.image-view')
                            .classList
                            .add('active');
                        parent
                            .querySelector('.image-view')
                            .childNodes[0]
                            .nextElementSibling
                            .setAttribute('src', basePath + data[0].path);
                    }
                }
            })
        }
    </script>
@stop
