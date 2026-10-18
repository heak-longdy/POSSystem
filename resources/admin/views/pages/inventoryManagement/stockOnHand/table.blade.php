<div class="table">
    @if ($data->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                <div class="row table-row-5">
                    <span>{{ __('global.table.no') }}</span>
                </div>
                <div class="row table-row-20 textLeft">
                    <span>{{ __('stock_on_hand.table.product') }}</span>
                </div>
                <div class="row table-row-14">
                    <span>{{ __('stock_on_hand.table.category') }}</span>
                </div>
                <div class="row table-row-6">
                    <span>{{ __('stock_on_hand.table.uom') }}</span>
                </div>
                <div class="row table-row-9">
                    <span>{{ __('stock_on_hand.table.current_stock') }}</span>
                </div>
                <div class="row table-row-12 text right">
                    <span>{{ __('stock_on_hand.table.total_cost') }}</span>
                </div>
                <div class="row table-row-12 text right">
                    <span>{{ __('stock_on_hand.table.total_amount') }}</span>
                </div>
                <div class="row table-row-14">
                    <span>{{ __('stock_on_hand.table.shop') }}</span>
                </div>
                <div class="row table-row-8">
                    <span>{{ __('stock_on_hand.table.date') }}</span>
                </div>
            </div>
            <div class="table-body">
                @foreach ($data as $index => $item)
                    <div class="column">
                        <div class="row table-row-5">
                            <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                        </div>
                        <div class="row table-row-20 previewImageText textLeft">
                            <span class="textView">{!! isset($item->product->name) ? $item->product->name : '--' !!}</span>
                        </div>
                       
                        <div class="row table-row-14">
                            <span>{!! isset($item->product->category->name) ? $item->product->category->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-6">
                            <span>{!! isset($item->product->uom->name) ? $item->product->uom->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-9">
                            <span>{!! isset($item->current_stock) ? $item->current_stock : 0 !!}</span>
                        </div>
                        <div class="row table-row-12 text right">
                            <span>{!! $item->total_cost_title ?? '$0.00' !!}</span>
                        </div>
                        <div class="row table-row-12 text right">
                            <span>{!! $item->total_amount_title ?? '$0.00' !!}</span>
                        </div>
                        <div class="row table-row-14">
                            <span>{!! isset($item->shop->name) ? $item->shop->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-8">
                            <span>{!! isset($item->created_date) ? $item->created_date : '--' !!}</span>
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
            'name' => __('stock_on_hand.empty.title'),
            'msg' => __('stock_on_hand.empty.description'),
            ])
        @endcomponent
    @endif
</div>
