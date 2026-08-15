@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => 'Stock Transfer Management'])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => 'Create Stock Transfer',
            'createPermission' => 'stock-transfer-create',
            'filterStatus' => false,
            // 'filterView' => 'admin::pages.inventoryManagement.partials.stock-filter',
            'filterData' => ['shop' => $shop],
            'showTabs' => true,
            'exportUrl' => '',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => 'Nº', 'class' => '', 'colVal' => 5],
                ['field' => 'product_title', 'title' => 'Product', 'class' => 'text left', 'colVal' => 14],
                ['field' => 'category_title', 'title' => 'Category', 'class' => 'text left', 'colVal' => 9],
                ['field' => 'uom_title', 'title' => 'UOM', 'class' => '', 'colVal' => 5],
                ['field' => 'qty', 'title' => 'Qty', 'class' => '', 'colVal' => 7],
                ['field' => 'created_date', 'title' => 'Date', 'class' => '', 'colVal' => 11],
                ['field' => 'remark', 'title' => 'Remark', 'class' => 'text left', 'colVal' => 11],
                ['field' => 'shop_title', 'title' => 'From Shop', 'class' => 'text left', 'colVal' => 10],
                ['field' => 'destination_shop_title', 'title' => 'To Shop', 'class' => 'text left', 'colVal' => 10],
                ['field' => 'request_by_title', 'title' => 'Requested By', 'class' => 'text left', 'colVal' => 8],
                ['field' => 'stock_status_title', 'title' => 'Status', 'class' => '', 'colVal' => 5],
                [
                    'field' => 'action',
                    'title' => '',
                    'class' => '',
                    'colVal' => 5,
                    'actions' => [
                        [
                            'key' => 'active',
                            'action' => [
                                ['url' => 'view', 'title' => 'View', 'icon' => 'visibility', 'type' => 'link'],
                                ['url' => 'edit', 'title' => 'Edit', 'icon' => 'edit', 'type' => 'link'],
                                ['url' => 'delete', 'title' => 'Delete', 'icon' => 'Delete', 'class' => 'text-danger'],
                            ],
                        ],
                        [
                            'key' => 'disable',
                            'action' => [['url' => 'status', 'title' => 'Disable', 'icon' => 'hide_source', 'class' => 'text-danger']],
                        ],
                        [
                            'key' => 'enable',
                            'action' => [['url' => 'status', 'title' => 'Enable', 'icon' => 'refresh']],
                        ],
                        [
                            'key' => 'trash',
                            'action' => [
                                ['url' => 'restore', 'title' => 'Restore', 'icon' => 'settings_backup_restore'],
                                ['url' => 'destroy', 'title' => 'Destroy', 'icon' => 'Delete', 'class' => 'text-danger'],
                            ],
                        ],
                    ],
                ],
            ],
        ])
        @endcomponent
    </div>
@stop

@section('script')
    @include('admin::pages.inventoryManagement.partials.stock-index-script')
@stop
