@extends('admin::shared.layout')
@section('layout')
    <style>
        .icon-input-wrap {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
        }
        .icon-input-wrap input {
            background-color: #fff;
            border-radius: 7px;
            border: 1px solid #d8dce5;
            box-sizing: border-box;
            color: #5a5e66;
            display: inline-block;
            font-size: 14px;
            height: 43px;
            line-height: 1;
            outline: 0;
            padding: 0 40px 0 15px !important;
            transition: border-color 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
            width: 100%;
        }
        .icon-input-wrap input:focus {
            border-color: #409eff;
            outline: 0;
        }
        .icon-input-wrap input::placeholder {
            color: #b7bac1;
        }
        .icon-input-wrap input::-webkit-outer-spin-button,
        .icon-input-wrap input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .icon-input-wrap input[type=number] {
            -moz-appearance: textfield;
        }
        .icon-input-wrap i,
        .icon-input-wrap svg {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #5a5e66a3;
            font-size: 22px;
            line-height: 1;
            pointer-events: none;
            margin: 0;
        }
    </style>
    @include('admin::shared.header', ['header_name' => __('setting.invoice.title'), 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="invoiceConfig()">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{{ route('admin-setting-invoice-save') }}" method="POST">
            @csrf
            <div class="form-header">
                <h3>
                    <i data-feather="settings"></i>
                    {{ __('setting.invoice.title') }}
                </h3>
            </div>

            <div class="form-body">
                {{-- Live Interactive Preview Card --}}
                <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 12px; padding: 22px; color: #fff; margin-bottom: 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); position: relative; overflow: hidden;">
                    <div style="position: absolute; right: -15px; bottom: -15px; opacity: 0.08;">
                        <i class='bx bx-receipt' style="font-size: 130px;"></i>
                    </div>
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 6px; font-weight: 600;">
                        {{ __('setting.invoice.preview') }}
                    </div>
                    <div style="display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap;">
                        <span x-text="computedPreview()" style="font-family: monospace; font-size: 30px; font-weight: 700; color: #38bdf8; letter-spacing: 1px; background: rgba(56, 189, 248, 0.12); padding: 4px 14px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3);">
                            {{ $preview }}
                        </span>
                        <span style="font-size: 13px; color: #cbd5e1;">
                            {{ __('setting.invoice.preview_desc') }}
                        </span>
                    </div>
                </div>

                {{-- Prefix and Separator --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('setting.invoice.prefix') }} <span>*</span></label>
                        <div class="icon-input-wrap">
                            <input type="text" name="prefix" x-model="prefix" placeholder="{{ __('setting.invoice.prefix_placeholder') }}" maxlength="20" required>
                            <i class='bx bx-font-family'></i>
                        </div>
                        <small style="color: #64748b; font-size: 11px; margin-top: 4px; display: block;">{{ __('setting.invoice.prefix_hint') }}</small>
                        @error('prefix')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>{{ __('setting.invoice.separator') }}</label>
                        <select name="separator" id="separator" x-model="separator">
                            <option value="-">{{ __('setting.invoice.separator_hyphen') }}</option>
                            <option value="/">{{ __('setting.invoice.separator_slash') }}</option>
                            <option value="_">{{ __('setting.invoice.separator_underscore') }}</option>
                            <option value="">{{ __('setting.invoice.separator_none') }}</option>
                        </select>
                        @error('separator')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Date Stamp and Digit Length --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('setting.invoice.date_format') }} <span>*</span></label>
                        <select name="date_format" id="date_format" x-model="dateFormat">
                            <option value="none">{{ __('setting.invoice.date_none') }}</option>
                            <option value="Y">{{ __('setting.invoice.date_year') }}</option>
                            <option value="Ym">{{ __('setting.invoice.date_year_month') }}</option>
                            <option value="Y-m">{{ __('setting.invoice.date_year_month_dash') }}</option>
                            <option value="Ymd">{{ __('setting.invoice.date_full') }}</option>
                        </select>
                        @error('date_format')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>{{ __('setting.invoice.digit_length') }} <span>*</span></label>
                        <select name="digit_length" id="digit_length" x-model="digitLength">
                            <option value="3">3 digits (001)</option>
                            <option value="4">4 digits (0001)</option>
                            <option value="5">5 digits (00001)</option>
                            <option value="6">6 digits (000001)</option>
                            <option value="7">7 digits (0000001)</option>
                            <option value="8">8 digits (00000001)</option>
                        </select>
                        <small style="color: #64748b; font-size: 11px; margin-top: 4px; display: block;">{{ __('setting.invoice.digit_hint') }}</small>
                        @error('digit_length')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Start Number and Reset Cycle --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('setting.invoice.start_number') }} <span>*</span></label>
                        <div class="icon-input-wrap">
                            <input type="number" name="start_number" x-model.number="startNumber" min="1" max="999999" required>
                            <i class='bx bx-hash'></i>
                        </div>
                        <small style="color: #64748b; font-size: 11px; margin-top: 4px; display: block;">{{ __('setting.invoice.start_number_hint') }}</small>
                        @error('start_number')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>{{ __('setting.invoice.reset_cycle') }} <span>*</span></label>
                        <select name="reset_cycle" id="reset_cycle" x-model="resetCycle">
                            <option value="never">{{ __('setting.invoice.reset_never') }}</option>
                            <option value="yearly">{{ __('setting.invoice.reset_yearly') }}</option>
                            <option value="monthly">{{ __('setting.invoice.reset_monthly') }}</option>
                            <option value="daily">{{ __('setting.invoice.reset_daily') }}</option>
                        </select>
                        @error('reset_cycle')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Reset Active Counter Checkbox --}}
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 14px 18px; margin: 15px 0 25px 0; display: flex; align-items: flex-start; gap: 12px;">
                    <input type="checkbox" name="reset_active_counter" id="reset_active_counter" value="1" style="width: 18px; height: 18px; margin-top: 2px; cursor: pointer;">
                    <label for="reset_active_counter" style="margin: 0; cursor: pointer;">
                        <span style="font-weight: 600; color: #334155; font-size: 14px;">{{ __('setting.invoice.reset_counter') }}</span>
                        <small style="color: #64748b; display: block; font-size: 12px; margin-top: 2px;">{{ __('setting.invoice.reset_counter_desc') }}</small>
                    </label>
                </div>

                {{-- Action Buttons --}}
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>{{ __('global.button.save') }}</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop

