@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-' . $routeName . '-save', request('id')) !!}" method="POST">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ request('id') ? __('other_income.category.title') . ' - ' . __('global.button.update') : __('other_income.category.create') }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('other_income.category.name') }} <span>*</span></label>
                        <input type="text" name="name" value="{!! request('id') ? $data?->name : old('name') !!}" placeholder="e.g. Supplier Volume Bonus" required>
                        <i class='bx bx-font-family'></i>
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row iconInput">
                        <label>{{ __('other_income.category.code') }} <span>*</span></label>
                        <input type="text" name="code" value="{!! request('id') ? $data?->code : old('code') !!}" placeholder="e.g. supplier_bonus" required {!! request('id') && $data?->is_system ? 'readonly' : '' !!}>
                        <i class='bx bx-code'></i>
                        @error('code')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('other_income.category.requires_supplier') }}</label>
                        <select name="requires_supplier" id="requires_supplier">
                            <option value="0" {!! (request('id') && !$data?->requires_supplier) || old('requires_supplier') == '0' ? 'selected' : '' !!}>{{ __('global.option.no') }}</option>
                            <option value="1" {!! (request('id') && $data?->requires_supplier) || old('requires_supplier') == '1' ? 'selected' : '' !!}>{{ __('global.option.yes') }}</option>
                        </select>
                        @error('requires_supplier')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row iconInput">
                        <label>{{ __('other_income.category.ordering') }}</label>
                        <input type="number" name="ordering" value="{!! request('id') ? $data?->ordering : (old('ordering') ?? 0) !!}">
                        <i class='bx bx-sort'></i>
                        @error('ordering')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                <div class="row-1">
                    <div class="form-row">
                        <label>{{ __('other_income.category.description') }}</label>
                        <textarea name="description" rows="3" placeholder="Description of this income category...">{!! request('id') ? $data?->description : old('description') !!}</textarea>
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
                    if ($(this.$root).find('#requires_supplier').length) {
                        $(this.$root).find('#requires_supplier').select2();
                    }
                    if ($(this.$root).find('#status').length) {
                        $(this.$root).find('#status').select2();
                    }
                }
            }));
        });
    </script>
@stop
