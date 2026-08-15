@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xShopProduct">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper shop-product-form" action="{!! route('admin-' . $routeName . '-product-save', $id) !!}" method="POST">
            <div class="form-header">
                <h3 class="shop-product-page-title">
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-product', $id) !!}"></i>
                    <span>Add Product to Shop</span>
                </h3>
                <div class="shop-product-context">
                    <span>Shop</span>
                    <strong>{{ $shop?->name }}</strong>
                </div>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                @error('products')
                    <label class="error">{{ $message }}</label>
                @enderror

                <div class="shop-product-toolbar">
                    <div class="shop-product-toolbar-text">
                        <strong>Product assignment</strong>
                        <span>Choose products and configure shop-specific price, points, limit, commission, and status.</span>
                    </div>
                    {{-- <button type="button" color="primary" @click="addRow" :disabled="!canAddRow()" x-show="!isEditMode()" style="display: none;">
                        <i class="material-symbols-outlined">add</i>
                        <span>Add Product</span>
                    </button> --}}
                </div>

                <div class="shop-product-alert" x-show="products.length === 0 && !isEditMode()" style="display: none;">
                    All active products are already assigned to this shop.
                </div>

                <template x-if="productRows.length > 0">
                    <template x-for="(row,index) in productRows" :key="row.uid">
                        <div class="shop-product-row">
                            <input type="hidden" :name="`products[${index}][id]`" x-model="row.id">
                            <input type="hidden" :name="`products[${index}][product_name]`" x-model="row.product_name">
                            <input type="hidden" :name="`products[${index}][product_image]`" x-model="row.product_image">
                            <input type="hidden" :name="`products[${index}][product_uom]`" x-model="row.product_uom">
                            <input type="hidden" :name="`products[${index}][product_category]`" x-model="row.product_category">

                            <div class="shop-product-row-header">
                                <div>
                                    <span x-text="row.id ? 'Assigned product' : `Product ${index + 1}`"></span>
                                    <h4 x-text="row.product_name || 'Select a product'"></h4>
                                </div>
                                <template x-if="!row.id">
                                    <button type="button" class="shop-product-remove" @click="removeRow(index)">
                                        <i class="material-symbols-outlined">delete</i>
                                    </button>
                                </template>
                            </div>

                            <div class="shop-product-main">
                                <div class="shop-product-preview">
                                    <img :src="productImage(row)" onerror="this.src='{{ asset('images/logo/default.png') }}'" alt="">
                                </div>
                                <div class="form-row">
                                    <label>Product <span>*</span></label>
                                    <template x-if="row.id">
                                        <div class="shop-product-readonly-wrap">
                                            <input type="hidden" :name="`products[${index}][product_id]`"
                                                x-model="row.product_id">
                                            <input type="text" x-model="row.product_name" readonly>
                                        </div>
                                    </template>
                                    <template x-if="!row.id">
                                        <select :name="`products[${index}][product_id]`" x-model="row.product_id"
                                            @change="syncProduct(row)">
                                            <option value="">Select Product</option>
                                            <template x-for="product in availableProducts(row)" :key="product.id">
                                                <option :value="product.id" x-text="product.name"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <div class="shop-product-meta">
                                        <span x-text="row.product_category || '---'"></span>
                                        <span x-text="row.product_uom || '---'"></span>
                                    </div>
                                    <template x-if="fieldError(index, 'product_id')">
                                        <label class="error" x-text="fieldError(index, 'product_id')"></label>
                                    </template>
                                </div>
                            </div>

                            <div class="shop-product-grid">
                                <div class="form-row iconInput">
                                    <label>Price <span>*</span></label>
                                    <input type="number" step="0.01" :name="`products[${index}][price]`"
                                        x-model="row.price" placeholder="Enter price ...">
                                    <i class='bx bx-dollar'></i>
                                    <template x-if="fieldError(index, 'price')">
                                        <label class="error" x-text="fieldError(index, 'price')"></label>
                                    </template>
                                </div>
                                <div class="form-row iconInput">
                                    <label>Point</label>
                                    <input type="number" step="0.01" :name="`products[${index}][point]`"
                                        x-model="row.point" placeholder="Enter point ...">
                                    <i class='bx bx-badge-check'></i>
                                    <template x-if="fieldError(index, 'point')">
                                        <label class="error" x-text="fieldError(index, 'point')"></label>
                                    </template>
                                </div>
                                <div class="form-row iconInput">
                                    <label>Max Qty</label>
                                    <input type="number" step="1" :name="`products[${index}][max_qty]`"
                                        x-model="row.max_qty" placeholder="Enter max qty ...">
                                    <i class='bx bx-package'></i>
                                    <template x-if="fieldError(index, 'max_qty')">
                                        <label class="error" x-text="fieldError(index, 'max_qty')"></label>
                                    </template>
                                </div>
                                <div class="form-row iconInput">
                                    <label>Commission</label>
                                    <input type="number" step="0.01" :name="`products[${index}][commission]`"
                                        x-model="row.commission" placeholder="Enter commission ...">
                                    <i class='bx bx-money'></i>
                                    <template x-if="fieldError(index, 'commission')">
                                        <label class="error" x-text="fieldError(index, 'commission')"></label>
                                    </template>
                                </div>
                                <div class="form-row">
                                    <label>Commission Type</label>
                                    <select :name="`products[${index}][commission_type]`" x-model="row.commission_type">
                                        <option value="">Select Type</option>
                                        <option value="khr">KHR</option>
                                        <option value="percent">%</option>
                                    </select>
                                    <template x-if="fieldError(index, 'commission_type')">
                                        <label class="error" x-text="fieldError(index, 'commission_type')"></label>
                                    </template>
                                </div>
                                <div class="form-row">
                                    <label>Status <span>*</span></label>
                                    <select :name="`products[${index}][status]`" x-model="row.status">
                                        @foreach (config('dummy.status') as $key => $item)
                                            <option value="{{ $key }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                    <template x-if="fieldError(index, 'status')">
                                        <label class="error" x-text="fieldError(index, 'status')"></label>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
                <div style="margin-bottom: 40px;">
                    <button type="button" class="add_button" color="primary" @click="addRow" :disabled="!canAddRow()" x-show="!isEditMode()" style="display: none;">
                        <i class="material-symbols-outlined">add</i>
                        <span>Add Product</span>
                    </button>
                </div>

                <div class="form-button">
                    <button type="submit" color="primary" :disabled="products.length === 0 && !isEditMode()">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-product', $id) !!}">
                        <i data-feather="x"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop

