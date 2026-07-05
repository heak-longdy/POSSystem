@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper">
        <div class="header">
            <div class="header-wrapper marginBottom">
                <div class="btn-toggle-sidebar">
                    <span>User Management</span>
                </div>
                <div class="navHeaderRight">
                    {{-- @can('barber-create')
                        <button class="btn btn-create" s-click-link="{!! route('admin-barber-create') !!}">
                            <i class='bx bx-plus-circle'></i>
                            <span>Create Barber</span>
                        </button>
                    @endcan
                    <button s-click-link="{!! url()->current() !!}" class="refresh">
                        <i data-feather="refresh-ccw"></i>
                        <span>Reload</span>
                    </button> --}}
                </div>
            </div>
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            <a href="{!! route('admin-customer-list', 1) !!}" class="{!! Request::is('admin/customer/list/1') ? 'tabActive' : '' !!}">
                                <i class='bx bx-data'></i>
                                Active
                            </a>
                            <a href="{!! route('admin-customer-list', 2) !!}" class="{!! Request::is('admin/customer/list/2') ? 'tabActive' : '' !!}">
                                <i class='bx bxs-color-fill'></i>
                                Disable
                            </a>
                            <a href="{!! route('admin-customer-list', 'trash') !!}" class="{!! Request::is('admin/customer/list/trash') ? 'tabActive' : '' !!}">
                                <i class='bx bx-trash-alt'></i>
                                Trash
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
            @include('admin::pages.customer.table')
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
                            url: `/admin/customer/delete/${id}`,
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