@section('script')
    <script>
        function invoiceConfig() {
            return {
                prefix: @json($setting->prefix ?? 'NO'),
                separator: @json($setting->separator ?? '-'),
                dateFormat: @json($setting->date_format ?? 'none'),
                digitLength: {{ (int) ($setting->digit_length ?? 4) }},
                startNumber: {{ (int) ($setting->start_number ?? 1) }},
                resetCycle: @json($setting->reset_cycle ?? 'never'),

                init() {
                    const self = this;
                    if ($.fn.select2) {
                        $('#separator, #date_format, #digit_length, #reset_cycle').select2();
                    }
                    $('#separator').val(this.separator).on('change', function() {
                        self.separator = this.value;
                    });
                    $('#date_format').val(this.dateFormat).on('change', function() {
                        self.dateFormat = this.value;
                    });
                    $('#digit_length').val(this.digitLength).on('change', function() {
                        self.digitLength = parseInt(this.value, 10);
                    });
                    $('#reset_cycle').val(this.resetCycle).on('change', function() {
                        self.resetCycle = this.value;
                    });
                },

                computedPreview() {
                    const now = new Date();
                    const year = now.getFullYear().toString();
                    const month = (now.getMonth() + 1).toString().padStart(2, '0');
                    const day = now.getDate().toString().padStart(2, '0');

                    let parts = [];
                    const cleanPrefix = (this.prefix || '').trim();
                    if (cleanPrefix) {
                        parts.push(cleanPrefix);
                    }

                    if (this.dateFormat === 'Y') {
                        parts.push(year);
                    } else if (this.dateFormat === 'Ym') {
                        parts.push(year + month);
                    } else if (this.dateFormat === 'Y-m') {
                        parts.push(year + '-' + month);
                    } else if (this.dateFormat === 'Ymd') {
                        parts.push(year + month + day);
                    }

                    const num = parseInt(this.startNumber, 10) || 1;
                    const padded = num.toString().padStart(parseInt(this.digitLength, 10) || 4, '0');

                    if (parts.length === 0) {
                        return padded;
                    }

                    const sep = this.separator !== undefined ? this.separator : '-';
                    let joined = parts.join(sep);
                    if (sep && !joined.endsWith(sep)) {
                        joined += sep;
                    }

                    return joined + padded;
                }
            };
        }
    </script>
@stop
