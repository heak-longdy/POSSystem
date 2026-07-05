<div class="table">
    @if ($data->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                <div class="row table-row-5">
                    <span>Nº</span>
                </div>
                <div class="row table-row-20 textLeft">
                    <span>Product</span>
                </div>
                <div class="row table-row-15">
                    <span>Categories</span>
                </div>
                <div class="row table-row-10">
                    <span>UOM</span>
                </div>
                <div class="row table-row-15">
                    <span>Current Stock</span>
                </div>
                <div class="row table-row-10">
                    <span>Date</span>
                </div>
                <div class="row table-row-15">
                    <span>Shop</span>
                </div>
                <div class="row table-row-10">
                    <span>Requested By</span>
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
                       
                        <div class="row table-row-15">
                            <span>{!! isset($item->product->category->name) ? $item->product->category->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->product->uom->name) ? $item->product->uom->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->current_stock) ? $item->current_stock : 0 !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->created_date) ? $item->created_date : '--' !!}</span>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->shop->name) ? $item->shop->name : '--' !!}</span>
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
            'name' => __('Stock on hand is empty'),
            ])
        @endcomponent
    @endif
</div>
