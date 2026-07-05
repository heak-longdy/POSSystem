@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin" x-data="xService">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-customer-save') !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-customer-list', 1) !!}"></i>
                   Create Customer
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>Name <span>*</span> </label>
                        <input type="text" name="name" value=""  placeholder="">
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>Phone<span>*</span> </label>
                        <input type="number" name="phone" value=""
                            placeholder="Enter phone ..." step="1" min="1">
                        @error('phone')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>Address</label>
                        <textarea placeholder="" name="address" row="3"></textarea>
                        @error('address')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>Profile</label>
                        <div class="form-select-photo image">
                            <div class="select-photo {!! request('id') && isset($data) && $data->profile != null ? 'active' : '' !!}">
                                <div class="icon">
                                    <i data-feather="image"></i>
                                </div>
                                <div class="title">
                                    <span>@lang('adminGlobal.form.image.placeholder')</span>
                                </div>
                            </div>
                            <div class="image-view">
                                <img src="{!! request('id') && isset($data) && $data->image != null ? asset('file_manager' . $data->image) : null !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                            <input type="text" name="image" s-click-fn="selectImage(event)" autocomplete="off"
                                role="presentation">
                            <input type="hidden" name="tmp_file" value="">
                        </div>
                        @error('image')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    {{-- <div class="form-row">
                        <label>@lang('adminGlobal.form.status.label')<span>*</span></label>
                        <select name="status">
                            <option value="1" {!! (request('id') && $data->status == 1) || old('status') == 1 ? 'selected' : '' !!}>@lang('adminGlobal.form.status.active')</option>
                            <option value="2" {!! (request('id') && $data->status == 2) || old('status') == 2 ? 'selected' : '' !!}>@lang('adminGlobal.form.status.disable')</option>
                        </select>
                    </div> --}}
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
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
    <script lang="ts">
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
    <script>
        const header = {
            headers: {
                "Content-Type": "application/x-www-form-urlencoded;charset=utf-8",
                Accept: "application/json",
            },
            responseType: "json",
        };
        document.addEventListener('alpine:init', () => {
            Alpine.data("xService", () => ({}));
        });
    </script>
@stop
