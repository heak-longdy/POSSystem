<style>
    .dashboard-container .dashboard-filter-toolbar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }
    .dashboard-container .dashboard-filter-button {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px;
        border: none !important;
        border-radius: 7px !important;
        background: unset !important;
        color: var(--text-dark) !important;
        padding: 0px 13px !important;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer !important;
        transition: all 0.2s ease;
    }
    .dashboard-container .dashboard-filter-button:hover:not(:disabled) {
        color: var(--primary-blue) !important;
    }
    .dashboard-container .dashboard-filter-button.active {
        color: var(--primary-blue) !important;
    }
    .dashboard-container .dashboard-filter-active-badge {
        width: 7px;
        height: 7px;
        background: var(--primary-blue);
        border-radius: 50%;
        display: inline-block;
    }
    .dashboard-container .dashboard-filter-range {
        font-size: 13px;
        color: var(--text-muted);
        overflow-wrap: anywhere;
    }
    .dashboard-container .dashboard-filter-range strong {
        color: var(--text-dark);
        font-weight: 600;
    }
    .dashboard-container .dashboard-filter-error {
        color: #dc2626;
        font-size: 13px;
        margin: 10px 0;
    }
    .dashboard-date-dialog.dialog {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 210299 !important;
        background: rgba(15, 23, 42, 0.45) !important;
        backdrop-filter: blur(2px) !important;
        -webkit-backdrop-filter: blur(2px) !important;
        display: flex;
        align-items: center !important;
        justify-content: center !important;
        padding: 20px 16px !important;
        box-sizing: border-box !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    .dashboard-date-dialog.dialog[style*="display: none"],
    .dashboard-date-dialog.dialog[x-cloak] {
        display: none !important;
    }
    .dashboard-date-dialog .dialog-wrapper {
        width: 100% !important;
        max-width: 580px !important;
        padding: 0 !important;
        margin: auto !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        box-sizing: border-box !important;
        overflow: visible !important;
    }
    .dashboard-date-dialog .dialog-container {
        width: 100% !important;
        max-width: 580px !important;
        max-height: calc(100vh - 40px) !important;
        padding: 20px 24px !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(15, 23, 42, 0.05) !important;
        box-sizing: border-box !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        position: relative !important;
    }
    .dashboard-date-dialog .dialog-form {
        display: flex !important;
        flex-direction: column !important;
        height: 100% !important;
        max-height: 100% !important;
        min-height: 0 !important;
        overflow: hidden !important;
        margin: 0 !important;
    }
    .dashboard-date-dialog .dialog-form-header {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        padding: 0 0 14px 0 !important;
        margin: 0 0 14px 0 !important;
        border-bottom: 1px solid #f1f5f9 !important;
        flex-shrink: 0 !important;
    }
    .dashboard-date-dialog .dialog-form-header h3 {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.4 !important;
        margin: 0 !important;
        letter-spacing: -0.2px !important;
    }
    .dashboard-date-dialog .dashboard-filter-close {
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        max-width: 32px !important;
        padding: 0 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 7px !important;
        background: #f8fafc !important;
        color: #64748b !important;
        font-size: 20px !important;
        line-height: 1 !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.15s ease !important;
    }
    .dashboard-date-dialog .dashboard-filter-close:hover:not(:disabled) {
        background: #fee2e2 !important;
        border-color: #fca5a5 !important;
        color: #ef4444 !important;
    }
    .dashboard-date-dialog .dialog-form-body {
        padding: 0 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overscroll-behavior: contain !important;
    }
    .dashboard-date-dialog .dialog-form-body::-webkit-scrollbar {
        width: 5px;
    }
    .dashboard-date-dialog .dialog-form-body::-webkit-scrollbar-track {
        background: transparent;
    }
    .dashboard-date-dialog .dialog-form-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .dashboard-date-dialog .dialog-form-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .dashboard-date-dialog .dashboard-filter-help {
        font-size: 12.5px !important;
        line-height: 1.65 !important;
        color: #64748b !important;
        margin: 0 0 14px 0 !important;
        padding: 0 !important;
    }
    .dashboard-date-dialog .dashboard-filter-presets {
        border: 0 !important;
        padding: 0 !important;
        margin: 0 0 14px 0 !important;
        min-width: 0 !important;
    }
    .dashboard-date-dialog .dashboard-filter-presets legend {
        font-size: 12px !important;
        font-weight: 600 !important;
        color: #334155 !important;
        margin: 0 0 8px 0 !important;
        padding: 0 !important;
    }
    .dashboard-date-dialog .dashboard-filter-options {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 6px !important;
    }
    .dashboard-date-dialog .dashboard-filter-preset {
        min-height: 32px !important;
        height: 32px !important;
        white-space: nowrap !important;
        text-overflow: ellipsis !important;
        overflow: hidden !important;
        padding: 0 8px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 7px !important;
        color: #334155 !important;
        background: #f8fafc !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        text-align: center !important;
        transition: all 0.15s ease !important;
    }
    .dashboard-date-dialog .dashboard-filter-preset:hover:not(:disabled) {
        background: #eff6ff !important;
        border-color: #93c5fd !important;
        color: #1d4ed8 !important;
    }
    .dashboard-date-dialog .dashboard-filter-preset.active {
        background: #eff6ff !important;
        border-color: #2563eb !important;
        color: #1d4ed8 !important;
        font-weight: 600 !important;
        box-shadow: 0 0 0 1px #2563eb !important;
    }
    .dashboard-date-dialog button:focus-visible,
    .dashboard-container .dashboard-filter-button:focus-visible {
        outline: 2px solid #2563eb !important;
        outline-offset: 2px !important;
    }
    .dashboard-date-dialog .dashboard-filter-dates {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 12px !important;
        margin-bottom: 0 !important;
    }
    .dashboard-date-dialog .dashboard-filter-dates .form-row {
        min-width: 0 !important;
        margin-bottom: 0 !important;
        line-height: normal !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .dashboard-date-dialog .dashboard-filter-dates .form-row label {
        display: block !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        color: #334155 !important;
        margin: 0 0 5px 0 !important;
        text-align: left !important;
        float: none !important;
        padding: 0 !important;
    }
    .dashboard-date-dialog .filter-date-input {
        position: relative !important;
        width: 100% !important;
        cursor: pointer !important;
    }
    .dashboard-date-dialog .filter-date-input input {
        width: 100% !important;
        height: 38px !important;
        box-sizing: border-box !important;
        padding: 0 34px 0 11px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 7px !important;
        font-size: 12.5px !important;
        font-weight: 500 !important;
        color: #1e293b !important;
        background: #ffffff !important;
        outline: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }
    .dashboard-date-dialog .filter-date-input input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
    }
    .dashboard-date-dialog .filter-date-input input[aria-invalid="true"] {
        border-color: #dc2626 !important;
        background-color: #fef2f2 !important;
    }
    .dashboard-date-dialog .filter-date-input i {
        position: absolute !important;
        right: 10px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        font-size: 18px !important;
        color: #64748b !important;
        pointer-events: none !important;
    }
    .dashboard-date-dialog .dashboard-filter-preview {
        margin-top: 12px !important;
        padding: 9px 12px !important;
        border-radius: 7px !important;
        background: #f0f7ff !important;
        border: 1px solid #bfdbfe !important;
        font-size: 12px !important;
    }
    .dashboard-date-dialog .dashboard-filter-preview span {
        display: block !important;
        margin-bottom: 2px !important;
        color: #64748b !important;
        font-size: 11.5px !important;
        font-weight: 500 !important;
    }
    .dashboard-date-dialog .dashboard-filter-preview strong {
        font-weight: 600 !important;
        color: #1d4ed8 !important;
        overflow-wrap: anywhere !important;
        font-size: 12.5px !important;
    }
    .dashboard-date-dialog .dashboard-filter-error {
        color: #dc2626 !important;
        font-size: 12px !important;
        line-height: 1.4 !important;
        margin: 8px 0 0 0 !important;
    }
    .dashboard-date-dialog .dialog-form-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        margin-top: 14px !important;
        padding-top: 12px !important;
        border-top: 1px solid #f1f5f9 !important;
        flex-shrink: 0 !important;
        background: #ffffff !important;
    }
    .dashboard-date-dialog .dialog-container .dialog-form-footer .dashboard-filter-reset {
        margin-right: auto !important;
        color: #475569 !important;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 7px !important;
        padding: 0 14px !important;
        font-size: 12.5px !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        transition: all 0.15s ease !important;
        height: 38px !important;
    }
    .dashboard-date-dialog .dialog-container .dialog-form-footer .dashboard-filter-reset:hover:not(:disabled) {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }
    .dashboard-date-dialog .dialog-container .dialog-form-footer button.close {
        cursor: pointer !important;
        padding: 0 16px !important;
        border-radius: 7px !important;
        font-size: 12.5px !important;
        font-weight: 500 !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #475569 !important;
        transition: all 0.15s ease !important;
        height: 38px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .dashboard-date-dialog .dialog-container .dialog-form-footer button.close:hover:not(:disabled) {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }
    .dashboard-date-dialog .dialog-container .dialog-form-footer button[type="submit"] {
        cursor: pointer !important;
        background: #2563eb !important;
        color: #ffffff !important;
        border: 1px solid #2563eb !important;
        border-radius: 7px !important;
        padding: 0 18px !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        transition: all 0.15s ease !important;
        height: 38px !important;
    }
    .dashboard-date-dialog .dialog-container .dialog-form-footer button[type="submit"]:hover:not(:disabled) {
        background: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }
    .dashboard-date-dialog button:disabled, .dashboard-filter-toolbar button:disabled {
        opacity: 0.6 !important;
        cursor: wait !important;
    }
    /* The shared dialog is at 210299; its calendar must sit above that overlay. */
    #ui-datepicker-div.dashboard-datepicker {
        z-index: 210300 !important;
    }
    @media (max-width: 540px) {
        .dashboard-container .dashboard-filter-toolbar {
            gap: 8px;
        }
        .dashboard-container .dashboard-filter-range {
            flex-basis: 100%;
            text-align: right;
        }
        .dashboard-date-dialog.dialog {
            padding: 12px 10px !important;
        }
        .dashboard-date-dialog .dialog-container {
            padding: 16px !important;
            max-height: calc(100vh - 24px) !important;
        }
        .dashboard-date-dialog .dashboard-filter-options {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
        .dashboard-date-dialog .dashboard-filter-dates {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }
        .dashboard-date-dialog .dialog-form-footer {
            flex-wrap: wrap !important;
            gap: 8px !important;
        }
        .dashboard-date-dialog .dialog-container .dialog-form-footer .dashboard-filter-reset {
            width: 100% !important;
            margin-right: 0 !important;
            justify-content: center !important;
            order: 3 !important;
        }
        .dashboard-date-dialog .dialog-container .dialog-form-footer button.close {
            flex: 1 1 calc(50% - 4px) !important;
            order: 1 !important;
        }
        .dashboard-date-dialog .dialog-container .dialog-form-footer button[type="submit"] {
            flex: 1 1 calc(50% - 4px) !important;
            order: 2 !important;
        }
    }
</style>

<div class="dashboard-filter-toolbar">
    <span class="dashboard-filter-range" aria-live="polite" aria-atomic="true">
        @lang('dashboard.filter.active_range'): <strong x-text="activeRangeSummary"></strong>
    </span>
    <span x-show="loading" style="display: none" role="status">
        <i class="bx bx-loader-alt bx-spin" aria-hidden="true"></i> @lang('dashboard.filter.loading')
    </span>
    <button type="button" class="dashboard-filter-button" x-ref="filterButton" @click="openFilter()"
        :disabled="loading" :class="{ active: fromDate }" aria-haspopup="dialog" :aria-expanded="showFilter">
        <i class="bx bx-calendar" aria-hidden="true"></i>
        <span>@lang('dashboard.filter.custom')</span>
        <template x-if="fromDate">
            <span class="dashboard-filter-active-badge" aria-hidden="true"></span>
        </template>
    </button>
</div>
<p class="dashboard-filter-error" x-show="requestError && !showFilter" x-text="requestError" role="alert" style="display: none"></p>

<template x-teleport="body">
    <div class="dialog dashboard-date-dialog" x-cloak x-show="showFilter" style="display: none"
        x-id="['dashboard-filter-title', 'dashboard-filter-help', 'dashboard-filter-error', 'dashboard-from-date', 'dashboard-to-date']"
        role="dialog" aria-modal="true" :aria-labelledby="$id('dashboard-filter-title')" :aria-describedby="$id('dashboard-filter-help')"
        :aria-busy="loading" @click.self="closeFilter()"
        @keydown.escape.stop.prevent="closeFilter()" @keydown.tab="trapDialogFocus($event)">
        <div class="dialog-wrapper" @click.self="closeFilter()">
            <div class="dialog-container">
                <form class="dialog-form" @submit.prevent.stop="applyFilter()">
                    <div class="dialog-form-header">
                        <h3 :id="$id('dashboard-filter-title')">@lang('dashboard.filter.custom')</h3>
                        <button type="button" class="dashboard-filter-close" x-ref="dialogClose" @click="closeFilter()"
                            :disabled="loading" aria-label="{{ __('global.button.close') }}"><i class="bx bx-x" aria-hidden="true"></i></button>
                    </div>
                    <div class="dialog-form-body">
                        <p class="dashboard-filter-help" :id="$id('dashboard-filter-help')">@lang('dashboard.filter.help')</p>
                        <fieldset class="dashboard-filter-presets" :disabled="loading">
                            <legend>@lang('dashboard.filter.quick_ranges')</legend>
                            <div class="dashboard-filter-options">
                                @foreach (__('dashboard.filter.ranges') as $range => $label)
                                    <button type="button" class="dashboard-filter-preset" data-range="{{ $range }}"
                                        @click="selectPreset('{{ $range }}')" :disabled="loading"
                                        :class="{ active: draftPreset === '{{ $range }}' }" :aria-pressed="draftPreset === '{{ $range }}'">{{ $label }}</button>
                                @endforeach
                            </div>
                        </fieldset>
                        <div class="dashboard-filter-dates">
                            <div class="form-row">
                                <label :for="$id('dashboard-from-date')">@lang('dashboard.filter.start_date')</label>
                                <div class="filter-date-input" @click="openCalendar('fromDate')">
                                    <input type="text" name="from_date" :id="$id('dashboard-from-date')" x-ref="fromDate"
                                        :aria-invalid="invalidField === 'fromDate'" :aria-describedby="$id('dashboard-filter-error')"
                                        autocomplete="off" placeholder="{{ __('dashboard.filter.date_placeholder') }}" :disabled="loading">
                                    <i class="bx bx-calendar" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="form-row">
                                <label :for="$id('dashboard-to-date')">@lang('dashboard.filter.to_date')</label>
                                <div class="filter-date-input" @click="openCalendar('toDate')">
                                    <input type="text" name="to_date" :id="$id('dashboard-to-date')" x-ref="toDate"
                                        :aria-invalid="invalidField === 'toDate'" :aria-describedby="$id('dashboard-filter-error')"
                                        autocomplete="off" placeholder="{{ __('dashboard.filter.date_placeholder') }}" :disabled="loading">
                                    <i class="bx bx-calendar" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-filter-preview" aria-live="polite" aria-atomic="true">
                            <span>@lang('dashboard.filter.selected_range')</span>
                            <strong x-text="draftRangeSummary"></strong>
                        </div>
                        <p class="dashboard-filter-error" :id="$id('dashboard-filter-error')" x-show="validationError || requestError"
                            x-text="validationError || requestError" role="alert" style="display: none"></p>
                    </div>
                    <div class="dialog-form-footer">
                        <button type="button" class="dashboard-filter-reset" @click="resetFilter()" :disabled="loading"
                            title="{{ __('dashboard.filter.reset_help') }}"><i class="bx bx-reset" aria-hidden="true"></i> @lang('global.button.reset')</button>
                        <button type="button" class="close" @click="closeFilter()" :disabled="loading">@lang('global.button.cancel')</button>
                        <button type="submit" :disabled="loading">
                            <i :class="loading ? 'bx bx-loader-alt bx-spin' : 'bx bx-check'" aria-hidden="true"></i>
                            <span x-text="loading ? @js(__('dashboard.filter.loading')) : @js(__('dashboard.filter.apply'))"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
