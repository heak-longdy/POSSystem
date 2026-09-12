<div class="table">
    @if ($data->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                <div class="row table-row-10">
                    <span>{{ __('global.table.no') }}</span>
                </div>
                <div class="row table-row-10 textLeft">
                    <span>{{ __('stock_movement.table.product') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_movement.table.category') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_movement.table.uom') }}</span>
                </div>
                {{-- <div class="row table-row-10">
                    <span>{{ __('stock_movement.table.current_stock') }}</span>
                </div> --}}
                <div class="row table-row-10">
                    <span>{{ __('stock_movement.table.qty') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_movement.table.status') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_movement.table.from') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_movement.table.to') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_movement.table.date') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_movement.table.requested_by') }}</span>
                </div>
            </div>
            <div class="table-body">
                @foreach ($data as $index => $item)
                    <div class="column">
                        <div class="row table-row-10">
                            <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                        </div>
                        <div class="row table-row-10 previewImageText textLeft">
                            <span class="textView">{!! isset($item->product->name) ? $item->product->name : '--' !!}</span>
                        </div>

                        <div class="row table-row-10">
                            <span>{!! isset($item->product->category->name) ? $item->product->category->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->product->uom->name) ? $item->product->uom->name : '--' !!}</span>
                        </div>
                        {{-- <div class="row table-row-10">
                            <span>{!! $item->current_stock !!}</span>
                        </div> --}}
                        <div class="row table-row-10">
                            {{-- <span>{!! isset($item->status) && $item->status == "stock_in" ? $item->stock_in : $item->stock_out !!}</span> --}}
                            <span>{!! $item->qty !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! $item->stock_status_title ?? $item->status !!}</span>
                        </div>
                        <div class="row table-row-10">
                            @if ($item->type == 'shop' && $item->status == 'stock_in')
                                <span>{!! isset($item->data_to->name) ? $item->data_to->name : '--' !!}</span>
                            @else
                                <span>{!! isset($item->shop->name) ? $item->shop->name : '--' !!}</span>
                            @endif
                        </div>
                        <div class="row table-row-10">
                            @if ($item->type == 'shop' && $item->status == 'stock_in')
                                <span>{!! isset($item->shop->name) ? $item->shop->name : '--' !!}</span>
                            @else
                                <span>{!! isset($item->data_to->name) ? $item->data_to->name : '--' !!}</span>
                            @endif
                        </div>
                        <div class="row table-row-10">

                            <span>{!! isset($item->created_date) ? $item->created_date : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            @if ($item->request_by_type == 'admin')
                                <span>{!! isset($item->user->username) && $item->user->username ? $item->user->username : '--' !!}</span>
                            @elseif ($item->request_by_type == 'barber')
                                <span>{!! isset($item->barber->name) && $item->barber->name ? $item->barber->name : '--' !!}</span>
                            @endif
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
            'name' => __('stock_movement.empty.title'),
            'msg' => __('stock_movement.empty.description'),
        ])
        @endcomponent
    @endif
</div>
