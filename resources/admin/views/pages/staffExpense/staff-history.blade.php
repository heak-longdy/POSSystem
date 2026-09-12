@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('staff_expense.history.title')])
    <div class="content-wrapper" id="app">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    @if ($staff->image_url)
                        <img src="{{ $staff->image_url }}" class="rounded-circle" width="48" height="48" alt="">
                    @else
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center font-weight-bold fs-4" style="width:48px; height:48px;">
                            {{ strtoupper(substr($staff->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h5 class="mb-0 font-weight-bold">{{ $staff->name }}</h5>
                        <small class="text-muted">
                            {{ $staff->phone_number ? __('staff_expense.history.phone') . ': ' . $staff->phone_number : '' }}
                            {{ $staff->position ? '| ' . __('staff_expense.history.position') . ': ' . $staff->position->title : '' }}
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin-staff-expense-create') }}?staff_id={{ $staff->id }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bx bx-plus"></i> {{ __('staff_expense.button.record_expense') }}
                    </a>
                    <a href="{{ route('admin-staff-expense-list', 1) }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <i class="bx bx-arrow-back"></i> {{ __('staff_expense.history.all_staff_expenses') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('admin-staff-expense-staff-history', $staff->id) }}" class="row g-2 mb-4 align-items-end">
                    <div class="col-md-3 col-6">
                        <label class="form-label font-weight-bold">{{ __('staff_expense.filter.from_date') }}</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label font-weight-bold">{{ __('staff_expense.filter.to_date') }}</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label font-weight-bold">{{ __('staff_expense.filter.expense_type') }}</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">{{ __('staff_expense.filter.all_types') }}</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->value }}" {{ ($filters['type'] ?? '') == $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-6 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-50 d-flex justify-content-center align-items-center gap-1">
                            <i class="bx bx-filter-alt"></i> {{ __('staff_expense.button.filter') }}
                        </button>
                        <a href="{{ route('admin-staff-expense-staff-history', $staff->id) }}" class="btn btn-sm btn-light w-50 d-flex justify-content-center align-items-center gap-1">
                            <i class="bx bx-refresh"></i> {{ __('staff_expense.button.reset') }}
                        </a>
                    </div>
                </form>

                <!-- Per-Staff Metric Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card border border-primary border-opacity-25 bg-light-primary text-primary mb-0 p-3">
                            <span class="text-uppercase font-weight-bold small">{{ __('staff_expense.summary.base_salary') }}</span>
                            <h4 class="mb-0 mt-1 font-weight-bold">${{ number_format($summary['salary'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border border-success border-opacity-25 bg-light-success text-success mb-0 p-3">
                            <span class="text-uppercase font-weight-bold small">{{ __('staff_expense.summary.bonus_rewards') }}</span>
                            <h4 class="mb-0 mt-1 font-weight-bold">${{ number_format($summary['bonus'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border border-danger border-opacity-25 bg-light-danger text-danger mb-0 p-3">
                            <span class="text-uppercase font-weight-bold small">{{ __('staff_expense.summary.deductions') }}</span>
                            <h4 class="mb-0 mt-1 font-weight-bold">${{ number_format($summary['deduction'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border border-info border-opacity-25 bg-light-info text-info mb-0 p-3">
                            <span class="text-uppercase font-weight-bold small">{{ __('staff_expense.summary.net_total_payable') }}</span>
                            <h4 class="mb-0 mt-1 font-weight-bold">${{ number_format($summary['netTotal'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>

                <!-- Expense History Datatable -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">{{ __('staff_expense.table.no') }}</th>
                                <th>{{ __('staff_expense.table.shop') }}</th>
                                <th>{{ __('staff_expense.table.type') }}</th>
                                <th>{{ __('staff_expense.table.amount_usd') }}</th>
                                <th>{{ __('staff_expense.table.expense_date') }}</th>
                                <th>{{ __('staff_expense.table.description') }}</th>
                                <th>{{ __('staff_expense.table.recorded_by') }}</th>
                                <th style="width: 120px;" class="text-center">{{ __('global.table.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expenses as $index => $item)
                                <tr>
                                    <td>{{ $expenses->firstItem() + $index }}</td>
                                    <td>{{ $item->shop->name ?? __('staff_expense.all_shops') }}</td>
                                    <td>
                                        <span class="{{ $item->type ? $item->type->badgeClass() : 'badge bg-secondary' }}">
                                            {{ $item->type ? $item->type->label() : '--' }}
                                        </span>
                                    </td>
                                    <td class="font-weight-bold {{ $item->type && $item->type->isDeduction() ? 'text-danger' : 'text-success' }}">
                                        {{ $item->type && $item->type->isDeduction() ? '-' : '+' }}${{ number_format($item->amount, 2) }}
                                    </td>
                                    <td>{{ $item->expense_date ? $item->expense_date->format('Y-m-d') : '--' }}</td>
                                    <td>{{ $item->description ?? '--' }}</td>
                                    <td><small class="text-muted">{{ $item->createdBy->name ?? '--' }}</small></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin-staff-expense-edit', $item->id) }}" class="btn btn-sm btn-icon btn-light-primary" title="{{ __('global.action.edit') }}">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin-staff-expense-delete', $item->id) }}" onsubmit="return confirm('{{ __('staff_expense.history.confirm_delete') }}');" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-icon btn-light-danger" title="{{ __('global.action.delete') }}">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        {{ __('staff_expense.history.no_records', ['name' => $staff->name]) }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    {{ $expenses->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
