@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper">
        <div class="header">
            @include('admin::shared.header', ['header_name' => 'Products Management'])
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item {!! Request::is('admin/product/list/1') ? 'active' : '' !!}" s-click-link="{!! route('admin-product-list', 1) !!}">
                            @lang('global.tab.active')</div>
                        <div class="menu-item {!! Request::is('admin/product/list/2') ? 'active' : '' !!}" s-click-link="{!! route('admin-product-list', 2) !!}">
                            @lang('global.tab.disable')</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row">
                            <input type="text" name="search" placeholder="@lang('global.filter.search')"
                                value="{!! request('search') !!}">
                            <i data-feather="filter"></i>
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success">
                            <i data-feather="search"></i>
                            <span>@lang('global.button.search')</span>
                        </button>
                    </form>
                    @can('product-create')
                        <button class="btn-create" s-click-link="{!! route('admin-product-create') !!}">
                            <i data-feather="plus-circle"></i>
                            <span>Create Product</span>
                        </button>
                    @endcan
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>@lang('global.button.reload')</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            @include('admin::pages.product.table')
        </div>
    </div>
@stop
