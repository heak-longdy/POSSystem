@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin" x-data="xService">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-currency-save',request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-currency-list', 1) !!}"></i>
                    Create Currency
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>Name<span>*</span></label>
                        <input type="text" name="name" placeholder="Enter name" value="{!! request('id') ? $data->name : old('name') !!}">
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>Ordering<span>*</span></label>
                        <input type="text" name="ordering" placeholder="Enter ordering number" value="{!! request('id') ? $data->ordering : old('ordering') !!}">
                        @error('ordering')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>@lang('user.form.status.label')<span>*</span></label>
                        <select name="status">
                            <option value="1" {!! (request('id') && $data->status == 1) || old('status') == 1 ? 'selected' : '' !!}>
                                Active
                            </option>
                            <option value="2" {!! (request('id') && $data->status == 2) || old('status') == 2 ? 'selected' : '' !!}>
                                Disable
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-currency-list', 1) !!}">
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
            ordering:{
                required:true,
            }
        });
    });
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
