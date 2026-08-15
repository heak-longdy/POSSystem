<div class="table">
    @if ($data->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                <div class="row table-row-5">
                    <span>Nº</span>
                </div>
                <div class="row table-row-10">
                    <span>Image</span>
                </div>
                <div class="row table-row-20">
                    <span>Name</span>
                </div>
                <div class="row table-row-20">
                    <span>Price</span>
                </div>
                <div class="row table-row-10">
                    <span>Commission %</span>
                </div>
                <div class="row table-row-15">
                    <span>Category</span>
                </div>
                <div class="row table-row-15">
                    <span>UOM</span>
                </div>
                <div class="row table-row-5">
                    <span></span>
                </div>
            </div>
            <div class="table-body">
                @foreach ($data as $index => $item)
                    <div class="column">
                        <div class="row table-row-5">
                            <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <div class="thumbnail" data-fancybox data-src="{{ asset('file_manager' . $item->image) }}">
                                <img src="{!! $item->image_url !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                        </div>
                        <div class="row table-row-20">
                            <span>{!! isset($item->name) ? $item->name : '---' !!}</span>
                        </div>
                        <div class="row table-row-20">
                            <span>{!! isset($item->price) ? $item->price : '---' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->commission) ? $item->commission : '---' !!}</span>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->category) ? $item->category->name : '---' !!}</span>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->uom) ? $item->uom->name : '---' !!}</span>
                        </div>
                        <div class="row table-row-5">
                            <div class="dropdown">
                                <i data-feather="more-vertical" class="action-btn" id="dropdownMenuButton"
                                    data-mdb-toggle="dropdown" aria-expanded="false">
                                </i>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    @if ($status != 'trash')
                                        @can('product-update')
                                            <li>
                                                <a class="dropdown-item" s-click-link="{!! route('admin-product-edit', $item->id) !!}">
                                                    <i data-feather="edit"></i>
                                                    <span>Edit</span>
                                                </a>
                                            </li>
                                            @if ($item->status == 2)
                                                <li>
                                                    <a class="dropdown-item enable-btn"
                                                        onclick="$onConfirmMessage(
                                                            '{!! route('admin-product-status', ['id' => $item->id, 'status' => 1]) !!}',
                                                            '@lang('dialog.msg.enable', ['name' => isset($item->name) ? $item->name : $item->name])',
                                                            {
                                                                confirm: '@lang('dialog.button.enable')',
                                                                cancel: '@lang('dialog.button.cancel')'
                                                            },
                                                        );">
                                                        <i data-feather="rotate-ccw"></i>
                                                        <span>@lang('table.option.enable')</span>
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <a class="dropdown-item disable-btn"
                                                        onclick="$onConfirmMessage(
                                                            '{!! route('admin-product-status', ['id' => $item->id, 'status' => 2]) !!}',
                                                            '@lang('dialog.msg.disable', ['name' => isset($item->name) ? $item->name : $item->name])',
                                                            {
                                                                confirm: '@lang('dialog.button.disable')',
                                                                cancel: '@lang('dialog.button.cancel')'
                                                            }
                                                        );">
                                                        <i data-feather="x-circle"></i>
                                                        <span>@lang('table.option.disable')</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endcan
                                    @endif
                                </ul>
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
        @component('admin::components.empty',
            [
                'name' => __('No data'),
                'msg' => __('You can create new product by click button above'),
                'permission' => 'product-create',
                'url' => route('admin-product-create'),
                'button' => __('Create Product'),
            ])
        @endcomponent
    @endif
</div>
