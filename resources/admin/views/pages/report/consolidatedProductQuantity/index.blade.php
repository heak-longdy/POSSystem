@extends('admin::shared.layout')
@section('layout')
    @include('admin::pages.report.customerRemaining.styles')
    <style>
        .consolidated-product-report .filter-header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 10px; }
        .consolidated-product-report .preset-badge-group { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .consolidated-product-report .preset-btn { border: 1px solid #e2e8f0; background: #f8fafc; color: #475569; font-size: 12px; font-weight: 500; padding: 5px 12px; border-radius: 7px; cursor: pointer; transition: all 0.15s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .consolidated-product-report .preset-btn:hover { background: #e2e8f0; color: #1e293b; }
        .consolidated-product-report .preset-btn.active { background: #2563eb; color: #ffffff; border-color: #2563eb; }
        .consolidated-product-report .filter-date-input { position: relative; cursor: pointer; }
        .consolidated-product-report .filter-date-input input { padding-right: 36px; }
        .consolidated-product-report .filter-date-input .bx-calendar { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #64748b; pointer-events: none; }
        #ui-datepicker-div.consolidated-product-datepicker { z-index: 9999 !important; }

        /* Detail Modal */
        .report-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .report-modal-card {
            background: #ffffff;
            border-radius: 14px;
            width: 100%;
            max-width: 1100px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }

        .report-modal-header {
            padding: 16px 22px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .report-modal-header h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-close-report-modal {
            background: transparent !important;
            border: none !important;
            font-size: 24px !important;
            line-height: 1 !important;
            color: #94a3b8 !important;
            cursor: pointer !important;
            padding: 0 4px !important;
            min-width: unset !important;
        }

        .btn-close-report-modal:hover { color: #1e293b !important; }

        .report-modal-body {
            padding: 18px 22px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-period-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .modal-summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
        }

        .modal-summary-box span {
            display: block;
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }

        .modal-summary-box strong {
            display: block;
            font-size: 16px;
            color: #1e293b;
            margin-top: 4px;
        }

        .report-modal-footer {
            padding: 12px 22px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
        }
    </style>
    @include('admin::shared.header', ['header_name' => __('consolidated_product_quantity.title')])
    <div class="content-wrapper customer-remaining-report consolidated-product-report" x-data="xConsolidatedProductQuantityReport">
        <div class="header box-shadow-bottom">
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row"><div class="tabs">
                        <a href="{{ route('admin-' . $routeName . '-index', $filters) }}" class="tabActive" aria-current="page"><i class="bx bx-package"></i> {{ __('consolidated_product_quantity.tab') }}</a>
                    </div></div>
                </div>
                <div class="header-action-button">
                    @can('report-consolidated-product-quantity-excel')
                        <button type="button" class="btn-excel-export" @click="excel()" :disabled="exportLoading">
                            <i class="bx bx-export"></i>
                            <span x-text="exportLoading ? @js(__('sales_report.excel.exporting')) : @js(__('global.button.export'))">{{ __('global.button.export') }}</span>
                        </button>
                    @endcan
                    <button type="button" s-click-link="{{ route('admin-' . $routeName . '-index', $filters) }}"><i class="bx bx-refresh"></i><span>{{ __('sales_report.button.reload') }}</span></button>
                </div>
            </div>
        </div>
        <div class="content-body" style="padding: 14px 20px 24px;">
            @if ($errors->any())
                <div class="report-error" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="report-filter-panel">
                <form x-ref="filterForm" method="GET" action="{{ route('admin-' . $routeName . '-index') }}">
                    @php
                        $today = \Carbon\CarbonImmutable::today();
                        $lastMonth = $today->subMonthNoOverflow();
                        $quickPresets = [
                            'today' => [$today, $today],
                            'yesterday' => [$today->subDay(), $today->subDay()],
                            'last_7_days' => [$today->subDays(6), $today],
                            'last_30_days' => [$today->subDays(29), $today],
                            'this_month' => [$today->startOfMonth(), $today->endOfMonth()],
                            'last_month' => [$lastMonth->startOfMonth(), $lastMonth->endOfMonth()],
                        ];
                    @endphp
                    <div class="filter-header-row">
                        <div class="preset-badge-group">
                            <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 4px;">{{ __('sales_report.presets.title') }}</span>
                            @foreach ($quickPresets as $preset => [$from, $to])
                                @php
                                    $presetDates = ['from_date' => $from->toDateString(), 'to_date' => $to->toDateString()];
                                    $isActive = $filters['from_date'] === $presetDates['from_date'] && $filters['to_date'] === $presetDates['to_date'];
                                @endphp
                                <a href="{{ route('admin-' . $routeName . '-index', array_merge($filters, $presetDates)) }}"
                                    class="preset-btn {{ $isActive ? 'active' : '' }}" @if ($isActive) aria-current="true" @endif>
                                    {{ __('sales_report.presets.' . $preset) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="filter-form-grid">
                        @foreach (['from_date' => 'fromDate', 'to_date' => 'toDate'] as $field => $ref)
                            <div class="filter-field-wrap" x-id="['cpq-{{ $field }}']">
                                <label :for="$id('cpq-{{ $field }}')">{{ __('consolidated_product_quantity.' . $field) }}</label>
                                <div class="filter-date-input" @click="$refs.{{ $ref }}.focus()">
                                    <input type="text" name="{{ $field }}" :id="$id('cpq-{{ $field }}')" x-ref="{{ $ref }}" class="filter-input" value="{{ $filters[$field] }}" placeholder="{{ __('sales_report.filter.placeholder_date') }}" autocomplete="off">
                                    <i class="bx bx-calendar" aria-hidden="true"></i>
                                </div>
                            </div>
                        @endforeach
                        @foreach (['shop_id' => ['shop', 'all_shops', $shops], 'category_id' => ['category', 'all_categories', $categories], 'product_id' => ['product', 'all_products', $products]] as $field => [$label, $placeholder, $options])
                            <div class="filter-field-wrap" x-id="['cpq-{{ $field }}']">
                                <label :for="$id('cpq-{{ $field }}')">{{ __('consolidated_product_quantity.' . $label) }}</label>
                                <div class="select2Group">
                                    <select name="{{ $field }}" :id="$id('cpq-{{ $field }}')" class="SelectField filter-select select2">
                                        <option value="">{{ __('consolidated_product_quantity.' . $placeholder) }}</option>
                                        @foreach ($options as $option)
                                            <option value="{{ $option->id }}" @selected(($filters[$field] ?? '') == $option->id)>{{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="select2Reset" x-show="filterValues.{{ $field }}" @click.stop="resetFilter('{{ $field }}')" @keydown.enter.prevent="resetFilter('{{ $field }}')" role="button" tabindex="0" aria-label="{{ __('global.button.reset') }}" style="display: none;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path></svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="filter-field-wrap" x-id="['cpq-search']">
                            <label :for="$id('cpq-search')">{{ __('consolidated_product_quantity.search') }}</label>
                            <input type="text" :id="$id('cpq-search')" class="filter-input" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('consolidated_product_quantity.search_placeholder') }}">
                        </div>
                        <div class="filter-actions-wrap">
                            <button class="btn btn-filter-search" type="submit"><i class="bx bx-search"></i><span>{{ __('sales_report.button.filter') }}</span></button>
                            <a class="btn btn-filter-reset" href="{{ route('admin-' . $routeName . '-index') }}"><i class="bx bx-reset"></i><span>{{ __('customer_remaining_report.reset') }}</span></a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="kpi-grid">
                @foreach (['product_count' => ['bx-package', 'indigo'], 'order_count' => ['bx-receipt', 'primary'], 'total_quantity' => ['bx-bar-chart-alt-2', 'success'], 'shop_count' => ['bx-store', 'warning']] as $key => [$icon, $color])
                    <div class="kpi-card"><div class="kpi-icon-wrap {{ $color }}"><i class="bx {{ $icon }}"></i></div><div class="kpi-info">
                        <div class="kpi-title">{{ __('consolidated_product_quantity.' . $key) }}</div>
                        <div class="kpi-value">{{ number_format($summary->$key) }}</div>
                    </div></div>
                @endforeach
            </div>
            <p class="report-description">{{ __('consolidated_product_quantity.description') }}</p>
            <div class="report-table-card">
                <div class="table-custom-header">
                    <h4><i class="bx bx-package"></i>{{ __('consolidated_product_quantity.title') }}</h4>
                    <span class="report-count">{{ __('customer_remaining_report.results', ['from' => $rows->firstItem() ?? 0, 'to' => $rows->lastItem() ?? 0, 'total' => number_format($rows->total())]) }}</span>
                </div>
                <div class="table-responsive-custom">
                    <table class="report-data-table">
                        <thead><tr>
                            <th scope="col" class="text-center">{{ __('global.table.no') }}</th>
                            @foreach (['product_code', 'product_name', 'category_name', 'uom_name', 'order_count', 'total_quantity', 'total_amount'] as $key)
                                <th scope="col" class="{{ in_array($key, ['order_count', 'total_quantity', 'total_amount']) ? 'text-right' : '' }}">{{ __('consolidated_product_quantity.' . $key) }}</th>
                            @endforeach
                            <th scope="col" class="text-center d-print-none">{{ __('consolidated_product_quantity.actions') }}</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="text-center">{{ $rows->firstItem() + $loop->index }}</td>
                                    <td>{{ $row->product_code ?: '—' }}</td>
                                    <td class="report-name">{{ $row->product_name ?: __('consolidated_product_quantity.unknown_product', ['id' => $row->product_id]) }}</td>
                                    <td>{{ $row->category_name ?: '—' }}</td>
                                    <td>{{ $row->uom_name ?: '—' }}</td>
                                    <td class="text-right">{{ number_format($row->order_count) }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($row->total_quantity) }}</td>
                                    <td class="text-right font-weight-bold">${{ number_format($row->total_amount, 2) }}</td>
                                    <td class="text-center d-print-none">
                                        <button type="button" class="btn-drilldown" @click="openDetailsModal({{ $row->product_id ?: 'null' }})">
                                            <i class="bx bx-detail"></i>
                                            <span>{{ __('consolidated_product_quantity.view_details') }}</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9"><div class="empty-placeholder"><i class="bx bx-package"></i><p>{{ __('consolidated_product_quantity.empty') }}</p></div></td></tr>
                            @endforelse
                        </tbody>
                        <tfoot><tr><td colspan="5">{{ __('consolidated_product_quantity.total') }}</td><td class="text-right">{{ number_format($summary->order_count) }}</td><td class="text-right">{{ number_format($summary->total_quantity) }}</td><td class="text-right">${{ number_format($summary->total_amount, 2) }}</td><td class="d-print-none"></td></tr></tfoot>
                    </table>
                </div>
                @include('admin::pages.report.customerRemaining.pagination', ['paginator' => $rows])
            </div>
            <p class="report-note">{{ __('consolidated_product_quantity.order_count_note') }}</p>
        </div>
        <template x-if="exportLoading"><div class="loadingFullSizeLayout"><div class="loading loadingSubmit"><span id="spinner"></span><label>{{ __('order.export_excel') }}</label></div></div></template>

        <!-- Detail Modal -->
        <template x-if="showDetailsModal">
            <div class="report-modal-backdrop" @click.self="closeDetailsModal()">
                <div class="report-modal-card">
                    <div class="report-modal-header">
                        <h3>
                            <i class="bx bx-detail text-primary"></i>
                            <span>{{ __('consolidated_product_quantity.order_item_details') }}</span>
                        </h3>
                        <button type="button" class="btn-close-report-modal" @click="closeDetailsModal()">&times;</button>
                    </div>

                    <div class="report-modal-body">
                        <template x-if="detailsLoading">
                            <div style="padding: 40px; text-align: center;">
                                <div class="loading loadingSubmit" style="position: static; transform: none; display: inline-block;">
                                    <span id="spinner"></span>
                                    <label>{{ __('sales_report.excel.exporting') }}</label>
                                </div>
                            </div>
                        </template>

                        <template x-if="!detailsLoading && detailsData">
                            <div>
                                <div class="modal-period-summary">
                                    <div class="modal-summary-box">
                                        <span>{{ __('consolidated_product_quantity.from_date') }}</span>
                                        <strong x-text="detailsData.filters.from_date || '—'"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('consolidated_product_quantity.to_date') }}</span>
                                        <strong x-text="detailsData.filters.to_date || '—'"></strong>
                                    </div>
                                    <div class="modal-summary-box">
                                        <span>{{ __('consolidated_product_quantity.product_count') }}</span>
                                        <strong x-text="detailsData.rows.total ? Number(detailsData.rows.total).toLocaleString() : '0'"></strong>
                                    </div>
                                </div>

                                <div class="table-responsive-custom">
                                    <table class="report-data-table">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="text-center">{{ __('global.table.no') }}</th>
                                                <th scope="col">{{ __('consolidated_product_quantity.invoice_number') }}</th>
                                                <th scope="col">{{ __('consolidated_product_quantity.order_date') }}</th>
                                                <th scope="col">{{ __('consolidated_product_quantity.shop') }}</th>
                                                <th scope="col">{{ __('consolidated_product_quantity.product_code') }}</th>
                                                <th scope="col">{{ __('consolidated_product_quantity.product_name') }}</th>
                                                <th scope="col">{{ __('consolidated_product_quantity.category_name') }}</th>
                                                <th scope="col">{{ __('consolidated_product_quantity.uom_name') }}</th>
                                                <th scope="col" class="text-right">{{ __('consolidated_product_quantity.quantity') }}</th>
                                                <th scope="col" class="text-right">{{ __('consolidated_product_quantity.unit_price') }}</th>
                                                <th scope="col" class="text-right">{{ __('consolidated_product_quantity.total_amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(item, index) in detailsData.rows.data" :key="item.id">
                                                <tr>
                                                    <td class="text-center" x-text="(detailsData.rows.from || 1) + index"></td>
                                                    <td>
                                                        @can('order-view')
                                                            <a class="report-link" :href="'{{ url('admin/order/detail') }}/' + item.order_id" x-text="item.invoice_number || ('00000' + item.order_id).slice(-5)"></a>
                                                        @else
                                                            <span x-text="item.invoice_number || ('00000' + item.order_id).slice(-5)"></span>
                                                        @endcan
                                                    </td>
                                                    <td class="report-nowrap" x-text="item.order_date_formatted || item.order_date || '—'"></td>
                                                    <td x-text="item.shop_name || '—'"></td>
                                                    <td x-text="item.product_code || '—'"></td>
                                                    <td class="report-name" x-text="item.product_name || ('{{ __('consolidated_product_quantity.unknown_product', ['id' => '']) }}' + item.product_id)"></td>
                                                    <td x-text="item.category_name || '—'"></td>
                                                    <td x-text="item.uom_name || '—'"></td>
                                                    <td class="text-right" x-text="Number(item.quantity || 0).toLocaleString()"></td>
                                                    <td class="text-right" x-text="'$' + Number(item.effective_unit_price || 0).toFixed(2)"></td>
                                                    <td class="text-right font-weight-bold" x-text="'$' + Number(item.total_amount || 0).toFixed(2)"></td>
                                                </tr>
                                            </template>
                                            <template x-if="!detailsData.rows.data || detailsData.rows.data.length === 0">
                                                <tr><td colspan="11"><div class="empty-placeholder"><i class="bx bx-detail"></i><p>{{ __('consolidated_product_quantity.no_order_items') }}</p></div></td></tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <template x-if="detailsData.rows.last_page > 1">
                                    <div class="modal-pagination" style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                                        <span style="font-size: 12px; color: #64748b;" x-text="'{{ __('customer_remaining_report.results', ['from' => '__FROM__', 'to' => '__TO__', 'total' => '__TOTAL__']) }}'.replace('__FROM__', detailsData.rows.from).replace('__TO__', detailsData.rows.to).replace('__TOTAL__', Number(detailsData.rows.total).toLocaleString())"></span>
                                        <div style="display: flex; gap: 6px; align-items: center;">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="detailsData.rows.current_page <= 1" @click="fetchDetails(detailsProductId, detailsData.rows.current_page - 1)" style="padding: 2px 8px;">
                                                <i class="bx bx-chevron-left"></i>
                                            </button>
                                            <span style="font-size: 12px; padding: 2px 6px; font-weight: 600;" x-text="detailsData.rows.current_page + ' / ' + detailsData.rows.last_page"></span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="detailsData.rows.current_page >= detailsData.rows.last_page" @click="fetchDetails(detailsProductId, detailsData.rows.current_page + 1)" style="padding: 2px 8px;">
                                                <i class="bx bx-chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="report-modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="closeDetailsModal()">{{ __('global.button.close') }}</button>
                    </div>
                </div>
            </div>
        </template>
                    </table>
                </div>
                @include('admin::pages.report.customerRemaining.pagination', ['paginator' => $rows])
            </div>
            <p class="report-note">{{ __('consolidated_product_quantity.order_count_note') }}</p>
        </div>
        <template x-if="exportLoading"><div class="loadingFullSizeLayout"><div class="loading loadingSubmit"><span id="spinner"></span><label>{{ __('order.export_excel') }}</label></div></div></template>
    </div>
@stop
@section('script')
    @include('admin::pages.report.partials.excel-assets')
    @include('admin::pages.report.consolidatedProductQuantity.script')
@stop
