@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('other_income.title') . ' — ' . $supplier->name])
    <div class="content-wrapper" id="app">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center font-weight-bold fs-4" style="width:48px; height:48px;">
                        <i class='bx bx-store'></i>
                    </div>
                    <div>
                        <h5 class="mb-0 font-weight-bold">{{ $supplier->name }}</h5>
                        <small class="text-muted">Supplier ID: #{{ $supplier->id }}</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin-other-income-create') }}?supplier_id={{ $supplier->id }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bx bx-plus"></i> {{ __('other_income.button.create') }}
                    </a>
                    <a href="{{ route('admin-other-income-list', 1) }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bx bx-arrow-back"></i> {{ __('other_income.name') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                {{-- KPI summary row --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="border rounded p-2 text-center bg-light">
                            <small class="text-muted">{{ __('other_income.report.total_transactions') }}</small>
                            <h5 class="mb-0 text-primary font-weight-bold">{{ $incomes->total() ?? $incomes->count() }}</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-2 text-center bg-light">
                            <small class="text-muted">{{ __('other_income.table.amount') }}</small>
                            <h5 class="mb-0 text-success font-weight-bold">${{ number_format($summary['total_received'] ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>

                {{-- Ledger Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('other_income.table.income_number') }}</th>
                                <th>{{ __('other_income.table.income_date') }}</th>
                                <th>{{ __('other_income.table.title') }}</th>
                                <th>{{ __('other_income.table.category') }}</th>
                                <th>{{ __('other_income.table.amount') }}</th>
                                <th>{{ __('other_income.table.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($incomes as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><code>{{ $item->income_number }}</code></td>
                                    <td>{{ $item->formatted_date }}</td>
                                    <td><strong>{{ $item->title }}</strong></td>
                                    <td>{{ $item->category_name }}</td>
                                    <td class="text-success font-weight-bold">${{ number_format((float) $item->received_amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('admin-other-income-edit', $item->id) }}" class="btn btn-sm btn-icon btn-light-primary" title="{{ __('global.action.edit') }}">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No records found for this supplier.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $incomes->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
