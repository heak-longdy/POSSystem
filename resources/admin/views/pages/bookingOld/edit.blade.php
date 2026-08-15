@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin" x-data="xService">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-customer-save', $data->id) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-customer-list', 1) !!}"></i>
                    Edit Customer
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body" x-data="{ tabSta: 'km' }">
                <div class="row-2">
                    <div class="form-row">
                        <label>Name <span>*</span> </label>
                        <input type="text" name="name" value="{!! isset($data->name) ? $data->name : old('name') !!}"  placeholder="">
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    
                    <div class="form-row">
                        <label>Phone<span>*</span> </label>
                        <input type="number" name="phone"
                            value="{{ $data->phone ? $data->phone : old('phone') }}"   placeholder="">
                        @error('phone')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>Address</label>
                        <textarea placeholder="" name="address" row="3">{{ $data->address ? $data->address : old('address') }}</textarea>
                        @error('address')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>@lang('adminGlobal.form.image.label')</label>
                        <div class="form-select-photo image">
                            <div class="select-photo {!! isset($data) && $data->profile != null ? 'active' : '' !!}">
                                <div class="icon">
                                    <i data-feather="image"></i>
                                </div>
                                <div class="title">
                                    <span>@lang('adminGlobal.form.image.placeholder')</span>
                                </div>
                            </div>
                            <div class="image-view {!! isset($data) && $data->profile != null ? 'active' : '' !!}">
                                <img src="{!! isset($data->profile) && $data->profile != null ? $data->image_url : null !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                            <input type="text" name="image" s-click-fn="selectImage(event)" autocomplete="off"
                                role="presentation" value="{{ isset($data->profile) ? $data->profile : null }}">
                            <input type="hidden" name="tmp_file"
                                value="{{ isset($data->profile) && $data->profile != null ? $data->profile : null }}">
                        </div>
                    </div>
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
        $(document).ready(function() {
            $validator("#form", {
                name: {
                    required: true,
                },
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
