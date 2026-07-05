<div class="table">
    @if ($data->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                <div class="row table-row-5">
                    <span>Nº</span>
                </div>
                <div class="row table-row-10 textLeft">
                    <span>Product</span>
                </div>
                <div class="row table-row-10">
                    <span>Categories</span>
                </div>
                <div class="row table-row-5">
                    <span>UOM</span>
                </div>
                <div class="row table-row-5">
                    <span>Qty</span>
                </div>
                <div class="row table-row-10">
                    <span>Date</span>
                </div>
                <div class="row table-row-15">
                    <span>Remark</span>
                </div>
                <div class="row table-row-10">
                    <span>Shop</span>
                </div>
                <div class="row table-row-10">
                    <span>To</span>
                </div>
                <div class="row table-row-10">
                    <span>Requested By</span>
                </div>
                <div class="row table-row-10">
                    <span>Status</span>
                </div>
            </div>
            <div class="table-body">
                @foreach ($data as $index => $item)
                    <div class="column">
                        <div class="row table-row-5">
                            <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                        </div>
                        <div class="row table-row-10 previewImageText textLeft">
                            {{-- <div class="thumbnail" data-fancybox data-src="{{ asset('file_manager' . $item?->product?->image) }}">
                                <img src="{!! $item?->product?->image_url !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div> --}}
                            <span class="textView">{!! isset($item->product->name) ? $item->product->name : '--' !!}</span>
                        </div>
                       
                        <div class="row table-row-10">
                            <span>{!! isset($item->product->category->name) ? $item->product->category->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-5">
                            <span>{!! isset($item->product->uom->name) ? $item->product->uom->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-5">
                            <span>{!! isset($item->qty) ? $item->qty : 0 !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->created_date) ? $item->created_date : '--' !!}</span>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->remark) && $item->remark ? $item->remark : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->shop->name) ? $item->shop->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->shopTo->name) ? $item->shopTo->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            @if ($item->request_by_type == 'admin')
                                <span>{!! isset($item->user->username) && $item->user->username ? $item->user->username : '--' !!}</span>
                            @elseif ($item->request_by_type == 'barber')
                                <span>{!! isset($item->barber->name) && $item->barber->name ? $item->barber->name : '--' !!}</span>
                            @endif
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->status) && $item->status == 1 ?  "Confirmed" : '--' !!}</span>
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
            'name' => __('Stock transfer is empty'),
            'msg' => __('You can create a new stock transfer by clicking the button below.'),
            'permission' => 'stock-transfer-create',
            'url' => route('admin-stock-transfer-create'),
            'button' => __('Create New Stock transfer'),
            ])
        @endcomponent
    @endif
</div>
