@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('stock_out.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('stock_out.button.create'),
            'createPermission' => 'stock-out-create',
            'filterStatus' => false,
            // 'filterView' => 'admin::pages.inventoryManagement.partials.stock-filter',
            'filterData' => ['shop' => $shop],
            'showTabs' => true,
            'exportUrl' => '',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'product_title', 'title' => __('stock_out.table.product'), 'class' => 'text left', 'colVal' => 14],
                ['field' => 'category_title', 'title' => __('stock_out.table.category'), 'class' => 'text left', 'colVal' => 9],
                ['field' => 'uom_title', 'title' => __('stock_out.table.uom'), 'class' => '', 'colVal' => 5],
                ['field' => 'qty', 'title' => __('stock_out.table.qty'), 'class' => '', 'colVal' => 6],
                ['field' => 'created_date', 'title' => __('stock_out.table.date'), 'class' => '', 'colVal' => 11],
                ['field' => 'remark', 'title' => __('stock_out.table.remark'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'shop_title', 'title' => __('stock_out.table.shop'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'destination_title', 'title' => __('stock_out.table.to'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'request_by_title', 'title' => __('stock_out.table.requested_by'), 'class' => 'text left', 'colVal' => 8],
                ['field' => 'stock_status_title', 'title' => __('stock_out.table.status'), 'class' => '', 'colVal' => 5],
                [
                    'field' => 'action',
                    'title' => '',
                    'class' => '',
                    'colVal' => 5,
                    'actions' => [
                        [
                            'key' => 'active',
                            'action' => [
                                ['url' => 'view', 'title' => __('global.action.view'), 'icon' => 'visibility', 'type' => 'link'],
                                ['url' => 'edit', 'title' => __('global.action.edit'), 'icon' => 'edit', 'type' => 'link'],
                                ['url' => 'delete', 'title' => __('global.action.delete'), 'icon' => 'Delete', 'class' => 'text-danger'],
                            ],
                        ],
                        [
                            'key' => 'disable',
                            'action' => [['url' => 'status', 'title' => __('global.action.disable'), 'icon' => 'hide_source', 'class' => 'text-danger']],
                        ],
                        [
                            'key' => 'enable',
                            'action' => [['url' => 'status', 'title' => __('global.action.enable'), 'icon' => 'refresh']],
                        ],
                        [
                            'key' => 'trash',
                            'action' => [
                                ['url' => 'restore', 'title' => __('global.action.restore'), 'icon' => 'settings_backup_restore'],
                                ['url' => 'destroy', 'title' => __('global.action.destroy'), 'icon' => 'Delete', 'class' => 'text-danger'],
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
