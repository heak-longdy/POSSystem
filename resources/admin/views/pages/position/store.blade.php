@extends('admin::shared.layout')
@php
    $toastTitles = [
        'success' => __('global.workspace.success'),
        'warning' => __('global.workspace.warning'),
    ];
@endphp
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-position-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-position-list', 1) !!}"></i>
                    {{ request('id') ? __('position.form.title.update') : __('position.form.title.create') }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-3">
                    <div class="form-row">
                        <label>{{ __('position.form.name.label') }} <span>*</span> </label>
                        <input type="text" name="title" value="{!! request('id') ? $data?->title : old('title') !!}" placeholder="{{ __('position.form.name.placeholder') }}">
                        @error('title')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-3">
                    <div class="form-row">
                        <label>@lang('global.form.status.label')<span>*</span></label>
                        <select name="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data->status == $key) || old('status') == $key ? 'selected' : '' !!}>{{ $key == 1 ? __('global.form.status.active') : __('global.form.status.disable') }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>{{ __('global.button.submit') }}</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-position-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>{{ __('global.button.cancel') }}</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop
