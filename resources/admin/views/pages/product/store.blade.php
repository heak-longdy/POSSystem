@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xProductStore">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-'.$routeName.'-save', request('id')) !!}" method="POST" enctype="multipart/form-data" @submit="handleSubmit($event)">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ $id ? __('product.form.title.update') : __('product.form.title.create') }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('product.form.category.label') }}<span>*</span></label>
                        <div class="select2Group">
                            <select name="category_id" class="SelectCategory" id="category_id" x-init="fetchSelectCategory()">
                                <option value=""> {{ __('product.form.category.placeholder') }}</option>
                            </select>
                            <div class="select2Reset" x-show="category?.id" @click="$select2Data($($root).find('#category_id')); category = {id: '', text: ''}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path
                                        d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" x-model="category.text" name="category_text">
                        <template x-if="clientErrors?.category_id">
                            <label class="error" x-text="clientErrors.category_id"></label>
                        </template>
                        @error('category_id')
                            <label class="error" x-show="!clientErrors?.category_id">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>{{ __('product.form.uom.label') }}<span>*</span></label>
                        <div class="select2Group">
                            <select name="uom_id" class="SelectUom" id="uom_id" x-init="fetchSelectUOM()">
                                <option value=""> {{ __('product.form.uom.placeholder') }}</option>
                            </select>
                            <div class="select2Reset" x-show="uom?.id" @click="$select2Data($($root).find('#uom_id')); uom = {id: '', text: ''}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path
                                        d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" x-model="uom.text" name="uom_text">
                        <template x-if="clientErrors?.uom_id">
                            <label class="error" x-text="clientErrors.uom_id"></label>
                        </template>
                        @error('uom_id')
                            <label class="error" x-show="!clientErrors?.uom_id">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('product.form.code.label') }}</label>
                        <input type="text" name="code" x-model="code" placeholder="{{ __('product.form.code.placeholder') }}" @input="delete clientErrors.code">
                        <i class='bx bx-barcode'></i>
                        <template x-if="clientErrors?.code">
                            <label class="error" x-text="clientErrors.code"></label>
                        </template>
                        @error('code')
                            <label class="error" x-show="!clientErrors?.code">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('product.form.name.label') }} <span>*</span> </label>
                        <input type="text" name="name" x-model="name" placeholder="{{ __('product.form.name.placeholder') }}" @input="delete clientErrors.name">
                        <i class='bx bx-font-family'></i>
                        <template x-if="clientErrors?.name">
                            <label class="error" x-text="clientErrors.name"></label>
                        </template>
                        @error('name')
                            <label class="error" x-show="!clientErrors?.name">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('product.form.cost.label') }} <span>*</span></label>
                        <input type="text" inputmode="decimal" name="cost" x-model="cost" placeholder="{{ __('product.form.cost.placeholder') }}" @input="handlePriceInput('cost')" @blur="handlePriceBlur('cost')">
                        <i class='bx bx-dollar'></i>
                        <template x-if="clientErrors?.cost">
                            <label class="error" x-text="clientErrors.cost"></label>
                        </template>
                        @error('cost')
                            <label class="error" x-show="!clientErrors?.cost">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('product.form.price.label') }} <span>*</span></label>
                        <input type="text" inputmode="decimal" name="price" x-model="price" placeholder="{{ __('product.form.price.placeholder') }}" @input="handlePriceInput('price')" @blur="handlePriceBlur('price')">
                        <i class='bx bx-dollar'></i>
                        <template x-if="clientErrors?.price">
                            <label class="error" x-text="clientErrors.price"></label>
                        </template>
                        @error('price')
                            <label class="error" x-show="!clientErrors?.price">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('product.form.status.label') }}<span>*</span></label>
                        <select name="status" id="status" x-model="status">
                            <option value="1">{{ __('product.form.status.active') }}</option>
                            <option value="2">{{ __('product.form.status.disable') }}</option>
                        </select>
                        @error('status')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>{{ __('product.form.photo.label') }}</label>
                        <div class="form-select-photo image" @click="selectImage(event)">
                            <div class="select-photo" :class='{ active: image }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>{{ __('product.form.photo.placeholder') }}</p>
                                </div>
                            </div>
                            <template x-if="image">
                                <div class="image-view active">
                                    <img x-bind:src="baseImageUrl + image" alt="">
                                </div>
                            </template>
                            <input type="hidden" x-model="image" name="image" autocomplete="off" role="presentation">
                        </div>
                        @error('image')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>{{ __('global.button.submit') }}</span>
                    </button>
                    <button type="submit" name="save_opt" value="save_new" color="success">
                        <i data-feather="save"></i>
                        <span>{{ __('global.button.save_new') }}</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>{{ __('global.button.cancel') }}</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
    @include('admin::file-manager.popup')
@stop

