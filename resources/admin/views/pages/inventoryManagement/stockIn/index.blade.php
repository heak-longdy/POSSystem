@extends('admin::shared.layout')
@section('layout')
    <div class="content-wrapper" x-data="xStockIn">
        <div class="header">
            @include('admin::shared.header', ['header_name' => __('Stock In Management')])
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item {!! Request::is('admin/stock-in/list') ? 'active' : '' !!}" s-click-link="{!! route('admin-stock-in-list') !!}">Data</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row">
                            <input type="text" name="search" placeholder="Enter product" value="{!! request('search') !!}">
                            <i data-feather="filter"></i>
                        </div>&nbsp;&nbsp;
                        <div class="category-content-gp">
                            <select name="shop_id" class="Select2Custom SelectShop" id="shop_id" x-init="fetchSelectShop()" >
                                <option value=""> Select Shop</option>
                            </select>
                        </div>
                        <div class="form-row form-row-inputCus">
                            <input type="text" name="date" placeholder="Date" value="{!! request('date') !!}" id="date" autocomplete="off">
                        </div>
                        <button mat-flat-button type="submit" class="btn-create bg-success">
                            <i data-feather="search"></i>
                            <span>Search</span>
                        </button>
                    </form>
                    @can('stock-in-create')
                        <button class="btn-create" s-click-link="{!! route('admin-stock-in-create') !!}">
                            <i data-feather="plus-circle"></i>
                            <span>Create Stock In</span>
                        </button>
                    @endcan
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>@lang('user.button.reload')</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            @include('admin::pages.inventoryManagement.stockIn.table')
        </div>
    </div>
@stop
@section('script')
    <script lang="ts">
        $(document).ready(function() {
            $("#date").datepicker({
                changeYear: true,
                gotoCurrent: true,
                yearRange: "-1:+1",
                dateFormat: "yy-mm-dd",
            });
        });
    </script>
    <script>
        var option = "<option selected></option>";
        //shop
        var shop = $(option).val(`{{ isset($shop->id) ? $shop->id : '' }}`).text(
            `{{ isset($shop->name) ? $shop->name : '' }}`);
        $('.SelectShop').append(shop).trigger('change');

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
            Alpine.data("xStockIn", () => ({
                loading: false,
                loadingSubmit: false,
                memberCarData: [],
                baseImageUrl: "{{ asset('file_manager') }}",
                dataError: null,
                init() {},
                fetchSelectShop() {
                    $(`#shop_id`).select2({
                        placeholder: `Select Shop`,
                        ajax: {
                            url: '{{ route('admin-select-stock-shop') }}',
                            dataType: 'json',
                            type: "GET",
                            quietMillis: 50,
                            data: function(param) {
                                return {
                                    search: param.term
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: $.map(data.data, function(item) {
                                        return {
                                            text: item?.name ? item?.name : '',
                                            id: item.id
                                        }
                                    })
                                };
                            }
                        }
                    }).on('select2:open', (e) => {
                        document.querySelector('.select2-search__field').focus();
                    });
                },
            }));
        });
    </script>
@stop

