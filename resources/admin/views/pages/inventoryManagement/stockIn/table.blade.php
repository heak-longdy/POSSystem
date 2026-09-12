<div class="table">
    @if ($data->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                <div class="row table-row-5">
                    <span>{{ __('global.table.no') }}</span>
                </div>
                <div class="row table-row-15 textLeft">
                    <span>{{ __('stock_in.table.product') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_in.table.shop') }}</span>
                </div>
                <div class="row table-row-15">
                    <span>{{ __('stock_in.table.category') }}</span>
                </div>
                <div class="row table-row-5">
                    <span>{{ __('stock_in.table.uom') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_in.table.qty') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_in.table.date') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_in.table.remark') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_in.table.requested_by') }}</span>
                </div>
                <div class="row table-row-10">
                    <span>{{ __('stock_in.table.status') }}</span>
                </div>
            </div>
            <div class="table-body">
                @foreach ($data as $index => $item)
                    <div class="column">
                        <div class="row table-row-5">
                            <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                        </div>
                        <div class="row table-row-15 previewImageText textLeft">
                            {{-- <div class="thumbnail" data-fancybox data-src="{{ asset('file_manager' . $item?->product?->image) }}">
                                <img src="{!! $item?->product?->image_url !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div> --}}
                            <span class="textView">{!! isset($item->product->name) ? $item->product->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->shop->name) ? $item->shop->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->product->category->name) ? $item->product->category->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-5">
                            <span>{!! isset($item->product->uom->name) ? $item->product->uom->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->qty) ? $item->qty : 0 !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->created_date) ? $item->created_date : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->remark) && $item->remark ? $item->remark : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            @if ($item->request_by_type == 'admin')
                                <span>{!! isset($item->user->username) && $item->user->username ? $item->user->username : '--' !!}</span>
                            @elseif ($item->request_by_type == 'barber')
                                <span>{!! isset($item->barber->name) && $item->barber->name ? $item->barber->name : '--' !!}</span>
                            @endif
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->status) && $item->status == 1 ? __('stock_in.status.confirmed') : '--' !!}</span>
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
