<div class="table">
    @if ($data->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                <div class="row table-row-5">
                    <span>{{ __('global.table.no') }}</span>
                </div>
                <div class="row table-row-15 textLeft">
                    <span>{{ __('stock_in.table.po_number') }}</span>
                </div>
                <div class="row table-row-15 textLeft">
                    <span>{{ __('stock_in.table.supplier') }}</span>
                </div>
                <div class="row table-row-12 textLeft">
                    <span>{{ __('stock_in.table.shop') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_in.table.date') }}</span>
                </div>
                <div class="row table-row-10 textRight">
                    <span>{{ __('stock_in.table.total_amount') }}</span>
                </div>
                <div class="row table-row-10 textRight">
                    <span>{{ __('stock_in.table.paid_amount') }}</span>
                </div>
                <div class="row table-row-10 textRight">
                    <span>{{ __('stock_in.table.remaining_amount') }}</span>
                </div>
                <div class="row table-row-12">
                    <span>{{ __('stock_in.table.order_status') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_in.table.payment_status') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_in.table.action') }}</span>
                </div>
            </div>
            <div class="table-body">
                @foreach ($data as $index => $item)
                    <div class="column">
                        <div class="row table-row-5">
                            <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                        </div>
                        <div class="row table-row-15 textLeft">
                            <a href="{!! route('admin-' . $routeName . '-view', $item->id) !!}" class="po-number-link">
                                <strong>{{ $item->purchase_number }}</strong>
                            </a>
                            @if ($item->reference_no)
                                <div class="table-subtext">Ref: {{ $item->reference_no }}</div>
                            @endif
                        </div>
                        <div class="row table-row-15 textLeft">
                            <span>{{ $item->supplier_title }}</span>
                        </div>
                        <div class="row table-row-12 textLeft">
                            <span>{{ $item->shop_title }}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{{ $item->created_date }}</span>
                        </div>
                        <div class="row table-row-10 textRight font-bold text-slate-700">
                            <span>{{ $item->formatted_total_amount }}</span>
                        </div>
                        <div class="row table-row-10 textRight font-semibold text-emerald-600">
                            <span>{{ $item->formatted_paid_amount }}</span>
                        </div>
                        <div class="row table-row-10 textRight font-semibold {{ $item->remaining_amount > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                            <span>{{ $item->formatted_remaining_amount }}</span>
                        </div>
                        <div class="row table-row-12">
                            @php
                                $statusClass = match($item->order_status) {
                                    'draft' => 'badge-draft',
                                    'ordered' => 'badge-ordered',
                                    'partial_received' => 'badge-partial',
                                    'received' => 'badge-received',
                                    'cancelled' => 'badge-cancelled',
                                    default => 'badge-default',
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $item->order_status_title }}
                            </span>
                        </div>
                        <div class="row table-row-10">
                            @php
                                $payClass = match($item->payment_status) {
                                    'unpaid' => 'badge-unpaid',
                                    'partial' => 'badge-partial',
                                    'paid' => 'badge-paid',
                                    default => 'badge-default',
                                };
                            @endphp
                            <span class="status-badge {{ $payClass }}">
                                {{ $item->payment_status_title }}
                            </span>
                        </div>
                        <div class="row table-row-10 action-cell">
                            <div class="action-buttons-wrap">
                                <a href="{!! route('admin-' . $routeName . '-view', $item->id) !!}" class="btn-action-icon" title="{{ __('global.action.view') }}">
                                    <i class='bx bx-show'></i>
                                </a>
                                @if (!$item->trashed())
                                    <a href="{!! route('admin-' . $routeName . '-edit', $item->id) !!}" class="btn-action-icon" title="{{ __('global.action.edit') }}">
                                        <i class='bx bx-edit-alt'></i>
                                    </a>
                                    <a href="{!! route('admin-' . $routeName . '-print', $item->id) !!}" target="_blank" class="btn-action-icon" title="{{ __('stock_in.button.print') }}">
                                        <i class='bx bx-printer'></i>
                                    </a>
                                    <button type="button" class="btn-action-icon text-danger" title="{{ __('global.action.delete') }}"
                                        @click="verifyDelete({{ $item->id }}, '{{ $item->purchase_number }}')">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                @else
                                    <button type="button" class="btn-action-icon text-success" title="{{ __('global.action.restore') }}"
                                        @click="verifyRestore({{ $item->id }}, '{{ $item->purchase_number }}')">
                                        <i class='bx bx-refresh'></i>
                                    </button>
                                    <button type="button" class="btn-action-icon text-danger" title="{{ __('global.action.destroy') }}"
                                        @click="verifyDestroy({{ $item->id }}, '{{ $item->purchase_number }}')">
                                        <i class='bx bx-x-circle'></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="table-footer">
                @include('admin::components.pagination', ['paginate' => $data])
            </div>
        </div>
    @else
        @component('admin::components.empty', [
            'name' => __('stock_in.empty.title'),
            'msg' => __('stock_in.empty.description'),
            'permission' => 'stock-in-create',
            'url' => route('admin-stock-in-create'),
            'button' => __('stock_in.button.create'),
        ])
        @endcomponent
    @endif
</div>
