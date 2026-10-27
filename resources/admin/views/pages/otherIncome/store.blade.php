@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xOtherIncomeStore">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-' . $routeName . '-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ request('id') ? __('other_income.form.title.update') : __('other_income.form.title.create') }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">

                {{-- Row 1: Category & Supplier --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('other_income.form.category') }} <span>*</span></label>
                        <div class="select2Group">
                            <select name="category_id" id="category_id" class="SelectField select2" required>
                                <option value="">{{ __('other_income.form.select_category') }}</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {!! (request('id') && $data?->category_id == $cat->id) || old('category_id') == $cat->id ? 'selected' : '' !!}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>{{ __('other_income.form.supplier') }}</label>
                        <div class="select2Group">
                            <select name="supplier_id" id="supplier_id" class="SelectField select2" @change="onSupplierChange($event)">
                                <option value="">{{ __('other_income.form.select_supplier') }}</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {!! (request('id') && $data?->supplier_id == $supplier->id) || old('supplier_id', request('supplier_id')) == $supplier->id ? 'selected' : '' !!}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('supplier_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Interactive Supplier Bonus Calculator Toggle & Panel --}}
                <div class="row-1">
                    <div class="calc-panel">
                        <div class="calc-panel-header">
                            <h4>
                                <i class='bx bx-calculator'></i>
                                <span>{{ __('other_income.calculator.title') }}</span>
                            </h4>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="toggleCalculator()">
                                <i class='bx' :class="showCalc ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                <span x-text="showCalc ? 'Hide Calculator' : 'Open Calculator'"></span>
                            </button>
                        </div>

                        <fieldset x-show="showCalc" :disabled="!showCalc" x-cloak>
                            <div class="row g-2 align-items-end mb-3">
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size: 11.5px; font-weight: 600;">{{ __('other_income.calculator.quarter') }}</label>
                                    <select class="form-select form-select-sm" x-model="calc.quarter" @change="onQuarterSelect()">
                                        <option value="">{{ __('other_income.calculator.select_quarter') }}</option>
                                        @foreach ($quarters as $q)
                                            <option value="{{ $q['key'] }}">{{ $q['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size: 11.5px; font-weight: 600;">{{ __('other_income.calculator.from_date') }}</label>
                                    <input type="date" class="form-control form-control-sm" x-model="calc.start_date">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" style="font-size: 11.5px; font-weight: 600;">{{ __('other_income.calculator.to_date') }}</label>
                                    <input type="date" class="form-control form-control-sm" x-model="calc.end_date">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-sm btn-primary w-100" @click="fetchPurchases()" :disabled="calcLoading">
                                        <i class='bx' :class="calcLoading ? 'bx-loader-alt bx-spin' : 'bx-refresh'"></i>
                                        <span x-text="calcLoading ? '{{ __('other_income.calculator.loading') }}' : '{{ __('other_income.calculator.fetch_purchases') }}'"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Calculation Rule Inputs --}}
                            <div class="row g-2 align-items-end mb-2">
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size: 11.5px; font-weight: 600;">{{ __('other_income.calculator.calc_model') }}</label>
                                    <select class="form-select form-select-sm" x-model="calc.model" @change="runCalculation()">
                                        <option value="percentage">{{ __('other_income.calculator.model_percentage') }}</option>
                                        <option value="tiered">{{ __('other_income.calculator.model_tiered') }}</option>
                                        <option value="fixed">{{ __('other_income.calculator.model_fixed') }}</option>
                                        <option value="per_unit">{{ __('other_income.calculator.model_per_unit') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4" x-show="calc.model === 'percentage'">
                                    <label class="form-label" style="font-size: 11.5px; font-weight: 600;">{{ __('other_income.calculator.rate_percent') }}</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.1" min="0" max="100" class="form-control" x-model="calc.rate_or_target" :disabled="calc.model !== 'percentage'" @input="runCalculation()" placeholder="e.g. 2.5">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-4" x-show="calc.model === 'per_unit'">
                                    <label class="form-label" style="font-size: 11.5px; font-weight: 600;">{{ __('other_income.calculator.unit_rate') }}</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" class="form-control" x-model="calc.rate_or_target" :disabled="calc.model !== 'per_unit'" @input="runCalculation()" placeholder="e.g. 0.50">
                                    </div>
                                </div>
                                <div class="col-md-4" x-show="calc.model === 'fixed'">
                                    <label class="form-label" style="font-size: 11.5px; font-weight: 600;">{{ __('other_income.calculator.target_amount') }}</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="100" min="0" class="form-control" x-model="calc.rate_or_target" :disabled="calc.model !== 'fixed'" @input="runCalculation()" placeholder="Target (e.g. 30000)">
                                    </div>
                                </div>
                                <div class="col-md-4" x-show="calc.model === 'fixed'">
                                    <label class="form-label" style="font-size: 11.5px; font-weight: 600;">{{ __('other_income.calculator.fixed_bonus') }}</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="10" min="0" class="form-control" x-model="calc.fixed_bonus" :disabled="calc.model !== 'fixed'" @input="runCalculation()" placeholder="Lump-sum Bonus">
                                    </div>
                                </div>
                            </div>

                            {{-- Live Results Summary Box --}}
                            <div class="calc-result-box" x-show="calc.has_data">
                                <div class="metric">
                                    <div class="lbl">{{ __('other_income.calculator.total_purchases') }}</div>
                                    <div class="val text-primary" x-text="'$' + Number(calc.total_purchases || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></div>
                                </div>
                                <div class="metric">
                                    <div class="lbl">{{ __('other_income.calculator.total_units') }}</div>
                                    <div class="val" x-text="Number(calc.total_qty || 0).toLocaleString()"></div>
                                </div>
                                <div class="metric">
                                    <div class="lbl">{{ __('other_income.calculator.total_deliveries') }}</div>
                                    <div class="val" x-text="calc.deliveries_count || 0"></div>
                                </div>
                                <div class="metric">
                                    <div class="lbl">{{ __('other_income.calculator.suggested_bonus') }}</div>
                                    <div class="val text-success" style="font-size: 19px;" x-text="'$' + Number(calc.suggested_bonus || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></div>
                                </div>
                            </div>

                            <div class="calc-action-bar" x-show="calc.has_data">
                                <span class="text-muted" style="font-size: 12px; margin-right: auto;" x-text="calc.calculation_note"></span>
                                <button type="button" class="btn btn-sm btn-success" @click="applyCalculationToForm()">
                                    <i class='bx bx-check'></i>
                                    <span>{{ __('other_income.calculator.apply_btn') }}</span>
                                </button>
                            </div>
                        </fieldset>
                    </div>
                </div>

                {{-- Row 2: Title & Income Date --}}
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('other_income.form.title_label') }} <span>*</span></label>
                        <input type="text" name="title" id="title_input" value="{!! request('id') ? $data?->title : old('title') !!}" placeholder="{{ __('other_income.form.placeholder_title') }}" required>
                        <i class='bx bx-edit-alt'></i>
                        @error('title')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row iconInput">
                        <label>{{ __('other_income.form.income_date') }} <span>*</span></label>
                        <input type="text" name="income_date" id="income_date" value="{!! request('id') ? ($data?->income_date?->format('Y-m-d') ?? $data?->income_date) : (old('income_date') ?? date('Y-m-d')) !!}" placeholder="YYYY-MM-DD" autocomplete="off" required>
                        <i class='bx bx-calendar'></i>
                        @error('income_date')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Row 3: Received Amount & Shop --}}
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label for="received_amount_input">{{ __('other_income.form.received_amount') }} <span>*</span></label>
                        <input type="number" step="0.01" min="{{ request('id') ? '0' : '0.01' }}" name="received_amount" id="received_amount_input" value="{{ old('received_amount', $data?->received_amount ?? '') }}" placeholder="{{ __('other_income.form.placeholder_amount') }}" required>
                        <i class='bx bx-dollar'></i>
                        @error('received_amount')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>{{ __('other_income.form.shop') }}</label>
                        <div class="select2Group">
                            <select name="shop_id" id="shop_id" class="SelectField select2">
                                <option value="">{{ __('other_income.all_shops') }}</option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}" {!! (request('id') && $data?->shop_id == $shop->id) || old('shop_id') == $shop->id ? 'selected' : '' !!}>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('shop_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Row 4: Payment Method --}}
                @if (!request('id'))
                    <div class="row-1">
                        <div class="form-row">
                            <label>{{ __('other_income.form.payment_method') }}</label>
                            <select name="payment_method" id="payment_method" class="select2">
                                <option value="Cash" {{ old('payment_method', 'Cash') === 'Cash' ? 'selected' : '' }}>{{ __('other_income.payment_methods.cash') }}</option>
                                <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>{{ __('other_income.payment_methods.bank_transfer') }}</option>
                                <option value="ABA Pay" {{ old('payment_method') === 'ABA Pay' ? 'selected' : '' }}>{{ __('other_income.payment_methods.aba') }}</option>
                                <option value="Cheque" {{ old('payment_method') === 'Cheque' ? 'selected' : '' }}>{{ __('other_income.payment_methods.cheque') }}</option>
                                <option value="Credit Note" {{ old('payment_method') === 'Credit Note' ? 'selected' : '' }}>{{ __('other_income.payment_methods.credit_note') }}</option>
                                <option value="Other" {{ old('payment_method') === 'Other' ? 'selected' : '' }}>{{ __('other_income.payment_methods.other') }}</option>
                            </select>
                            @error('payment_method')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- Row 5: Description Remarks & Status --}}
                <div class="row-1">
                    <div class="form-row">
                        <label>{{ __('other_income.form.description') }}</label>
                        <textarea name="description" id="description_input" rows="3" placeholder="{{ __('other_income.form.placeholder_description') }}">{!! request('id') ? $data?->description : old('description') !!}</textarea>
                        @error('description')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                <div class="row-2">
                    <div class="form-row">
                        <label>@lang('global.form.status.label') <span>*</span></label>
                        <select name="status" id="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data?->status == $key) || old('status', 1) == $key ? 'selected' : '' !!}>
                                    {{ $key == 1 ? __('global.form.status.active') : __('global.form.status.disable') }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Action Submit Buttons --}}
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>{{ request('id') ? __('global.button.update') : __('global.button.submit') }}</span>
                    </button>
                    @if (!request('id'))
                        <button type="submit" name="save_opt" value="save_new" color="success">
                            <i data-feather="save"></i>
                            <span>{{ __('global.button.save_new') }}</span>
                        </button>
                    @endif
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
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
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data("xOtherIncomeStore", () => ({
                showCalc: false,
                calcLoading: false,
                calc: {
                    quarter: '',
                    start_date: '',
                    end_date: '',
                    model: 'percentage',
                    rate_or_target: 2.5,
                    fixed_bonus: 0,
                    total_purchases: 0,
                    total_qty: 0,
                    deliveries_count: 0,
                    suggested_bonus: 0,
                    calculation_note: '',
                    has_data: false,
                },

                init() {
                    this.$nextTick(() => {
                        this.initDropdowns();
                        this.initDatepickers();
                    });
                },

                initDropdowns() {
                    const $root = $(this.$root);
                    if ($root.find('#category_id').length) {
                        $root.find('#category_id').select2({ width: '100%' });
                    }
                    if ($root.find('#supplier_id').length) {
                        $root.find('#supplier_id').select2({ width: '100%' });
                    }
                    if ($root.find('#shop_id').length) {
                        $root.find('#shop_id').select2({ width: '100%' });
                    }
                    if ($root.find('#payment_method').length) {
                        $root.find('#payment_method').select2({ width: '100%' });
                    }
                    if ($root.find('#status').length) {
                        $root.find('#status').select2({ width: '100%' });
                    }
                },

                initDatepickers() {
                    if (window.jQuery && $.fn.datepicker && $('#income_date').length) {
                        $("#income_date").datepicker({
                            dateFormat: "yy-mm-dd",
                            changeYear: true,
                            changeMonth: true,
                            gotoCurrent: true,
                            yearRange: "-10:+10",
                            beforeShow: function(input, instance) {
                                instance.dpDiv.addClass('other-income-datepicker');
                            },
                            onClose: function(dateText, instance) {
                                instance.dpDiv.removeClass('other-income-datepicker');
                            }
                        });
                    }
                },

                toggleCalculator() {
                    this.showCalc = !this.showCalc;
                    if (this.showCalc && !this.calc.quarter) {
                        // default to current year and quarter
                        const now = new Date();
                        const q = Math.floor(now.getMonth() / 3) + 1;
                        this.calc.quarter = `${now.getFullYear()}-Q${q}`;
                        this.onQuarterSelect();
                    }
                },

                onQuarterSelect() {
                    if (!this.calc.quarter) return;
                    const parts = this.calc.quarter.split('-Q');
                    if (parts.length === 2) {
                        const year = parseInt(parts[0], 10);
                        const q = parseInt(parts[1], 10);
                        const ranges = {
                            1: ['01-01', '03-31'],
                            2: ['04-01', '06-30'],
                            3: ['07-01', '09-30'],
                            4: ['10-01', '12-31'],
                        };
                        this.calc.start_date = `${year}-${ranges[q][0]}`;
                        this.calc.end_date = `${year}-${ranges[q][1]}`;
                    }
                },

                onSupplierChange(e) {
                    if (this.showCalc) {
                        this.fetchPurchases();
                    }
                },

                async fetchPurchases() {
                    const supplierId = $(this.$root).find('#supplier_id').val();
                    if (!supplierId) {
                        alert('Please select a supplier first.');
                        return;
                    }

                    this.calcLoading = true;
                    try {
                        const params = new URLSearchParams({
                            supplier_id: supplierId,
                            quarter: this.calc.quarter || '',
                            from_date: this.calc.start_date || '',
                            to_date: this.calc.end_date || '',
                            model: this.calc.model,
                            rate_or_target: this.calc.rate_or_target,
                            fixed_bonus: this.calc.fixed_bonus,
                        });

                        const res = await fetch(`{{ route('admin-other-income-calculate-purchases') }}?${params.toString()}`);
                        const data = await res.json();

                        if (res.ok && data.status === 'success') {
                            this.calc.total_purchases  = data.purchases.total_purchases;
                            this.calc.total_qty        = data.purchases.total_qty;
                            this.calc.deliveries_count = data.purchases.deliveries_count;
                            this.calc.suggested_bonus  = data.bonus.suggested_bonus;
                            this.calc.calculation_note = data.bonus.calculation_note;
                            this.calc.has_data         = true;
                        } else {
                            alert(data.message || 'Failed to calculate purchases.');
                        }
                    } catch (err) {
                        alert('Network or server error while querying purchases.');
                    } finally {
                        this.calcLoading = false;
                    }
                },

                runCalculation() {
                    const purchases = Number(this.calc.total_purchases || 0);
                    const qty = Number(this.calc.total_qty || 0);
                    let bonus = 0;
                    let note = '';

                    switch (this.calc.model) {
                        case 'tiered':
                            let rate = 0;
                            if (purchases >= 100000) rate = 5.0;
                            else if (purchases >= 50000) rate = 3.5;
                            else if (purchases >= 20000) rate = 2.0;
                            bonus = (purchases * rate) / 100.0;
                            note = `Tiered Rebate: Purchases $${purchases.toFixed(2)} @ ${rate}% = $${bonus.toFixed(2)}`;
                            break;
                        case 'fixed':
                            const target = Number(this.calc.rate_or_target || 0);
                            const fixed = Number(this.calc.fixed_bonus || 0);
                            if (purchases >= target) {
                                bonus = fixed;
                                note = `Target Achieved: $${purchases.toFixed(2)} >= $${target.toFixed(2)}. Flat Bonus = $${bonus.toFixed(2)}`;
                            } else {
                                bonus = 0;
                                note = `Target Not Met: $${purchases.toFixed(2)} < $${target.toFixed(2)}. Bonus = $0.00`;
                            }
                            break;
                        case 'per_unit':
                            const unitRate = Number(this.calc.rate_or_target || 0);
                            bonus = qty * unitRate;
                            note = `Incentive: ${qty} units @ $${unitRate.toFixed(2)}/unit = $${bonus.toFixed(2)}`;
                            break;
                        case 'percentage':
                        default:
                            const pRate = Number(this.calc.rate_or_target || 0);
                            bonus = (purchases * pRate) / 100.0;
                            note = `Volume Bonus: $${purchases.toFixed(2)} purchases @ ${pRate}% = $${bonus.toFixed(2)}`;
                            break;
                    }

                    this.calc.suggested_bonus = Math.round(bonus * 100) / 100;
                    this.calc.calculation_note = note;
                },

                applyCalculationToForm() {
                    const receivedInput = this.$root.querySelector('#received_amount_input');
                    if (receivedInput) {
                        receivedInput.value = Number(this.calc.suggested_bonus || 0).toFixed(2);
                    }

                    const titleInput = document.getElementById('title_input');
                    const supplierSelect = document.getElementById('supplier_id');
                    const supplierName = supplierSelect && supplierSelect.selectedOptions[0] ? supplierSelect.selectedOptions[0].text : '';

                    if (titleInput && (!titleInput.value || titleInput.value.trim() === '')) {
                        titleInput.value = `${this.calc.quarter || 'Periodic'} Purchase Rebate - ${supplierName}`.trim();
                    }

                    const descInput = document.getElementById('description_input');
                    if (descInput) {
                        const noteText = `Calculation Basis: Total purchases $${Number(this.calc.total_purchases).toFixed(2)} (${this.calc.deliveries_count} inward deliveries, ${this.calc.total_qty} units). ${this.calc.calculation_note}`;
                        descInput.value = descInput.value ? (descInput.value + "\n" + noteText) : noteText;
                    }

                    alert(@json(__('other_income.message.received_amount_applied')).replace(':amount', Number(this.calc.suggested_bonus).toFixed(2)));
                }
            }));
        });
    </script>
@stop
