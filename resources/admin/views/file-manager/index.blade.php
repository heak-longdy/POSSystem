@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('file-manager.title')])
    <div class="content-wrapper">
        <div class="content-body file-manager-page" x-data="fileManagerPage">
            @include('admin::file-manager.content')
        </div>
    </div>

    <style>
        /* Fit the existing file browser inside the standard workspace content area. */
        .file-manager-page .file-manager-wrapper.full-page {
            width: 100%;
            min-width: 800px;
            height: calc(100vh - 45px);
            min-height: 500px;
            max-height: none;
        }

        .file-manager-page .file-manager-body {
            flex: 1;
            min-height: 0;
        }
    </style>
@endsection
