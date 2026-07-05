@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-position-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-position-list', 1) !!}"></i>
                    {{ request('id') ? 'Update Position' : 'Create Position' }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-3">
                    <div class="form-row">
                        <label>Title <span>*</span> </label>
                        <input type="text" name="title" value="{!! request('id') ? $data?->title : old('title') !!}" placeholder="Enter title ...">
                        @error('title')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-3">
                    <div class="form-row">
                        <label>@lang('adminGlobal.form.status.label')<span>*</span></label>
                        <select name="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data->status == $key) || old('status') == $key ? 'selected' : '' !!}>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-position-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop
