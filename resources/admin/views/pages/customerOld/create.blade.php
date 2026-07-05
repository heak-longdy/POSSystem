@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-customer-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-customer-list', 1) !!}"></i>
                    {!! request('id') ? __('Update', ['name' => __('user.name')]) : __('Create', ['name' => __('user.name')]) !!}
                </h3>
            </div>
            @csrf
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>Name<span>*</span> </label>
                        <input type="text" name="name" value="{!! request('id') ? $data->name : old('name') !!}" placeholder="eg.join">
                        @error('name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>Phone<span>*</span></label>
                        <input name="phone" type="number" value="{!! request('id') ? $data->phone : old('phone') !!}" placeholder="0123456789">
                        @error('phone')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>Email<span>*</span> </label>
                        <input type="text" name="email" value="{!! request('id') ? $data->email : old('email') !!}"
                            data-old="{!! request('id') ? $data->email : old('email') !!}" placeholder="example@gmail.com" autocomplete="off">
                        @error('email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>Date of Birth<span>*</span> </label>
                        <input type="text" name="dob" placeholder="Date of Birth" id="dob" autocomplete="off"
                            value="{!! request('id') ? $data->dob : old('dob') !!}" data-old="{!! request('id') ? $data->dob : old('dob') !!}">
                    </div>
                </div>
                <div class="row-2">
                    @if (!request('id'))
                        <div class="form-row">
                            <label>Password<span>*</span> </label>
                            <input type="password" name="password" id="password" placeholder="New password"
                                autocomplete="new-password">
                            @error('password')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-row">
                            <label>Confirm Password<span>*</span> </label>
                            <input type="password" name="confirm_password" placeholder="Repeat password">
                            @error('confirm_password')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>Status<span>*</span></label>
                        <select name="status">
                            <option value="1" {!! (request('id') && $data->status == 1) || old('status') == 1 ? 'selected' : '' !!}>
                                Active
                            </option>
                            <option value="2" {!! (request('id') && $data->status == 2) || old('status') == 2 ? 'selected' : '' !!}>
                                Disable
                            </option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>identity<span>*</span></label>
                        <input name="identity" type="number" value="{!! request('id') ? $data->identity : old('identity') !!}" placeholder="Enter identity ...">
                        @error('identity')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>Image</label>
                        <div class="form-select-photo image">
                            <div class="select-photo {!! isset($data) && $data->image != null ? 'active' : '' !!}">
                                <div class="icon">
                                    <i data-feather="image"></i>
                                </div>
                                <div class="title">
                                    <span>Image</span>
                                </div>
                            </div>
                            <div class="image-view {!! isset($data) && $data->image != null ? 'active' : '' !!}">
                                <img src="{!! isset($data->image) && $data->image != null ? $data->image_url : null !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                            <input type="text" name="image" s-click-fn="selectImage(event)" autocomplete="off"
                                role="presentation" value="{{ isset($data->image) ? $data->image : null }}">
                            <input type="hidden" name="tmp_file"
                                value="{{ isset($data->image) && $data->image != null ? $data->image : null }}">
                        </div>
                    </div>
                </div>
                
                <div class="form-button">
                    <button type="submit" class="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-customer-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
    @include('admin::file-manager.popup')
@stop

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#dob').datepicker({
                changeYear: true,
                gotoCurrent: true,
                yearRange: '-123:+0',
                dateFormat: "yy-mm-dd",
                onSelect: (select) => {
                    $('#dob').datepicker('option', 'minDate', select);
                }
            });
        })
    </script>

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
                phone: {
                    required: true,
                    phone: true,
                },
                dob: {
                    required: true,
                    dob: true,
                },
                identity: {
                    required: true,
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
