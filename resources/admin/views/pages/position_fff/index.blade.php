@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => 'Position Management'])
    <div class="content-wrapper" id="app">
        <div class="header">
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            <a href="{!! route('admin-position-list', 1) !!}" class="{!! Request::is('admin/position/list/1') ? 'tabActive' : '' !!}">
                                <i class='bx bx-data'></i>
                                Data
                            </a>
                            <a href="{!! route('admin-position-list', 2) !!}" class="{!! Request::is('admin/position/list/2') ? 'tabActive' : '' !!}">
                                <i class='bx bx-navigation'></i>
                                Disable
                            </a>
                            <a href="{!! route('admin-position-list', 'trash') !!}" class="{!! Request::is('admin/position/list/trash') ? 'tabActive' : '' !!}">
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
                        <button mat-flat-button type="submit" class="bg-success btnSearch">
                            <i class='bx bx-search' ></i>
                        </button>
                    </form>
                    <button s-click-link="{!! url()->current() !!}">
                        <i class='bx bx-revision'></i>
                        <span>Reload</span>
                    </button>
                    <button class="btn btn-create" s-click-link="{!! route('admin-position-create') !!}">
                        <i class='bx bx-plus' ></i>
                        <span>Create Position</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            @include('admin::pages.position.table')
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
                            url: `/admin/booking/delete/${id}`,
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
