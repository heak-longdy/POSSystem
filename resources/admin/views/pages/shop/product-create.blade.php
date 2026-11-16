@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xShopProduct">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper shop-product-form" action="{!! route('admin-' . $routeName . '-product-save', $id) !!}" method="POST" @submit="handleSubmit($event)">
            <div class="form-header">
                <h3 class="shop-product-page-title">
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-product', $id) !!}"></i>
                    <span x-text="isEditMode() ? '{{ __('shop.product.title_edit') }}' : '{{ __('shop.product.title_create') }}'">{{ __('shop.product.title_create') }}</span>
                </h3>
                <div class="shop-product-context">
                    <span>{{ __('shop.name') }}</span>
                    <strong>{{ $shop?->name }}</strong>
                </div>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                @error('products')
                    <label class="error">{{ $message }}</label>
                @enderror
                <template x-if="errors?.products?.[0]">
                    <label class="error" x-text="errors.products[0]"></label>
                </template>

                <div class="shop-product-toolbar">
                    <div class="shop-product-toolbar-text">
                        <strong>{{ __('shop.product.toolbar_title') }}</strong>
                        <span>{{ __('shop.product.toolbar_desc') }}</span>
                    </div>
                    {{-- <button type="button" color="primary" @click="addRow" :disabled="!canAddRow()" x-show="!isEditMode()" style="display: none;">
                        <i class="material-symbols-outlined">add</i>
                        <span>{{ __('shop.button.add_product_row') }}</span>
                    </button> --}}
                </div>

                <div class="shop-product-alert" x-show="products.length === 0 && !isEditMode()" style="display: none;">
                    {{ __('shop.product.all_assigned_alert') }}
                </div>

                <template x-for="(row,index) in productRows" :key="row.uid">
                    <div class="shop-product-row">
                            <input type="hidden" :name="`products[${index}][id]`" x-model="row.id">
                            <input type="hidden" :name="`products[${index}][product_code]`" x-model="row.product_code">
                            <input type="hidden" :name="`products[${index}][product_name]`" x-model="row.product_name">
                            <input type="hidden" :name="`products[${index}][product_image]`" x-model="row.product_image">
                            <input type="hidden" :name="`products[${index}][product_uom]`" x-model="row.product_uom">
                            <input type="hidden" :name="`products[${index}][product_category]`" x-model="row.product_category">
                            <input type="hidden" :name="`products[${index}][point]`" x-model="row.point">
                            <input type="hidden" :name="`products[${index}][commission]`" x-model="row.commission">
                            <input type="hidden" :name="`products[${index}][commission_type]`" x-model="row.commission_type">
                            <input type="hidden" :name="`products[${index}][max_qty]`" x-model="row.max_qty">

                            <div class="shop-product-row-header">
                                <div>
                                    <span x-text="row.id ? '{{ __('shop.product.assigned_product') }}' : `{{ __('shop.product.product_label') }} ${index + 1}`"></span>
                                    <h4 x-text="row.product_name ? (row.product_code ? `${row.product_code} - ${row.product_name}` : row.product_name) : '{{ __('shop.product.select_product') }}'"></h4>
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
                                    <label>{{ __('shop.product.product') }} <span>*</span></label>
                                    <div class="select2Group">
                                        <select :name="`products[${index}][product_id]`"
                                            :id="`product_id_${row.uid}`"
                                            class="SelectProduct select2"
                                            :class="{ 'is-invalid': !!fieldError(index, 'product_id') }"
                                            x-init="initSelect2Product(row, index, $el)">
                                            <option value="">{{ __('shop.product.select_product') }}</option>
                                            @if (isset($availableProducts) && count($availableProducts))
                                                @foreach ($availableProducts as $product)
                                                    @php
                                                        $pCode = trim((string) data_get($product, 'code', ''));
                                                        $pName = data_get($product, 'name', '');
                                                    @endphp
                                                    <option value="{{ data_get($product, 'id') }}">
                                                        {{ $pCode !== '' ? $pCode . ' - ' : '' }}{{ $pName }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="select2Reset" x-show="row.product_id" @click.stop="resetProduct(row, index)" style="display: none;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="shop-product-meta">
                                        <span x-show="row.product_code" x-text="row.product_code" style="display: none;"></span>
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
                                    <label>{{ __('shop.table.price') }} <span>*</span></label>
                                    <input type="text" inputmode="decimal" :name="`products[${index}][price]`"
                                        x-model="row.price"
                                        :class="{ 'is-invalid': !!fieldError(index, 'price') }"
                                        @input="clearFieldError(index, 'price')"
                                        @blur="handlePriceBlur(index)"
                                        placeholder="{{ __('shop.placeholder.enter_price') }}">
                                    <i class='bx bx-dollar'></i>
                                    <template x-if="fieldError(index, 'price')">
                                        <label class="error" x-text="fieldError(index, 'price')"></label>
                                    </template>
                                </div>


                                <div class="form-row">
                                    <label>{{ __('shop.table.status') }} <span>*</span></label>
                                    <select :name="`products[${index}][status]`" x-model="row.status"
                                        :class="{ 'is-invalid': !!fieldError(index, 'status') }"
                                        @change="clearFieldError(index, 'status')">
                                        @foreach (config('dummy.status') as $key => $item)
                                            <option value="{{ $key }}">
                                                {{ $key == 1 ? __('global.form.status.active') : __('global.form.status.disable') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <template x-if="fieldError(index, 'status')">
                                        <label class="error" x-text="fieldError(index, 'status')"></label>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                <div style="margin-bottom: 40px;">
                    <button type="button" class="add_button" color="primary" @click="addRow" :disabled="!canAddRow()" x-show="!isEditMode()" style="display: none;">
                        <i class="material-symbols-outlined">add</i>
                        <span>{{ __('shop.button.add_product_row') }}</span>
                    </button>
                </div>

                <div class="form-button">
                    <button type="submit" color="primary" :disabled="!canSubmit()">
                        <i data-feather="save"></i>
                        <span>{{ __('global.button.submit') }}</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-product', $id) !!}">
                        <i data-feather="x"></i>
                        <span>{{ __('global.button.cancel') }}</span>
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
            grid-template-columns: repeat(2, minmax(180px, 1fr));
            column-gap: 16px;
            row-gap: 20px;
            align-items: end;
        }

        .shop-product-grid .form-row,
        .shop-product-main .form-row {
            margin-bottom: 0 !important;
            min-width: 0;
        }

        .shop-product-form .error {
            position: static !important;
            display: block !important;
            color: #ff9d9d !important;
            font-size: 12px !important;
            margin-top: 5px !important;
            line-height: 1.3 !important;
        }

        .shop-product-form select.is-invalid,
        .shop-product-form input.is-invalid {
            border-color: #ff9d9d !important;
            box-shadow: 0 0 0 1px rgba(255, 157, 157, 0.25) !important;
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
                        product_code: '',
                        product_name: '',
                        product_image: '',
                        product_uom: '',
                        product_category: '',
                        price: '',
                        point: '',
                        max_qty: '',
                        commission: '',
                        commission_type: 'usd',
                        status: '1',
                    };
                },
                normalizeRow(row) {
                    let initialPrice = row?.price ?? '';
                    if (initialPrice !== null && initialPrice !== undefined && String(initialPrice).trim() !== '') {
                        const p = this.parseAndNormalizePrice(initialPrice);
                        if (p.valid) initialPrice = p.formatted;
                    }
                    let productCode = row?.product_code ?? '';
                    if (!productCode && row?.product_id && Array.isArray(this.products)) {
                        const matched = this.products.find((p) => Number(p.id) === Number(row.product_id));
                        if (matched && matched.code) {
                            productCode = matched.code;
                        }
                    }
                    return {
                        uid: `${Date.now()}-${Math.random()}`,
                        id: row?.id ?? '',
                        initial_product_id: row?.product_id ? String(row.product_id) : '',
                        product_id: row?.product_id ? String(row.product_id) : '',
                        product_code: productCode,
                        product_name: row?.product_name ?? '',
                        product_image: row?.product_image ?? '',
                        product_uom: row?.product_uom ?? '',
                        product_category: row?.product_category ?? '',
                        price: initialPrice,
                        point: row?.point ?? '',
                        max_qty: row?.max_qty ?? '',
                        commission: row?.commission ?? '',
                        commission_type: row?.commission_type ?? 'usd',
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
                syncProduct(row, productData = null) {
                    let product = (productData && productData.price !== undefined) ? productData : null;
                    if (!product && Array.isArray(this.products)) {
                        product = this.products.find((item) => Number(item.id) === Number(row.product_id));
                    }
                    if (!product) {
                        row.product_code = '';
                        row.product_name = '';
                        row.product_image = '';
                        row.product_uom = '';
                        row.product_category = '';
                        row.price = '';
                        row.commission = '';
                        return;
                    }

                    row.product_code = product.code ?? '';
                    row.product_name = product.name ?? '';
                    row.product_image = product.image ?? '';
                    row.product_uom = product.uom ?? '';
                    row.product_category = product.category ?? '';
                    let pPrice = product.price ?? '';
                    if (pPrice !== null && pPrice !== undefined && String(pPrice).trim() !== '') {
                        const p = this.parseAndNormalizePrice(pPrice);
                        if (p.valid) pPrice = p.formatted;
                    }
                    row.price = pPrice;
                    row.commission = product.commission ?? '';
                    row.commission_type = row.commission_type || 'usd';
                },
                parseAndNormalizePrice(input) {
                    if (input === null || input === undefined || input === '') {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'required' };
                    }

                    let str = String(input).trim();

                    // Convert Khmer numerals (០-៩)
                    const khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
                    for (let i = 0; i < khmerDigits.length; i++) {
                        str = str.split(khmerDigits[i]).join(String(i));
                    }

                    // Map Khmer khan (។) to period
                    str = str.split('។').join('.');

                    // Remove currency symbols & whitespace
                    str = str.replace(/[\$៛\s]/g, '');

                    if (str === '') {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'required' };
                    }

                    if (str.startsWith('-')) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'min' };
                    }

                    // Allowed characters: only digits, dots, commas
                    if (!/^[0-9.,]+$/.test(str)) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'invalid_chars' };
                    }

                    // Must contain at least one digit
                    if (!/[0-9]/.test(str)) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                    }

                    const hasDot = str.includes('.');
                    const hasComma = str.includes(',');

                    let clean = '';

                    if (hasDot && hasComma) {
                        const lastDot = str.lastIndexOf('.');
                        const lastComma = str.lastIndexOf(',');

                        if (lastDot > lastComma) {
                            // Comma is thousands, dot is decimal (e.g. 1,500.50)
                            if (str.split('.').length - 1 > 1) {
                                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                            }
                            const parts = str.split('.');
                            const thousandPart = parts[0];
                            const decimalPart = parts[1];

                            const groups = thousandPart.split(',');
                            if (groups[0].length < 1 || groups[0].length > 3) {
                                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                            }
                            for (let i = 1; i < groups.length; i++) {
                                if (groups[i].length !== 3) {
                                    return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                                }
                            }
                            clean = groups.join('') + '.' + decimalPart;
                        } else {
                            // Dot is thousands, comma is decimal (e.g. 1.500,50)
                            if (str.split(',').length - 1 > 1) {
                                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                            }
                            const parts = str.split(',');
                            const thousandPart = parts[0];
                            const decimalPart = parts[1];

                            const groups = thousandPart.split('.');
                            if (groups[0].length < 1 || groups[0].length > 3) {
                                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                            }
                            for (let i = 1; i < groups.length; i++) {
                                if (groups[i].length !== 3) {
                                    return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                                }
                            }
                            clean = groups.join('') + '.' + decimalPart;
                        }
                    } else if (hasComma) {
                        const commaCount = str.split(',').length - 1;
                        if (commaCount > 1) {
                            // Multiple commas -> thousands (e.g. 1,500,000)
                            const groups = str.split(',');
                            if (groups[0].length < 1 || groups[0].length > 3) {
                                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                            }
                            for (let i = 1; i < groups.length; i++) {
                                if (groups[i].length !== 3) {
                                    return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                                }
                            }
                            clean = groups.join('');
                        } else {
                            // Exactly 1 comma
                            const parts = str.split(',');
                            let before = parts[0];
                            const after = parts[1];

                            if (before === '') before = '0';

                            if (after.length === 3 && before.length >= 1 && before.length <= 3 && before !== '0') {
                                clean = before + after;
                            } else {
                                clean = before + (after !== '' ? '.' + after : '');
                            }
                        }
                    } else if (hasDot) {
                        const dotCount = str.split('.').length - 1;
                        if (dotCount > 1) {
                            // Multiple dots -> thousands (e.g. 1.500.000)
                            const groups = str.split('.');
                            if (groups[0].length < 1 || groups[0].length > 3) {
                                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                            }
                            for (let i = 1; i < groups.length; i++) {
                                if (groups[i].length !== 3) {
                                    return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                                }
                            }
                            clean = groups.join('');
                        } else {
                            // Exactly 1 dot e.g. 10.50
                            const parts = str.split('.');
                            let before = parts[0] === '' ? '0' : parts[0];
                            const after = parts[1];
                            clean = before + (after !== '' ? '.' + after : '');
                        }
                    } else {
                        clean = str;
                    }

                    if (clean.endsWith('.')) {
                        clean = clean.slice(0, -1);
                    }

                    if (clean === '' || isNaN(Number(clean))) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                    }

                    const val = Number(clean);
                    if (!Number.isFinite(val) || val < 0) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'min' };
                    }

                    let formatted = '';
                    if (clean.includes('.')) {
                        formatted = val.toFixed(2);
                    } else {
                        formatted = String(val);
                    }

                    return {
                        valid: true,
                        value: val,
                        formatted: formatted,
                        numeric_string: val.toFixed(2),
                        clean: clean,
                        error: null
                    };
                },
                getPriceErrorMessage(errorCode) {
                    if (errorCode === 'required') {
                        return '{{ __('shop.validation.price_required') }}';
                    }
                    if (errorCode === 'min') {
                        return '{{ __('shop.validation.price_min') }}';
                    }
                    return '{{ __('shop.validation.price_numeric') }}';
                },
                handlePriceBlur(index) {
                    const row = this.productRows[index];
                    if (!row) return;
                    const raw = row.price !== null && row.price !== undefined ? String(row.price).trim() : '';
                    if (raw === '') return;
                    const res = this.parseAndNormalizePrice(raw);
                    if (!res.valid) {
                        const key = `products.${index}.price`;
                        this.errors = { ...this.errors, [key]: [this.getPriceErrorMessage(res.error)] };
                    } else {
                        this.clearFieldError(index, 'price');
                        row.price = res.formatted;
                        const inputEl = document.querySelector(`input[name="products[${index}][price]"]`);
                        if (inputEl) {
                            inputEl.value = res.formatted;
                        }
                    }
                },
                canAddRow() {
                    const hasBlankNewRow = this.productRows.some((row) => !row.id && !row.product_id);
                    return !hasBlankNewRow;
                },
                canSubmit() {
                    if (this.isEditMode()) return true;
                    return this.productRows.length > 0 && this.productRows.some((r) => r.product_id);
                },
                initSelect2Product(row, index, el = null) {
                    this.$nextTick(() => {
                        const selectEl = el || document.getElementById(`product_id_${row.uid}`);
                        if (!selectEl) return;
                        const $select = $(selectEl);

                        if ($select.hasClass('select2-hidden-accessible')) {
                            $select.select2('destroy');
                        }

                        $select.select2({
                            placeholder: '{{ __('shop.product.select_product') }}',
                            width: '100%',
                            ajax: {
                                url: '{{ route('admin-select-available-shop-products', [], false) }}',
                                dataType: 'json',
                                delay: 250,
                                data: (params) => {
                                    const excludeIds = this.selectedProductIds(row.uid);
                                    return {
                                        shop_id: '{{ $shop->id ?? ($id ?? '') }}',
                                        search: params.term || '',
                                        page: params.page || 1,
                                        per_page: 100,
                                        exclude_ids: JSON.stringify(excludeIds),
                                        current_product_id: row.initial_product_id || ''
                                    };
                                },
                                processResults: (data, params) => {
                                    params.page = params.page || 1;
                                    return {
                                        results: data.results || [],
                                        pagination: {
                                            more: data.pagination ? data.pagination.more : false
                                        }
                                    };
                                },
                                cache: true
                            }
                        }).on('select2:open', function () {
                            if (typeof window.$select2FocusInputSearch === 'function') {
                                window.$select2FocusInputSearch();
                            }
                        }).on('select2:select', (event) => {
                            const item = event.params.data;
                            row.product_id = item.id;
                            this.syncProduct(row, item);
                            this.clearFieldError(index, 'product_id');
                        }).on('change select2:clear select2:unselect', (event) => {
                            const val = $(event.target).val();
                            if (!val) {
                                row.product_id = '';
                                this.syncProduct(row, null);
                            } else if (String(row.product_id) !== String(val)) {
                                row.product_id = val;
                                this.syncProduct(row);
                            }
                        });

                        if (row.product_id) {
                            const displayText = row.product_code
                                ? `${row.product_code} - ${row.product_name}`
                                : (row.product_name || `Product #${row.product_id}`);
                            if ($select.find(`option[value="${row.product_id}"]`).length === 0) {
                                const option = new Option(displayText, row.product_id, true, true);
                                $select.append(option);
                            }
                            $select.val(String(row.product_id)).trigger('change.select2');
                        }
                    });
                },
                resetProduct(row, index) {
                    row.product_id = '';
                    row.product_code = '';
                    this.syncProduct(row, null);
                    this.clearFieldError(index, 'product_id');
                    const selectEl = document.getElementById(`product_id_${row.uid}`);
                    if (selectEl) {
                        $(selectEl).val('').trigger('change').trigger('change.select2');
                    }
                },
                updateDisabledOptions() {
                    // Handled automatically via exclude_ids in Select2 AJAX queries
                },
                addRow() {
                    if (!this.canAddRow()) {
                        return;
                    }
                    this.productRows.push(this.newRow());
                },
                removeRow(index) {
                    const row = this.productRows[index];
                    if (row) {
                        const selectEl = document.getElementById(`product_id_${row.uid}`);
                        if (selectEl && $(selectEl).data('select2')) {
                            $(selectEl).select2('destroy');
                        }
                    }
                    if (this.productRows.length === 1) {
                        this.productRows.splice(index, 1, this.newRow());
                        this.updateDisabledOptions();
                        return;
                    }
                    this.productRows.splice(index, 1);
                    this.updateDisabledOptions();
                },
                productImage(row) {
                    return row.product_image ? this.baseImageUrl + row.product_image : this.defaultImage;
                },
                fieldError(index, field) {
                    const key = `products.${index}.${field}`;
                    return this.errors?.[key]?.[0] ?? '';
                },
                validateForm() {
                    const newErrors = {};
                    let isValid = true;
                    let firstErrorElement = null;

                    if (!this.productRows || this.productRows.length === 0) {
                        newErrors['products'] = ['{{ __('shop.validation.products_required') }}'];
                        isValid = false;
                    }

                    const seenProductIds = new Set();

                    this.productRows.forEach((row, index) => {
                        // 1. Validate Product selection
                        if (!row.product_id) {
                            newErrors[`products.${index}.product_id`] = ['{{ __('shop.validation.product_id_required') }}'];
                            isValid = false;
                            if (!firstErrorElement) {
                                firstErrorElement = document.getElementById(`product_id_${row.uid}`) || document.querySelector(`select[name="products[${index}][product_id]"]`);
                            }
                        } else if (seenProductIds.has(String(row.product_id))) {
                            newErrors[`products.${index}.product_id`] = ['{{ __('shop.validation.product_id_distinct') }}'];
                            isValid = false;
                            if (!firstErrorElement) {
                                firstErrorElement = document.getElementById(`product_id_${row.uid}`) || document.querySelector(`select[name="products[${index}][product_id]"]`);
                            }
                        } else {
                            seenProductIds.add(String(row.product_id));
                        }

                        // 2. Validate Price
                        const priceVal = row.price !== null && row.price !== undefined ? String(row.price).trim() : '';
                        if (priceVal === '') {
                            newErrors[`products.${index}.price`] = ['{{ __('shop.validation.price_required') }}'];
                            isValid = false;
                            if (!firstErrorElement) {
                                firstErrorElement = document.querySelector(`input[name="products[${index}][price]"]`);
                            }
                        } else {
                            const parsedPrice = this.parseAndNormalizePrice(priceVal);
                            if (!parsedPrice.valid) {
                                newErrors[`products.${index}.price`] = [this.getPriceErrorMessage(parsedPrice.error)];
                                isValid = false;
                                if (!firstErrorElement) {
                                    firstErrorElement = document.querySelector(`input[name="products[${index}][price]"]`);
                                }
                            } else {
                                row.price = parsedPrice.numeric_string;
                                const inputEl = document.querySelector(`input[name="products[${index}][price]"]`);
                                if (inputEl) {
                                    inputEl.value = parsedPrice.numeric_string;
                                }
                            }
                        }

                        // 3. Validate Status
                        const statusVal = row.status !== null && row.status !== undefined ? String(row.status).trim() : '';
                        if (statusVal === '' || !['1', '2'].includes(statusVal)) {
                            newErrors[`products.${index}.status`] = ['{{ __('shop.validation.status_invalid') }}'];
                            isValid = false;
                        }

                    });

                    this.errors = newErrors;

                    if (!isValid) {
                        if (firstErrorElement) {
                            if ($(firstErrorElement).data('select2')) {
                                $(firstErrorElement).select2('open');
                            } else {
                                firstErrorElement.focus();
                                firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                        if (window.iziToast) {
                            window.iziToast.warning({
                                title: 'Warning',
                                message: '{{ __('shop.validation.product_id_required') ? __('shop.validation.product_id_required') : 'Please check required fields.' }}'
                            });
                        }
                    }

                    return isValid;
                },
                handleSubmit(event) {
                    if (!this.validateForm()) {
                        event.preventDefault();
                        event.stopPropagation();
                        if (typeof event.stopImmediatePropagation === 'function') {
                            event.stopImmediatePropagation();
                        }
                        return false;
                    }
                },
                clearFieldError(index, field) {
                    const key = `products.${index}.${field}`;
                    if (this.errors && this.errors[key]) {
                        delete this.errors[key];
                        this.errors = { ...this.errors };
                    }
                },
            }));
        });
    </script>
@stop