@section('script')
    <script>
        (function() {
            const registerProductStore = () => {
                Alpine.data("xProductStore", () => ({
                    baseImageUrl: "{{ asset('file_manager') }}",
                    image: "{!! request('id') ? ($data?->image ?? '') : (old('image') ?? '') !!}",
                    code: "{!! request('id') ? addslashes($data?->code ?? '') : addslashes(old('code') ?? '') !!}",
                    name: "{!! request('id') ? addslashes($data?->name ?? '') : addslashes(old('name') ?? '') !!}",
                    cost: "{!! request('id') ? ($data?->cost ?? '') : (old('cost') ?? '') !!}",
                    price: "{!! request('id') ? ($data?->price ?? '') : (old('price') ?? '') !!}",
                    status: "{!! request('id') ? ($data?->status ?? '1') : (old('status') ?? '1') !!}",
                    category: {
                        id: "{!! request('id') ? ($data?->category_id ?? '') : (old('category_id') ?? '') !!}",
                        text: "{!! request('id') ? addslashes($data?->category_title ?? '') : addslashes(old('category_text') ?? '') !!}"
                    },
                    uom: {
                        id: "{!! request('id') ? ($data?->uom_id ?? '') : (old('uom_id') ?? '') !!}",
                        text: "{!! request('id') ? addslashes($data?->uom_title ?? '') : addslashes(old('uom_text') ?? '') !!}"
                    },
                    clientErrors: {},
                    priceTranslations: {
                        price_required: @json(__('product.validation.price_required')),
                        price_numeric: @json(__('product.validation.price_numeric')),
                        price_min: @json(__('product.validation.price_min')),
                        cost_required: @json(__('product.validation.cost_required')),
                        cost_numeric: @json(__('product.validation.cost_numeric')),
                        cost_min: @json(__('product.validation.cost_min')),
                    },

                    init() {
                        $(this.$root).find('#status').select2();
                        if (this.price !== null && this.price !== undefined && String(this.price).trim() !== '') {
                            const p = this.parseAndNormalizePrice(this.price);
                            if (p.valid) this.price = p.formatted;
                        }
                        if (this.cost !== null && this.cost !== undefined && String(this.cost).trim() !== '') {
                            const c = this.parseAndNormalizePrice(this.cost);
                            if (c.valid) this.cost = c.formatted;
                        }
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

                    getPriceErrorMessage(field, errorCode) {
                        if (errorCode === 'required') {
                            return this.priceTranslations[field + '_required'] || (field === 'price' ? 'Price is required' : 'Cost is required');
                        }
                        if (errorCode === 'min') {
                            return this.priceTranslations[field + '_min'] || (field === 'price' ? 'Price must be greater than or equal to 0.' : 'Cost must be greater than or equal to 0.');
                        }
                        return this.priceTranslations[field + '_numeric'] || (field === 'price' ? 'Price format invalid' : 'Cost format invalid');
                    },

                    handlePriceInput(field) {
                        delete this.clientErrors[field];
                    },

                    handlePriceBlur(field) {
                        const raw = (this[field] !== null && this[field] !== undefined) ? String(this[field]).trim() : '';
                        if (raw === '') {
                            return;
                        }
                        const res = this.parseAndNormalizePrice(raw);
                        if (!res.valid) {
                            this.clientErrors[field] = this.getPriceErrorMessage(field, res.error);
                        } else {
                            delete this.clientErrors[field];
                            this[field] = res.formatted;
                            const inputEl = $(this.$root).find(`input[name="${field}"]`)[0];
                            if (inputEl) {
                                inputEl.value = res.formatted;
                            }
                        }
                    },

                    fetchSelectCategory() {
                        const $cat = $(this.$root).find(`#category_id`);
                        $cat.select2({
                            placeholder: `{{ __('product.form.category.placeholder') }}`,
                            ajax: {
                                url: '{{ route('admin-select-category') }}',
                                dataType: 'json',
                                type: "GET",
                                quietMillis: 50,
                                data: (param) => ({
                                    search: param.term
                                }),
                                processResults: (data) => ({
                                    results: $.map(data.data, (item) => ({
                                        text: item?.name || '',
                                        id: item.id
                                    }))
                                }),
                                error: (xhr, status, error) => {
                                    console.error('Error fetching categories:', error);
                                }
                            }
                        }).on('select2:select', (event) => {
                            this.category = {
                                id: event.params.data.id,
                                text: event.params.data.text
                            };
                            delete this.clientErrors.category_id;
                        }).on('change', (e) => {
                            if (!$(e.target).val()) {
                                this.category = { id: '', text: '' };
                            }
                        }).on('select2:open', () => {
                            $select2FocusInputSearch();
                        });

                        if (this.category.id && this.category.text) {
                            $select2Data($(this.$root).find('#category_id'), this.category.id, this.category.text);
                        }
                    },

                    fetchSelectUOM() {
                        const $uom = $(this.$root).find(`#uom_id`);
                        $uom.select2({
                            placeholder: `{{ __('product.form.uom.placeholder') }}`,
                            ajax: {
                                url: '{{ route('admin-select-uom') }}',
                                dataType: 'json',
                                type: "GET",
                                quietMillis: 50,
                                data: (param) => ({
                                    search: param.term
                                }),
                                processResults: (data) => ({
                                    results: $.map(data.data, (item) => ({
                                        text: item?.name || '',
                                        id: item.id
                                    }))
                                })
                            }
                        }).on('select2:select', (event) => {
                            this.uom = {
                                id: event.params.data.id,
                                text: event.params.data.text
                            };
                            delete this.clientErrors.uom_id;
                        }).on('change', (e) => {
                            if (!$(e.target).val()) {
                                this.uom = { id: '', text: '' };
                            }
                        }).on('select2:open', () => {
                            $select2FocusInputSearch();
                        });

                        if (this.uom.id && this.uom.text) {
                            $select2Data($(this.$root).find('#uom_id'), this.uom.id, this.uom.text);
                        }
                    },

                    selectImage() {
                        fileManager({
                            multiple: false,
                            afterClose: (data, basePath) => {
                                if (data?.length > 0) {
                                    this.image = data[0].path;
                                }
                            }
                        });
                    },

                    validateForm() {
                        const errors = {};
                        let isValid = true;
                        let firstErrorEl = null;

                        if (!this.category.id) {
                            errors.category_id = @json(__('product.validation.category_required'));
                            isValid = false;
                            if (!firstErrorEl) firstErrorEl = $(this.$root).find('#category_id');
                        }

                        if (!this.uom.id) {
                            errors.uom_id = @json(__('product.validation.uom_required'));
                            isValid = false;
                            if (!firstErrorEl) firstErrorEl = $(this.$root).find('#uom_id');
                        }

                        const codeVal = (this.code !== null && this.code !== undefined) ? String(this.code).trim() : '';
                        if (codeVal.length > 50) {
                            errors.code = @json(__('product.validation.code_max'));
                            isValid = false;
                            if (!firstErrorEl) firstErrorEl = $('input[name="code"]');
                        }

                        const nameVal = (this.name !== null && this.name !== undefined) ? String(this.name).trim() : '';
                        if (!nameVal) {
                            errors.name = @json(__('product.validation.name_required'));
                            isValid = false;
                            if (!firstErrorEl) firstErrorEl = $('input[name="name"]');
                        } else if (nameVal.length > 50) {
                            errors.name = @json(__('product.validation.name_max'));
                            isValid = false;
                            if (!firstErrorEl) firstErrorEl = $('input[name="name"]');
                        }

                        const costVal = (this.cost !== null && this.cost !== undefined) ? String(this.cost).trim() : '';
                        if (costVal === '') {
                            errors.cost = this.priceTranslations.cost_required;
                            isValid = false;
                            if (!firstErrorEl) firstErrorEl = $('input[name="cost"]');
                        } else {
                            const parsedCost = this.parseAndNormalizePrice(costVal);
                            if (!parsedCost.valid) {
                                errors.cost = this.getPriceErrorMessage('cost', parsedCost.error);
                                isValid = false;
                                if (!firstErrorEl) firstErrorEl = $('input[name="cost"]');
                            } else {
                                this.cost = parsedCost.numeric_string;
                                const costInput = $(this.$root).find('input[name="cost"]')[0];
                                if (costInput) {
                                    costInput.value = parsedCost.numeric_string;
                                }
                            }
                        }

                        const priceVal = (this.price !== null && this.price !== undefined) ? String(this.price).trim() : '';
                        if (priceVal === '') {
                            errors.price = this.priceTranslations.price_required;
                            isValid = false;
                            if (!firstErrorEl) firstErrorEl = $('input[name="price"]');
                        } else {
                            const parsedPrice = this.parseAndNormalizePrice(priceVal);
                            if (!parsedPrice.valid) {
                                errors.price = this.getPriceErrorMessage('price', parsedPrice.error);
                                isValid = false;
                                if (!firstErrorEl) firstErrorEl = $('input[name="price"]');
                            } else {
                                this.price = parsedPrice.numeric_string;
                                const priceInput = $(this.$root).find('input[name="price"]')[0];
                                if (priceInput) {
                                    priceInput.value = parsedPrice.numeric_string;
                                }
                            }
                        }

                        this.clientErrors = errors;

                        if (!isValid) {
                            if (firstErrorEl) {
                                if (firstErrorEl.is && firstErrorEl.is('select')) {
                                    firstErrorEl.select2('open');
                                } else if (firstErrorEl.focus) {
                                    firstErrorEl.focus();
                                }
                            }
                            if (window.iziToast) {
                                window.iziToast.warning({
                                    title: 'Warning',
                                    message: @json(__('product.validation.category_required')) || 'Please check required fields.'
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
                    }
                }));
            };

            if (window.Alpine) {
                registerProductStore();
            } else {
                document.addEventListener('alpine:init', registerProductStore);
            }
        })();
    </script>
@stop
