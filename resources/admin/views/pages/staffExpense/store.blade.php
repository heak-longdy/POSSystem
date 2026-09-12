@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-' . $routeName . '-save', request('id')) !!}" method="POST">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ request('id') ? __('staff_expense.form.title.update') : __('staff_expense.form.title.create') }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">

                {{-- Staff Member & Expense Type --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('staff_expense.form.staff') }} <span>*</span></label>
                        <select name="staff_id" id="staff_id" class="select2">
                            <option value="">{{ __('staff_expense.form.select_staff') }}</option>
                            @foreach ($staffList as $staff)
                                <option value="{{ $staff->id }}" {!! (request('id') && $data?->staff_id == $staff->id) || old('staff_id') == $staff->id ? 'selected' : '' !!}>
                                    {{ $staff->name }} {{ $staff->phone_number ? '('.$staff->phone_number.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('staff_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>{{ __('staff_expense.form.type') }} <span>*</span></label>
                        <select name="type" id="type" class="select2">
                            <option value="">{{ __('staff_expense.form.select_type') }}</option>
                            @foreach ($types as $type)
                                @php
                                    $typeVal = is_object($type) ? $type->value : $type;
                                    $typeLabel = is_object($type) ? $type->label() : $type;
                                    $selected = (request('id') && (is_object($data?->type) ? $data?->type->value : $data?->type) == $typeVal) || old('type') == $typeVal;
                                @endphp
                                <option value="{{ $typeVal }}" {!! $selected ? 'selected' : '' !!}>
                                    {{ $typeLabel }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Shop / Branch & Expense Date --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('staff_expense.form.shop') }}</label>
                        <select name="shop_id" id="shop_id" class="select2">
                            <option value="">{{ __('staff_expense.form.all_shops') }}</option>
                            @foreach ($shops as $shop)
                                <option value="{{ $shop->id }}" {!! (request('id') && $data?->shop_id == $shop->id) || old('shop_id') == $shop->id ? 'selected' : '' !!}>
                                    {{ $shop->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('shop_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row iconInput">
                        <label>{{ __('staff_expense.form.expense_date') }} <span>*</span></label>
                        <input type="text" name="expense_date" id="expense_date" value="{!! request('id') ? ($data?->expense_date?->format('Y-m-d') ?? $data?->expense_date) : (old('expense_date') ?? date('Y-m-d')) !!}" placeholder="{{ __('staff_expense.form.placeholder_date') }}" autocomplete="off">
                        <i class='bx bx-calendar'></i>
                        @error('expense_date')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Amount ($) & Status --}}
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('staff_expense.form.amount') }} <span>*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{!! request('id') ? $data?->amount : (old('amount') ?? '') !!}" placeholder="{{ __('staff_expense.form.placeholder_amount') }}">
                        <i class='bx bx-dollar'></i>
                        @error('amount')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>@lang('global.form.status.label') <span>*</span></label>
                        <select name="status" id="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data?->status == $key) || old('status', 1) == $key ? 'selected' : '' !!}>{{ $key == 1 ? __('global.form.status.active') : __('global.form.status.disable') }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Description / Remarks --}}
                <div class="row-1">
                    <div class="form-row">
                        <label>{{ __('staff_expense.form.description') }}</label>
                        <textarea name="description" rows="3" placeholder="{{ __('staff_expense.form.placeholder_description') }}">{!! request('id') ? $data?->description : old('description') !!}</textarea>
                        @error('description')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Form Submit Buttons --}}
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
            Alpine.data("xComponent", () => ({
                init() {
                    if ($('#staff_id').length) {
                        $('#staff_id').select2();
                    }
                    if ($('#type').length) {
                        $('#type').select2();
                    }
                    if ($('#shop_id').length) {
                        $('#shop_id').select2();
                    }
                    if ($('#status').length) {
                        $('#status').select2();
                    }
                    if ($('#expense_date').length) {
                        $("#expense_date").datepicker({
                            changeYear: true,
                            gotoCurrent: true,
                            yearRange: "-10:+10",
                            dateFormat: "yy-mm-dd"
                        });
                    }
                }
            }));
        });
    </script>
@stop
