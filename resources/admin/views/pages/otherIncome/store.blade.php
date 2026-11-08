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
                            <select name="category_id" id="category_id" class="SelectField select2" >
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
                            <select name="supplier_id" id="supplier_id" class="SelectField select2">
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

                {{-- Row 2: Title & Income Date --}}
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('other_income.form.title_label') }}</label>
                        <input type="text" name="title" id="title_input" value="{!! request('id') ? $data?->title : old('title') !!}" placeholder="{{ __('other_income.form.placeholder_title') }}">
                        <i class='bx bx-edit-alt'></i>
                        @error('title')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row iconInput">
                        <label>{{ __('other_income.form.income_date') }} <span>*</span></label>
                        <input type="text" name="income_date" id="income_date" value="{!! request('id') ? ($data?->income_date?->format('Y-m-d') ?? $data?->income_date) : (old('income_date') ?? date('Y-m-d')) !!}" placeholder="YYYY-MM-DD" autocomplete="off" >
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
                        <input type="number" step="0.01" min="{{ request('id') ? '0' : '0.01' }}" name="received_amount" id="received_amount_input" value="{{ old('received_amount', $data?->received_amount ?? '') }}" placeholder="{{ __('other_income.form.placeholder_amount') }}" >
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
                }
            }));
        });
    </script>
@stop
