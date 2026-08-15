@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-' . $routeName . '-save', request('id')) !!}" method="POST">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ request('id') ? 'Update Staff Expense' : 'Record Staff Expense' }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">

                {{-- Staff Member & Expense Type --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>Staff Member <span>*</span></label>
                        <select name="staff_id" id="staff_id" class="select2">
                            <option value="">-- Select Staff Member --</option>
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
                        <label>Expense Type <span>*</span></label>
                        <select name="type" id="type" class="select2">
                            <option value="">-- Select Expense Type --</option>
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
                        <label>Shop / Branch</label>
                        <select name="shop_id" id="shop_id" class="select2">
                            <option value="">-- All Shops / General --</option>
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
                        <label>Expense Date <span>*</span></label>
                        <input type="text" name="expense_date" id="expense_date" value="{!! request('id') ? ($data?->expense_date?->format('Y-m-d') ?? $data?->expense_date) : (old('expense_date') ?? date('Y-m-d')) !!}" placeholder="YYYY-MM-DD" autocomplete="off">
                        <i class='bx bx-calendar'></i>
                        @error('expense_date')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Amount ($) & Status --}}
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Amount ($) <span>*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{!! request('id') ? $data?->amount : (old('amount') ?? '') !!}" placeholder="0.00">
                        <i class='bx bx-dollar'></i>
                        @error('amount')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>@lang('adminGlobal.form.status.label') <span>*</span></label>
                        <select name="status" id="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data?->status == $key) || old('status', 1) == $key ? 'selected' : '' !!}>{{ $item }}</option>
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
                        <label>Description / Remarks</label>
                        <textarea name="description" rows="3" placeholder="Enter notes or explanation for this expense ...">{!! request('id') ? $data?->description : old('description') !!}</textarea>
                        @error('description')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Form Submit Buttons --}}
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button type="submit" name="save_opt" value="save_new" color="success">
                        <i data-feather="save"></i>
                        <span>Save & New</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
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