@section('script')
    <style>
        .shop-product-form {
            max-width: 72rem;
            padding-left: 70px;
            padding-right: 70px;
        }

        .shop-product-form .form-header {
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 16px 0 18px;
        }

        .shop-product-page-title {
            gap: 10px;
            color: #3b3f47;
            font-size: 25px;
            line-height: 1.2;
        }

        .shop-product-page-title span {
            display: inline-block;
        }

        .shop-product-context {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid rgba(152, 152, 152, 0.2);
            border-radius: 8px;
            background: #fff;
            color: #6b7280;
            font-size: 13px;
        }

        .shop-product-context strong {
            color: #3b3f47;
            font-weight: 600;
        }

        .shop-product-form .form-body {
            padding: 28px;
        }

        .shop-product-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .shop-product-toolbar-text {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .shop-product-toolbar-text strong {
            color: #3b3f47;
            font-size: 16px;
            font-weight: 600;
        }

        .shop-product-toolbar-text span {
            color: #7a7f89;
            font-size: 13px;
            line-height: 1.45;
        }

        .add_button {
            min-width: 145px !important;
            height: 40px;
            border: none !important;
            border-radius: 20px !important;
            background: #3C91E6;
            color: #fff;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px;
            font-size: 14px;
        }

        .add_button:disabled {
            cursor: not-allowed;
            background: #d9dce1;
            color: #999;
        }

        .add_button i {
            font-size: 22px;
        }

        .shop-product-alert {
            margin: -8px 0 18px;
            padding: 10px 12px;
            border: 1px solid rgba(60, 145, 230, 0.18);
            border-radius: 8px;
            background: rgba(60, 145, 230, 0.07);
            color: #4b657f;
            font-size: 13px;
        }

        .shop-product-row {
            border: 1px solid rgba(152, 152, 152, 0.24);
            border-radius: 8px;
            margin-bottom: 18px;
            padding: 20px;
            background: #fff;
        }

        .shop-product-row-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(152, 152, 152, 0.16);
        }

        .shop-product-row-header span {
            color: #8a9099;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .shop-product-row-header h4 {
            margin: 4px 0 0;
            color: #3b3f47;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.35;
        }

        .shop-product-remove {
            width: 38px;
            min-width: 38px !important; 
            height: 38px;
            border: 1px solid rgba(255, 56, 56, 0.22) !important;
            border-radius: 8px !important;
            background: rgba(255, 56, 56, 0.08) !important;
            color: #ff5656 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .shop-product-remove i {
            font-size: 21px;
        }

        .shop-product-main {
            display: grid;
            grid-template-columns: 96px minmax(0, 1fr);
            gap: 18px;
            align-items: start;
            margin-bottom: 20px;
        }

        .shop-product-preview {
            width: 96px;
            height: 96px;
            border-radius: 8px;
            overflow: hidden;
            background: #f6f7f9;
            border: 1px solid rgba(152, 152, 152, 0.2);
        }

        .shop-product-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .shop-product-readonly-wrap {
            width: 100%;
        }

        .shop-product-readonly-wrap input[type="text"] {
            background-color: #f9fafb;
            border-radius: 7px;
            border: 1px solid #d8dce5;
            box-sizing: border-box;
            color: #5a5e66;
            display: inline-block;
            font-size: 14px;
            height: 43px;
            line-height: 1;
            outline: 0;
            padding: 0 15px;
            width: 100%;
        }

        .shop-product-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            column-gap: 16px;
            row-gap: 20px;
            align-items: end;
        }

        .shop-product-grid .form-row,
        .shop-product-main .form-row {
            margin-bottom: 0 !important;
            min-width: 0;
        }

        .shop-product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
            color: #777;
            font-size: 12px;
        }

        .shop-product-meta span {
            border: 1px solid rgba(152, 152, 152, 0.2);
            border-radius: 6px;
            padding: 4px 9px;
            background: #fafafa;
            color: #6b7280;
            display: flex;
            align-items: center;
        }

        .shop-product-form .form-button {
            padding-top: 8px;
            border-top: 1px solid rgba(152, 152, 152, 0.16);
            margin-top: 8px;
        }

        .shop-product-form .form-button button:disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }

        @media (max-width: 1200px) {
            .shop-product-form {
                padding-left: 40px;
                padding-right: 40px;
            }

            .shop-product-grid {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .shop-product-form {
                padding-left: 18px;
                padding-right: 18px;
            }

            .shop-product-form .form-body {
                padding: 18px;
            }

            .shop-product-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .shop-product-toolbar button {
                width: 100%;
            }

            .shop-product-main,
            .shop-product-grid {
                grid-template-columns: 1fr;
            }

            .shop-product-preview {
                width: 100%;
                height: 180px;
            }
        }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data("xShopProduct", () => ({
                baseImageUrl: "{{ asset('file_manager') }}",
                defaultImage: "{{ asset('images/logo/default.png') }}",
                existingRows: @json($products ?? []),
                oldRows: @json(old('products')),
                products: @json($availableProducts ?? []),
                errors: @json($errors->toArray()),
                productRows: [],
                init() {
                    const sourceRows = this.oldRows && this.oldRows.length > 0 ? this.oldRows : this.existingRows;
                    this.productRows = sourceRows && sourceRows.length > 0
                        ? sourceRows.map((row) => this.normalizeRow(row))
                        : [this.newRow()];
                },
                newRow() {
                    return {
                        uid: `${Date.now()}-${Math.random()}`,
                        id: '',
                        product_id: '',
                        product_name: '',
                        product_image: '',
                        product_uom: '',
                        product_category: '',
                        price: '',
                        point: '',
                        max_qty: '',
                        commission: '',
                        commission_type: 'khr',
                        status: '1',
                    };
                },
                normalizeRow(row) {
                    return {
                        uid: `${Date.now()}-${Math.random()}`,
                        id: row?.id ?? '',
                        product_id: row?.product_id ? String(row.product_id) : '',
                        product_name: row?.product_name ?? '',
                        product_image: row?.product_image ?? '',
                        product_uom: row?.product_uom ?? '',
                        product_category: row?.product_category ?? '',
                        price: row?.price ?? '',
                        point: row?.point ?? '',
                        max_qty: row?.max_qty ?? '',
                        commission: row?.commission ?? '',
                        commission_type: row?.commission_type ?? 'khr',
                        status: row?.status ? String(row.status) : '1',
                    };
                },
                selectedProductIds(exceptUid = null) {
                    return this.productRows
                        .filter((row) => row.uid !== exceptUid && row.product_id)
                        .map((row) => Number(row.product_id));
                },
                availableProducts(row) {
                    const selectedIds = this.selectedProductIds(row.uid);
                    return this.products.filter((product) => {
                        return !selectedIds.includes(Number(product.id)) ||
                            Number(product.id) === Number(row.product_id);
                    });
                },
                isEditMode() {
                    return this.productRows.some((row) => row.id);
                },
                syncProduct(row) {
                    const product = this.products.find((item) => Number(item.id) === Number(row.product_id));
                    if (!product) {
                        row.product_name = '';
                        row.product_image = '';
                        row.product_uom = '';
                        row.product_category = '';
                        row.price = '';
                        row.commission = '';
                        return;
                    }

                    row.product_name = product.name ?? '';
                    row.product_image = product.image ?? '';
                    row.product_uom = product.uom ?? '';
                    row.product_category = product.category ?? '';
                    row.price = product.price ?? '';
                    row.commission = product.commission ?? '';
                    row.commission_type = row.commission_type || 'khr';
                },
                canAddRow() {
                    const hasBlankNewRow = this.productRows.some((row) => !row.id && !row.product_id);
                    const selectedIds = this.selectedProductIds();
                    const hasAvailableProduct = this.products.some((product) => {
                        return !selectedIds.includes(Number(product.id));
                    });

                    return !hasBlankNewRow && hasAvailableProduct;
                },
                addRow() {
                    if (!this.canAddRow()) {
                        return;
                    }
                    this.productRows.push(this.newRow());
                },
                removeRow(index) {
                    if (this.productRows.length === 1) {
                        this.productRows.splice(index, 1, this.newRow());
                        return;
                    }
                    this.productRows.splice(index, 1);
                },
                productImage(row) {
                    return row.product_image ? this.baseImageUrl + row.product_image : this.defaultImage;
                },
                fieldError(index, field) {
                    const key = `products.${index}.${field}`;
                    return this.errors?.[key]?.[0] ?? '';
                },
            }));
        });
    </script>
@stop
