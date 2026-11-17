@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('purchase.title')])

    <style>
        .purchase-listing-wrapper {
            padding: 16px 20px;
        }

        /* KPI Summary Cards */
        .purchase-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .purchase-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .purchase-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }

        .purchase-kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .purchase-kpi-icon.blue { background: #eff6ff; color: #2563eb; }
        .purchase-kpi-icon.indigo { background: #e0e7ff; color: #4f46e5; }
        .purchase-kpi-icon.emerald { background: #ecfdf5; color: #059669; }
        .purchase-kpi-icon.amber { background: #fffbeb; color: #d97706; }

        .purchase-kpi-info h4 {
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin: 0 0 4px 0;
        }

        .purchase-kpi-info .val {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        /* Tab Navigation */
        .purchase-tabs-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 18px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .purchase-tab-btn {
            padding: 10px 18px;
            font-size: 13.5px;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .purchase-tab-btn:hover {
            color: #1e293b;
        }

        .purchase-tab-btn.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }

        /* Filter Toolbar */
        .purchase-filter-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .purchase-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            align-items: flex-end;
        }

        .purchase-filter-field label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .purchase-filter-field input,
        .purchase-filter-field select {
            width: 100%;
            height: 43px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            padding: 0 12px;
            font-size: 12.5px;
            font-weight: 500;
            color: #334155;
            background: #ffffff;
            outline: none;
            transition: all 0.15s ease;
            box-sizing: border-box;
        }

        .purchase-filter-field input:focus,
        .purchase-filter-field select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
        }

        .purchase-filter-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Date Picker styling */
        .filter-date-input {
            position: relative;
            cursor: pointer;
            width: 100%;
        }

        .filter-date-input input {
            padding-right: 36px !important;
            cursor: pointer;
        }

        .filter-date-input i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #64748b;
            pointer-events: none;
        }

        /* Buttons */
        .btn-action-primary {
            height: 43px;
            padding: 0 18px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease;
        }

        .btn-action-primary:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-action-outline {
            height: 43px;
            padding: 0 16px;
            background: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-action-outline:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .btn-excel-export {
            height: 43px;
            padding: 0 16px;
            background: #10b981;
            color: #ffffff;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-excel-export:hover:not(:disabled) {
            background: #059669;
        }

        .btn-excel-export:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Table Card */
        .purchase-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .purchase-table-container {
            width: 100%;
            overflow-x: auto;
        }

        .purchase-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            text-align: left;
        }

        .purchase-table thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .purchase-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .purchase-table tbody tr:hover {
            background: #f8fafc;
        }

        .purchase-po-cell {
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .purchase-po-cell:hover {
            text-decoration: underline;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-badge-draft { background: #f1f5f9; color: #475569; }
        .status-badge-ordered { background: #e0e7ff; color: #4338ca; }
        .status-badge-partial-received { background: #fef3c7; color: #b45309; }
        .status-badge-received { background: #dcfce7; color: #15803d; }
        .status-badge-cancelled { background: #fee2e2; color: #b91c1c; }

        .payment-badge-unpaid { background: #fee2e2; color: #b91c1c; }
        .payment-badge-partial { background: #fef3c7; color: #b45309; }
        .payment-badge-paid { background: #dcfce7; color: #15803d; }

        /* Actions Cell */
        .table-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-table-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-table-icon:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .btn-table-icon.text-primary:hover { background: #eff6ff; color: #2563eb; }
        .btn-table-icon.text-success:hover { background: #ecfdf5; color: #059669; }
        .btn-table-icon.text-danger:hover { background: #fee2e2; color: #dc2626; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 14px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            animation: modalPop 0.2s ease-out;
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            padding: 18px 22px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .modal-body {
            padding: 22px;
            max-height: 75vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 16px 22px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>

    <div class="purchase-listing-wrapper" x-data="xPurchaseApp">
        {{-- KPI Summary Cards --}}
        <div class="purchase-kpi-grid">
            <div class="purchase-kpi-card">
                <div class="purchase-kpi-icon blue">
                    <i class='bx bx-cart-download'></i>
                </div>
                <div class="purchase-kpi-info">
                    <h4>{{ __('purchase.summary.total_purchases') }}</h4>
                    <div class="val">{{ number_format($totalPurchases ?? 0) }}</div>
                </div>
            </div>

            <div class="purchase-kpi-card">
                <div class="purchase-kpi-icon indigo">
                    <i class='bx bx-receipt'></i>
                </div>
                <div class="purchase-kpi-info">
                    <h4>{{ __('purchase.summary.total_volume') }}</h4>
                    <div class="val">$ {{ number_format($totalVolume ?? 0, 2) }}</div>
                </div>
            </div>

            <div class="purchase-kpi-card">
                <div class="purchase-kpi-icon emerald">
                    <i class='bx bx-check-shield'></i>
                </div>
                <div class="purchase-kpi-info">
                    <h4>{{ __('purchase.summary.total_paid') }}</h4>
                    <div class="val">$ {{ number_format($totalPaid ?? 0, 2) }}</div>
                </div>
            </div>

            <div class="purchase-kpi-card">
                <div class="purchase-kpi-icon amber">
                    <i class='bx bx-time-five'></i>
                </div>
                <div class="purchase-kpi-info">
                    <h4>{{ __('purchase.summary.total_outstanding') }}</h4>
                    <div class="val">$ {{ number_format($totalOutstanding ?? 0, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Status Tabs --}}
        <div class="purchase-tabs-nav">
            @php
                $tabs = [
                    'all'              => __('purchase.status.all'),
                    'draft'            => __('purchase.status.draft'),
                    'ordered'          => __('purchase.status.ordered'),
                    'partial_received' => __('purchase.status.partial_received'),
                    'received'         => __('purchase.status.received'),
                    'cancelled'        => __('purchase.status.cancelled'),
                    'trash'            => __('purchase.status.trash'),
                ];
            @endphp

            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin-purchase-list', array_merge(['status' => $key], request()->except('status', 'page'))) }}"
                   class="purchase-tab-btn {{ $status == $key ? 'active' : '' }}">
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </div>

        {{-- Filter Card --}}
        <div class="purchase-filter-card">
            <form id="purchaseFilterForm" method="GET" action="{{ route('admin-purchase-list', $status) }}">
                <div class="purchase-filter-grid">
                    <div class="purchase-filter-field">
                        <label>{{ __('purchase.filter.keyword_search', ['default' => 'Search']) }}</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="{{ __('purchase.table.purchase_number') }}, {{ __('purchase.table.supplier') }}...">
                    </div>

                    <div class="purchase-filter-field">
                        <label>{{ __('purchase.table.supplier') }}</label>
                        <div class="select2Group">
                            <select name="supplier_id" id="filter_supplier_id" class="SelectField select2">
                                <option value="">{{ __('purchase.all_suppliers') }}</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ ($filters['supplier_id'] ?? '') == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="select2Reset" x-show="supplierFilter" @click.stop="resetSupplierFilter()" style="display: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="purchase-filter-field">
                        <label>{{ __('purchase.table.shop') }}</label>
                        <div class="select2Group">
                            <select name="shop_id" id="filter_shop_id" class="SelectField select2">
                                <option value="">{{ __('purchase.all_shops') }}</option>
                                @foreach ($shops as $shp)
                                    <option value="{{ $shp->id }}" {{ ($filters['shop_id'] ?? '') == $shp->id ? 'selected' : '' }}>
                                        {{ $shp->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="select2Reset" x-show="shopFilter" @click.stop="resetShopFilter()" style="display: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="purchase-filter-field">
                        <label>{{ __('purchase.table.payment_status') }}</label>
                        <select name="payment_status">
                            <option value="all">{{ __('purchase.payment_status.all') }}</option>
                            <option value="unpaid" {{ ($filters['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' }}>{{ __('purchase.payment_status.unpaid') }}</option>
                            <option value="partial" {{ ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' }}>{{ __('purchase.payment_status.partial') }}</option>
                            <option value="paid" {{ ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' }}>{{ __('purchase.payment_status.paid') }}</option>
                        </select>
                    </div>

                    <div class="purchase-filter-field">
                        <label for="purchase-filter-from-date">{{ __('purchase.table.date') }} (From)</label>
                        <div class="filter-date-input" @click="$refs.fromDate.focus()">
                            <input type="text" name="from_date" id="purchase-filter-from-date"
                                   x-ref="fromDate" class="filter-input" value="{{ $filters['from_date'] ?? '' }}"
                                   autocomplete="off" placeholder="YYYY-MM-DD">
                            <i class='bx bx-calendar' aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="purchase-filter-field">
                        <label for="purchase-filter-to-date">{{ __('purchase.table.date') }} (To)</label>
                        <div class="filter-date-input" @click="$refs.toDate.focus()">
                            <input type="text" name="to_date" id="purchase-filter-to-date"
                                   x-ref="toDate" class="filter-input" value="{{ $filters['to_date'] ?? '' }}"
                                   autocomplete="off" placeholder="YYYY-MM-DD">
                            <i class='bx bx-calendar' aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <div class="purchase-filter-actions" style="margin-top: 14px;">
                    <button type="submit" class="btn-action-primary">
                        <i class='bx bx-filter-alt'></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('admin-purchase-list', $status) }}" class="btn-action-outline">
                        <i class='bx bx-reset'></i>
                        <span>Reset</span>
                    </a>

                    @can('purchase-excel')
                        <button type="button" @click="excel()" class="btn-excel-export" :disabled="exportLoading">
                            <i class='bx bx-download' x-show="!exportLoading"></i>
                            <i class='bx bx-loader-alt bx-spin' x-show="exportLoading" style="display: none;"></i>
                            <span x-text="exportLoading ? 'Exporting...' : 'Export Excel'">Export Excel</span>
                        </button>
                    @endcan

                    <div style="flex: 1;"></div>

                    @can('purchase-create')
                        <a href="{{ route('admin-purchase-create') }}" class="btn-action-primary" style="background: #059669;">
                            <i class='bx bx-plus'></i>
                            <span>{{ __('purchase.button.create') }}</span>
                        </a>
                    @endcan
                </div>
            </form>
        </div>

        {{-- Table Card --}}
        <div class="purchase-table-card">
            <div class="purchase-table-container">
                <table class="purchase-table">
                    <thead>
                        <tr>
                            <th>{{ __('purchase.table.no') }}</th>
                            <th>{{ __('purchase.table.purchase_number') }}</th>
                            <th>{{ __('purchase.table.supplier_invoice_no') }}</th>
                            <th>{{ __('purchase.table.supplier') }}</th>
                            <th>{{ __('purchase.table.shop') }}</th>
                            <th>{{ __('purchase.table.date') }}</th>
                            <th>{{ __('purchase.table.due_date') }}</th>
                            <th>{{ __('purchase.table.total_amount') }}</th>
                            <th>{{ __('purchase.table.paid_amount') }}</th>
                            <th>{{ __('purchase.table.remaining_amount') }}</th>
                            <th>{{ __('purchase.table.status') }}</th>
                            <th>{{ __('purchase.table.payment_status') }}</th>
                            <th style="text-align: right;">{{ __('purchase.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $item)
                            <tr>
                                <td>{{ $data->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('admin-purchase-view', $item->id) }}" class="purchase-po-cell">
                                        {{ $item->purchase_number }}
                                    </a>
                                </td>
                                <td>{{ $item->supplier_invoice_no ?? '---' }}</td>
                                <td><strong>{{ $item->supplier?->name ?? '---' }}</strong></td>
                                <td>{{ $item->shop?->name ?? '---' }}</td>
                                <td>{{ $item->purchase_date ? $item->purchase_date->format('Y-m-d') : '---' }}</td>
                                <td>{{ $item->payment_due_date ? $item->payment_due_date->format('Y-m-d') : '---' }}</td>
                                <td><strong>{{ $item->formatted_total }}</strong></td>
                                <td><span style="color: #059669;">{{ $item->formatted_paid }}</span></td>
                                <td>
                                    @if ($item->remaining_amount > 0)
                                        <strong style="color: #dc2626;">{{ $item->formatted_remaining }}</strong>
                                    @else
                                        <span style="color: #64748b;">$ 0.00</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item->status_badge['class'] }}">
                                        {{ $item->status_badge['label'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $item->payment_status_badge['class'] }}">
                                        {{ $item->payment_status_badge['label'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        @can('purchase-view')
                                            <a href="{{ route('admin-purchase-view', $item->id) }}" class="btn-table-icon text-primary" title="View Details">
                                                <i class='bx bx-show'></i>
                                            </a>
                                        @endcan

                                        @if ($status !== 'trash')
                                            @can('purchase-update')
                                                @if ($item->status !== 'received' && $item->status !== 'cancelled')
                                                    <a href="{{ route('admin-purchase-edit', $item->id) }}" class="btn-table-icon" title="Edit Purchase">
                                                        <i class='bx bx-edit-alt'></i>
                                                    </a>
                                                @endif
                                            @endcan

                                            @can('purchase-receive')
                                                @if (in_array($item->status, ['draft', 'ordered', 'partial_received'], true))
                                                    <button type="button" @click="openReceiveModal({{ $item->id }}, '{{ $item->purchase_number }}')" class="btn-table-icon text-success" title="Receive Goods">
                                                        <i class='bx bx-package'></i>
                                                    </button>
                                                @endif
                                            @endcan

                                            @can('purchase-payment')
                                                @if ($item->remaining_amount > 0 && $item->status !== 'cancelled')
                                                    <button type="button" @click="openPaymentModal({{ $item->id }}, '{{ $item->purchase_number }}', {{ $item->remaining_amount }})" class="btn-table-icon text-primary" title="Record Payment">
                                                        <i class='bx bx-credit-card'></i>
                                                    </button>
                                                @endif
                                            @endcan

                                            @can('purchase-print')
                                                <a href="{{ route('admin-purchase-print', $item->id) }}" target="_blank" class="btn-table-icon" title="Print PO">
                                                    <i class='bx bx-printer'></i>
                                                </a>
                                            @endcan

                                            @can('purchase-delete')
                                                @if ($item->status !== 'received')
                                                    <form action="{{ route('admin-purchase-delete', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this purchase order?');" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn-table-icon text-danger" title="Delete">
                                                            <i class='bx bx-trash'></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        @else
                                            @can('purchase-restore')
                                                <form action="{{ route('admin-purchase-restore', $item->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn-table-icon text-success" title="Restore">
                                                        <i class='bx bx-revision'></i>
                                                    </button>
                                                </form>
                                            @endcan
                                            @can('purchase-force-delete')
                                                <form action="{{ route('admin-purchase-destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this record?');" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn-table-icon text-danger" title="Permanently Destroy">
                                                        <i class='bx bx-x-circle'></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" style="text-align: center; padding: 32px; color: #94a3b8;">
                                    <i class='bx bx-inbox' style="font-size: 36px; display: block; margin-bottom: 8px;"></i>
                                    <span>No purchase orders found matching your criteria.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($data->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0;">
                    {{ $data->links() }}
                </div>
            @endif
        </div>

        {{-- Record Payment Modal --}}
        <div class="modal-overlay" x-show="showPaymentModal" style="display: none;" @click.self="showPaymentModal = false">
            <div class="modal-card">
                <form :action="paymentActionUrl" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h3>Record Supplier Payment (<span x-text="activePoNumber"></span>)</h3>
                        <button type="button" @click="showPaymentModal = false" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div style="background: #f8fafc; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; display: flex; justify-content: space-between;">
                            <span style="color: #64748b; font-size: 12px; font-weight: 600;">REMAINING DUE:</span>
                            <strong style="color: #dc2626; font-size: 14px;" x-text="'$ ' + Number(activeRemaining).toFixed(2)"></strong>
                        </div>

                        <div class="purchase-filter-field" style="margin-bottom: 14px;">
                            <label>Payment Amount ($) <span style="color: #dc2626;">*</span></label>
                            <input type="number" step="0.01" min="0.01" :max="activeRemaining" name="amount" x-model="paymentAmount" required>
                        </div>

                        <div class="purchase-filter-field" style="margin-bottom: 14px;">
                            <label>Payment Date <span style="color: #dc2626;">*</span></label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="purchase-filter-field" style="margin-bottom: 14px;">
                            <label>Payment Method <span style="color: #dc2626;">*</span></label>
                            <select name="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="ABA Pay">ABA Pay</option>
                                <option value="Wing">Wing</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="purchase-filter-field" style="margin-bottom: 14px;">
                            <label>Reference No / Slip #</label>
                            <input type="text" name="reference_no" placeholder="Bank transaction ID or Cheque #">
                        </div>

                        <div class="purchase-filter-field" style="margin-bottom: 14px;">
                            <label>Attachment Slip (Image/PDF)</label>
                            <input type="file" name="receipt" accept="image/*,.pdf" style="padding: 7px 12px;">
                        </div>

                        <div class="purchase-filter-field">
                            <label>Note / Remarks</label>
                            <textarea name="note" rows="2" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 7px; padding: 8px 12px; font-size: 12.5px; outline: none;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showPaymentModal = false" class="btn-action-outline">Cancel</button>
                        <button type="submit" class="btn-action-primary">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Goods Receive Modal --}}
        <div class="modal-overlay" x-show="showReceiveModal" style="display: none;" @click.self="showReceiveModal = false">
            <div class="modal-card">
                <form :action="receiveActionUrl" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h3>Confirm Goods Receiving (<span x-text="activePoNumber"></span>)</h3>
                        <button type="button" @click="showReceiveModal = false" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p style="font-size: 13.5px; color: #334155; line-height: 1.5; margin: 0 0 16px 0;">
                            Are you sure you want to mark this purchase order as received?
                            All ordered quantities will be atomically added to the shop's <strong>Stock On Hand</strong> and recorded in stock movement history.
                        </p>
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 14px; font-size: 12px; color: #1e40af;">
                            <i class='bx bx-info-circle'></i> If you wish to receive partial quantities, please open the purchase details view.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showReceiveModal = false" class="btn-action-outline">Cancel</button>
                        <button type="submit" class="btn-action-primary" style="background: #059669;">
                            <i class='bx bx-check'></i>
                            <span>Confirm Receipt</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Full Screen Loading Spinner Overlay for Excel Export --}}
        <template x-if="exportLoading">
            <div class="loadingFullSizeLayout">
                <div class="loading loadingSubmit">
                    <span id="spinner"></span>
                    <label>Exporting Purchases to Excel...</label>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    @include('admin::pages.report.partials.excel-assets')
    <script>
        (function() {
            const registerXPurchaseApp = () => {
                Alpine.data('xPurchaseApp', () => ({
                    exportLoading: false,
                    supplierFilter: '{{ $filters['supplier_id'] ?? '' }}',
                    shopFilter: '{{ $filters['shop_id'] ?? '' }}',

                    // Modal state
                    showPaymentModal: false,
                    showReceiveModal: false,
                    activePoId: null,
                    activePoNumber: '',
                    activeRemaining: 0,
                    paymentAmount: 0,
                    paymentActionUrl: '',
                    receiveActionUrl: '',

                    init() {
                        this.$nextTick(() => {
                            this.initDatepickers();
                            this.initSelect2();
                        });
                    },

                    initDatepickers() {
                        if (!window.jQuery || !$.fn.datepicker) return;

                        const options = {
                            dateFormat: 'yy-mm-dd',
                            changeYear: true,
                            changeMonth: true,
                            gotoCurrent: true,
                            yearRange: '-50:+10',
                            beforeShow: function(input, instance) {
                                instance.dpDiv.addClass('sales-report-datepicker');
                            },
                            onClose: function(dateText, instance) {
                                instance.dpDiv.removeClass('sales-report-datepicker');
                            },
                        };

                        const $from = $(this.$refs.fromDate);
                        const $to = $(this.$refs.toDate);

                        if ($from.length) {
                            $from.datepicker({
                                ...options,
                                onSelect: function(selected) {
                                    $to.datepicker('option', 'minDate', selected);
                                }
                            }).on('change', function() {
                                $to.datepicker('option', 'minDate', $from.datepicker('getDate'));
                            });
                        }

                        if ($to.length) {
                            $to.datepicker({
                                ...options,
                                onSelect: function(selected) {
                                    $from.datepicker('option', 'maxDate', selected);
                                }
                            }).on('change', function() {
                                $from.datepicker('option', 'maxDate', $to.datepicker('getDate'));
                            });
                        }

                        if ($from.val()) $to.datepicker('option', 'minDate', $from.datepicker('getDate'));
                        if ($to.val()) $from.datepicker('option', 'maxDate', $to.datepicker('getDate'));
                    },

                    initSelect2() {
                        if (!window.jQuery || !$.fn.select2) return;

                        const self = this;
                        const $supplier = $('#filter_supplier_id');
                        const $shop = $('#filter_shop_id');

                        $supplier.select2({
                            placeholder: '{{ __('purchase.all_suppliers') }}',
                            width: '100%'
                        }).on('select2:open', function () {
                            if (typeof window.$select2FocusInputSearch === 'function') {
                                window.$select2FocusInputSearch();
                            }
                        }).on('change', function() {
                            self.supplierFilter = $(this).val();
                        });

                        $shop.select2({
                            placeholder: '{{ __('purchase.all_shops') }}',
                            width: '100%'
                        }).on('select2:open', function () {
                            if (typeof window.$select2FocusInputSearch === 'function') {
                                window.$select2FocusInputSearch();
                            }
                        }).on('change', function() {
                            self.shopFilter = $(this).val();
                        });
                    },

                    resetSupplierFilter() {
                        $('#filter_supplier_id').val('').trigger('change');
                        this.supplierFilter = '';
                    },

                    resetShopFilter() {
                        $('#filter_shop_id').val('').trigger('change');
                        this.shopFilter = '';
                    },

                    openPaymentModal(id, poNumber, remaining) {
                        this.activePoId = id;
                        this.activePoNumber = poNumber;
                        this.activeRemaining = remaining;
                        this.paymentAmount = remaining;
                        this.paymentActionUrl = '{{ url("admin/purchase") }}/' + id + '/payment';
                        this.showPaymentModal = true;
                    },

                    openReceiveModal(id, poNumber) {
                        this.activePoId = id;
                        this.activePoNumber = poNumber;
                        this.receiveActionUrl = '{{ url("admin/purchase") }}/' + id + '/receive';
                        this.showReceiveModal = true;
                    },

                    getExportParams() {
                        const params = new URLSearchParams(window.location.search);

                        if (window.MDI && Array.isArray(window.MDI.tabs) && window.MDI.activeTabKey) {
                            const activeTab = window.MDI.tabs.find(t => t.key === window.MDI.activeTabKey);
                            if (activeTab && activeTab.url && activeTab.url.includes('?')) {
                                const tabParams = new URLSearchParams(activeTab.url.split('?')[1]);
                                tabParams.forEach((value, key) => params.set(key, value));
                            }
                        }

                        const form = document.getElementById('purchaseFilterForm');
                        if (form) {
                            const formData = new FormData(form);
                            formData.forEach((value, key) => {
                                if (value !== null && value !== '') {
                                    params.set(key, value);
                                }
                            });
                        }

                        params.delete('page');
                        return params;
                    },

                    async excel() {
                        if (this.exportLoading) return;
                        this.exportLoading = true;

                        try {
                            const params = this.getExportParams();
                            const url = '{{ route("admin-purchase-report") }}?' + params.toString();
                            const response = await axios.get(url);
                            const data = response.data;

                            if (data && data.status === 'success' && Array.isArray(data.rows)) {
                                await this.runExcelExport(data.rows);
                            } else {
                                alert('No purchase records found to export.');
                            }
                        } catch (e) {
                            console.error('Export error:', e);
                            alert('An error occurred while generating the Excel file.');
                        } finally {
                            this.exportLoading = false;
                        }
                    },

                    async runExcelExport(rows) {
                        const workbook = new ExcelJS.Workbook();
                        workbook.creator = 'POS System';
                        workbook.created = new Date();

                        const worksheet = workbook.addWorksheet('Purchases', {
                            views: [{ showGridLines: true }]
                        });

                        worksheet.columns = [
                            { header: 'Nº', key: 'no', width: 8 },
                            { header: 'PO Number', key: 'purchase_number', width: 22 },
                            { header: 'Supplier Invoice #', key: 'supplier_invoice_no', width: 22 },
                            { header: 'Supplier', key: 'supplier', width: 26 },
                            { header: 'Shop', key: 'shop', width: 22 },
                            { header: 'Date', key: 'date', width: 14 },
                            { header: 'Due Date', key: 'due_date', width: 14 },
                            { header: 'Subtotal ($)', key: 'subtotal', width: 16 },
                            { header: 'Discount ($)', key: 'discount', width: 14 },
                            { header: 'Tax ($)', key: 'tax', width: 14 },
                            { header: 'Shipping ($)', key: 'shipping', width: 14 },
                            { header: 'Total ($)', key: 'total', width: 18 },
                            { header: 'Paid ($)', key: 'paid', width: 16 },
                            { header: 'Balance ($)', key: 'balance', width: 16 },
                            { header: 'Status', key: 'status', width: 16 },
                            { header: 'Payment Status', key: 'payment_status', width: 16 },
                        ];

                        const headerRow = worksheet.getRow(1);
                        headerRow.height = 28;
                        headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
                        headerRow.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'FF1E293B' }
                        };
                        headerRow.alignment = { vertical: 'middle', horizontal: 'center' };

                        let totalVolume = 0;
                        let totalPaid = 0;
                        let totalBalance = 0;

                        rows.forEach((r, idx) => {
                            const tot = Number(r.total_amount || 0);
                            const pd = Number(r.paid_amount || 0);
                            const rem = Math.max(0, tot - pd);

                            totalVolume += tot;
                            totalPaid += pd;
                            totalBalance += rem;

                            const row = worksheet.addRow({
                                no: idx + 1,
                                purchase_number: r.purchase_number || '',
                                supplier_invoice_no: r.supplier_invoice_no || '',
                                supplier: r.supplier?.name || '',
                                shop: r.shop?.name || '',
                                date: r.purchase_date ? r.purchase_date.substring(0, 10) : '',
                                due_date: r.payment_due_date ? r.payment_due_date.substring(0, 10) : '',
                                subtotal: Number(r.subtotal || 0),
                                discount: Number(r.discount_amount || 0),
                                tax: Number(r.tax_amount || 0),
                                shipping: Number(r.shipping_cost || 0),
                                total: tot,
                                paid: pd,
                                balance: rem,
                                status: (r.status || '').toUpperCase(),
                                payment_status: (r.payment_status || '').toUpperCase(),
                            });

                            row.height = 22;
                            row.alignment = { vertical: 'middle' };

                            ['subtotal', 'discount', 'tax', 'shipping', 'total', 'paid', 'balance'].forEach(col => {
                                row.getCell(col).numFmt = '$#,##0.00';
                            });
                        });

                        // Summary row
                        const totalRow = worksheet.addRow({
                            no: '',
                            purchase_number: 'TOTAL',
                            total: totalVolume,
                            paid: totalPaid,
                            balance: totalBalance,
                        });
                        totalRow.height = 26;
                        totalRow.font = { bold: true };
                        totalRow.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'FFF1F5F9' }
                        };
                        ['total', 'paid', 'balance'].forEach(col => {
                            totalRow.getCell(col).numFmt = '$#,##0.00';
                        });

                        const buffer = await workbook.xlsx.writeBuffer();
                        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                        saveAs(blob, 'Purchases_Export_' + new Date().toISOString().slice(0, 10) + '.xlsx');
                    }
                }));
            };

            if (window.Alpine) {
                registerXPurchaseApp();
            } else {
                document.addEventListener('alpine:init', registerXPurchaseApp);
            }
        })();
    </script>
@stop
