@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper">
        {{-- <div class="header">
            @include('admin::shared.header', ['header_name' => 'Currency Management'])
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item {!! Request::is('admin/currency/list/1') ? 'active' : '' !!}" s-click-link="{!! route('admin-currency-list', 1) !!}">
                            Active</div>
                        <div class="menu-item {!! Request::is('admin/currency/list/2') ? 'active' : '' !!}" s-click-link="{!! route('admin-currency-list', 2) !!}">
                            Inactive</div>
                        <div class="menu-item {!! Request::is('admin/currency/list/trash') ? 'active' : '' !!}" s-click-link="{!! route('admin-currency-list', 'trash') !!}">Trash</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row">
                            <input type="text" name="search" placeholder="Search"
                                value="{!! request('search') !!}">
                            <i data-feather="filter"></i>
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success">
                            <i data-feather="search"></i>
                            <span>Search</span>
                        </button>
                    </form>
                    @can('currency-create')
                        <button class="btn-create" s-click-link="{!! route('admin-currency-create') !!}">
                            <i data-feather="plus-circle"></i>
                            <span>Create Currency</span>
                        </button>
                    @endcan
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button>
                </div>
            </div>
        </div> --}}
        <div class="header">
            <div class="header-wrapper marginBottom">
                <div class="btn-toggle-sidebar">
                    <span>Admin Management</span>
                </div>
                <div class="navHeaderRight">
                    @can('barber-create')
                        <button class="btn btn-create" s-click-link="{!! route('admin-barber-create') !!}">
                            <i class='bx bx-plus-circle'></i>
                            <span>@lang('user.button.create')</span>
                        </button>
                    @endcan
                    <button s-click-link="{!! url()->current() !!}" class="refresh">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button>
                </div>
            </div>
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            <a href="{!! route('admin-user-list', 1) !!}" class="{!! Request::is('admin/user/list/1') ? 'tabActive' : '' !!}">
                                <i class='bx bx-data'></i>
                                Active
                            </a>
                            <a href="{!! route('admin-user-list', 2) !!}" class="{!! Request::is('admin/user/list/2') ? 'tabActive' : '' !!}">
                                <i class='bx bxs-color-fill'></i>
                                Disable
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row w80">
                            <select name="payment_status">
                                <option value="">All Status</option>
                                <option value="Pending" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}> Pending</option>
                                <option value="Paid" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}> Paid</option>
                            </select>
                        </div>
                        
                        <button mat-flat-button type="submit" class="btn-create bg-success btnSearch">
                            <i data-feather="search" style="margin-right: 0;"></i>
                        </button>
                    </form>
                    <button type="button" @click="excel()" class="btnExcel">
                        <i class="material-symbols-outlined">upgrade</i>
                        <span>Excel</span>
                    </button>
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            @include('admin::pages.currency.table')
        </div>
    </div>
@stop
@section('script')
    <script lang="ts">
        $("body").on("click", ".trash-btn", function() {
            let url = $(this).data('url');
            let id = url.split('/').pop();
            let row = $(this).closest('.column');
            Swal.fire({
                customClass: "confirm-message",
                icon: "warning",
                html: `Are you sure to delete <b>${$(this).data('name')}</b>?`,
                input: 'checkbox',
                inputValue: 1,
                inputPlaceholder: 'Move to trash.',
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel",
            }).then(result => {
                if (result.isConfirmed) {
                    if (result.value == 1) {
                        $.ajax({
                            url: `/admin/currency/delete/${id}`,
                            method: 'GET',
                            success: function(data) {
                                row.remove();
                                Toast({
                                    title: 'Success Message',
                                    message: 'Delete Successfully',
                                    status: 'success',
                                    duration: 5000,
                                });
                            }
                        });
                    } else {
                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function(data) {
                                row.remove();
                                Toast({
                                    title: 'Success Message',
                                    message: 'Delete Successfully',
                                    status: 'success',
                                    duration: 5000,
                                });
                            }
                        });
                    }
                }
            });
        });
    </script>
@stop
