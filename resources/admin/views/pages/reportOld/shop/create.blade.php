@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-barber-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-barber-list', 1) !!}"></i>
                    {!! request('id') ? "Update Barber" : "Create Barber"!!}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>Name<span>*</span> </label>
                        <input type="text" name="name" value="{!! request('id') ? $data->name : old('name') !!}" placeholder="Name">
                    </div>
                    <div class="form-row">
                        <label>Gender <span>*</span></label>
                        <select name="gender">
                            <option value="F" {!! (request('id') && $data->gender == "F") || old('gender') == "F" ? 'selected' : '' !!}>F</option>
                            <option value="M" {!! (request('id') && $data->gender == "M") || old('gender') == "M" ? 'selected' : '' !!}>M</option>
                        </select>
                        @error('gender')
                            <span class="error">@lang("message.".$message)</span>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>Phone<span>*</span> </label>
                        <input type="text" name="phone" value="{!! request('id') ? $data->phone : old('phone') !!}" placeholder="Phone">
                    </div>
                    <div class="form-row">
                        <label>DOB</label>
                        <input type="text" name="dob" value="{!! request('id') ? $data->dob : old('dob') !!}" placeholder="DOB" id="dob">
                        @error('dob')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>Address</label>
                        <textarea placeholder="Address" name="address" row="3">{!! request('id') ? $data->address : old('address') !!}</textarea>
                        @error('address')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                
                @if (!request('id'))
                    <div class="row-2">
                        <div class="form-row">
                            <label>Password<span>*</span> </label>
                            <input type="password" name="password" placeholder="Password" autocomplete="new-password">
                        </div>
                        <div class="form-row">
                            <label>Confirm Password<span>*</span> </label>
                            <input type="password" name="confirm_password" placeholder="Confirm Password">
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="form-row">
                        <label>Profile</label>
                        <div class="form-select-photo image">
                            <div class="select-photo {!! request('id') && isset($data) && $data->image != null ? 'active' : '' !!}">
                                <div class="icon">
                                    <i data-feather="image"></i>
                                </div>
                                <div class="title">
                                    <span>Profile</span>
                                </div>
                            </div>
                            <div class="image-view {!! request('id') && isset($data) && $data->image != null ? 'active' : '' !!}" >
                                <img src="{!! request('id') && isset($data) && $data->image != null ? asset('file_manager' . $data->image) : null !!}"  onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                            <input type="text" name="image" s-click-fn="selectImage(event)" autocomplete="off"
                                role="presentation">
                            <input type="hidden" name="tmp_file" value="{!! request('id') && isset($data) && $data->image != null ? $data->image : '' !!}">
                        </div>
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>@lang('user.form.button.submit')</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-barber-list', 1) !!}">
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
                gender: {
                    required: true,
                    nick_name: true,
                },
                phone: {
                    required: true,
                    phone: true,
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

            $("#dob").datepicker({
                changeYear: true,
                gotoCurrent: true,
                yearRange: "-1:+1",
                dateFormat: "yy-mm-dd",
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
