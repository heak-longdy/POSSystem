@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => 'Shop Product'])
    <div class="content-wrapper" id="app" x-data="xShopProductListing">
        <div class="header box-shadow-bottom">
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="tabs">
                            <a href="{!! route('admin-' . $routeName . '-list', 1) !!}">
                                <i class='bx bx-store'></i>
                                Shop
                            </a>
                            <a href="{!! route('admin-' . $routeName . '-product', $id) !!}" class="{!! $status != 'trash' ? 'tabActive' : '' !!}">
                                <i class='bx bx-package'></i>
                                Product
                            </a>
                            <a href="{!! route('admin-' . $routeName . '-product', ['id' => $id, 'status' => 'trash']) !!}" class="{!! $status == 'trash' ? 'tabActive' : '' !!}">
                                <i class='bx bx-trash-alt'></i>
                                Trash
                            </a>
                        </div>
                    </div>
                </div>
                <div class="header-action-button">
                    <form class="filter" action="{!! url()->current() !!}" method="GET">
                        <div class="form-row w200">
                            <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                        </div>
                        <button mat-flat-button type="submit" class="bg-success btnSearch">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </button>
                    </form>
                    <button s-click-link="{!! url()->current() !!}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Reload</span>
                    </button>
                    @if ($status != 'trash')
                        <button class="btn btn-create" s-click-link="{!! route('admin-' . $routeName . '-product-create', $id) !!}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Add Product to Shop</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="table">
                @if ($products->count() > 0)
                    <div class="table-wrapper">
                        <div class="paginationLayout2">
                            @include('admin::components.pagination', ['paginate' => $products])
                        </div>
                        <div class="table-header">
                            <div class="row table-row-5"><span>Nº</span></div>
                            <div class="row table-row-8 text left"><span>Image</span></div>
                            <div class="row table-row-20 text left"><span>Product</span></div>
                            <div class="row table-row-12 text left"><span>Category</span></div>
                            <div class="row table-row-8 text left"><span>UOM</span></div>
                            <div class="row table-row-9"><span>Price</span></div>
                            <div class="row table-row-7"><span>Point</span></div>
                            <div class="row table-row-7"><span>Max Qty</span></div>
                            <div class="row table-row-9"><span>Commission</span></div>
                            <div class="row table-row-10"><span>Status</span></div>
                            <div class="row table-row-5"><span></span></div>
                        </div>
                        <div class="table-body">
                            @foreach ($products as $index => $item)
                                <div class="column">
                                    <div class="row table-row-5">
                                        <span>{!! $products->currentPage() * $products->perPage() - $products->perPage() + ($index + 1) !!}</span>
                                    </div>
                                    <div class="row table-row-8 text left">
                                        <div class="thumbnail" data-fancybox data-src="{!! $item->product?->image_url !!}">
                                            <img src="{!! $item->product?->image_url !!}"
                                                onerror="this.src='{{ asset('images/logo/default.png') }}'" alt="">
                                        </div>
                                    </div>
                                    <div class="row table-row-20 text left">
                                        <span>{{ $item->product?->name ?? '---' }}</span>
                                    </div>
                                    <div class="row table-row-12 text left">
                                        <span>{{ $item->product?->category?->name ?? '---' }}</span>
                                    </div>
                                    <div class="row table-row-8 text left">
                                        <span>{{ $item->product?->uom?->name ?? '---' }}</span>
                                    </div>
                                    <div class="row table-row-9">
                                        <span>{{ $item->price !== null ? number_format($item->price, 2) . ' $' : '---' }}</span>
                                    </div>
                                    <div class="row table-row-7">
                                        <span>{{ $item->point ?? '---' }}</span>
                                    </div>
                                    <div class="row table-row-7">
                                        <span>{{ $item->max_qty ?? '---' }}</span>
                                    </div>
                                    <div class="row table-row-9">
                                        <span>
                                            @if ($item->commission !== null)
                                                {{ number_format($item->commission, 2) }}{{ $item->commission_type == 'percent' ? '%' : '៛' }}
                                            @else
                                                ---
                                            @endif
                                        </span>
                                    </div>
                                    <div class="row table-row-10">
                                        <span>{{ config('dummy.status')[$item->status] ?? '---' }}</span>
                                    </div>
                                    <div class="row table-row-5">
                                        <div class="dropdown">
                                            <i data-feather="more-vertical" class="action-btn" id="dropdownMenuButton"
                                                data-mdb-toggle="dropdown" aria-expanded="false">
                                            </i>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                @if ($status != 'trash')
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{!! route('admin-' . $routeName . '-product-edit', ['shopId' => $id, 'shopProductId' => $item->id]) !!}">
                                                            <i class="material-symbols-outlined">edit</i>
                                                            <span>Edit</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger"
                                                            @click="verifyDialog({{ $item }},'delete','Delete')">
                                                            <i class="material-symbols-outlined">Delete</i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </li>
                                                @else
                                                    <li>
                                                        <a class="dropdown-item"
                                                            @click="verifyDialog({{ $item }},'restore','Restore')">
                                                            <i class="material-symbols-outlined">settings_backup_restore</i>
                                                            <span>Restore</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger"
                                                            @click="verifyDialog({{ $item }},'destroy','Destroy')">
                                                            <i class="material-symbols-outlined">Delete</i>
                                                            <span>Destroy</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="paginationLayout">
                            @include('admin::components.paginationNumber', ['paginate' => $products])
                        </div>
                    </div>
                @else
                    @component('admin::components.empty', [
                        'name' => $status == 'trash' ? 'No trash product' : 'No product',
                        'msg' => $status == 'trash' ? 'Trash product data not found.' : 'You can add a product to this shop by clicking the button below',
                        'url' => $status == 'trash' ? null : route('admin-' . $routeName . '-product-create', $id),
                        'button' => $status == 'trash' ? null : 'Add Product to Shop',
                    ])
                    @endcomponent
                @endif
            </div>
        </div>
        @include('admin::components.verify')
    </div>
@stop

@section('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('xShopProductListing', () => ({
                verifyDialog(data, typeAction, btn) {
                    this.$store.confirmDialog.open({
                        data: {
                            message: `Are you sure want to ${btn} ?`,
                            btnClose: `{{ __('action_button.cancel') }}`,
                            btnSave: btn,
                            item: data,
                            urlName: `shop/product/{{ $id }}`,
                            typeAction: typeAction,
                            digPosition: "posTop",
                            class: "deleteDialog",
                            width: "18rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                window.location.href = `{{ url()->full() }}`;
                            }
                        }
                    });
                },
            }))
        });
    </script>
@stop
