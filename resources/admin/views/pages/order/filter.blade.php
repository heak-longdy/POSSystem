@php
    $advancedCount = (request()->filled('shop_id') ? 1 : 0) + (request()->filled('from_date') ? 1 : 0) + (request()->filled('to_date') ? 1 : 0);
    $hasAnyFilter = request()->filled('from_date') || request()->filled('to_date') || request()->filled('shop_id') || request()->filled('search');
@endphp

<div class="form-row search-input" style="width: 190px; min-width: 180px;">
    <input type="text" name="search" placeholder="{{ __('order.filter.search') }}" value="{!! request('search') !!}">
</div>

<div class="advanced-filter-container"
    x-data="{
        showAdvancedFilter: false,
        toggle() {
            this.showAdvancedFilter = !this.showAdvancedFilter;
            if (this.showAdvancedFilter) {
                this.$nextTick(() => {
                    if (typeof initOrderDatepickers === 'function') initOrderDatepickers();
                });
            }
        },
        close() {
            this.showAdvancedFilter = false;
        }
    }"
    @keydown.escape.window="close()"
    @click.outside="if (!$event.target.closest('.select2-container') && !$event.target.closest('#ui-datepicker-div')) { close(); }">

    <button type="button" class="btn-advanced-filter {{ $advancedCount > 0 ? 'active' : '' }}"
        :class="{ 'open': showAdvancedFilter }"
        @click.stop="toggle()"
        title="{{ __('order.filter.advanced_filter') }}">
        <i class='bx bx-filter-alt'></i>
        <span>{{ __('order.filter.advanced_filter') }}</span>
        @if($advancedCount > 0)
            <span class="advanced-filter-badge">{{ $advancedCount }}</span>
        @endif
        <i class='bx bx-chevron-down' :class="{ 'bx-rotate-180': showAdvancedFilter }" style="font-size: 15px; transition: transform 0.2s ease;"></i>
    </button>

    <div class="advanced-filter-popover" x-show="showAdvancedFilter" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 transform scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 transform scale-95 -translate-y-1">
        <div class="filter-popover-header">
            <div class="filter-popover-title">
                <i class='bx bx-filter-alt'></i>
                <span>{{ __('order.filter.advanced_filter') }}</span>
                @if($advancedCount > 0)
                    <span class="filter-popover-count">{{ $advancedCount }} {{ __('global.tab.active') ?? 'Active' }}</span>
                @endif
            </div>
            <button type="button" class="filter-popover-close" @click="close()" title="{{ __('order.button.close') ?? 'Close' }}">
                <i class='bx bx-x'></i>
            </button>
        </div>

        <div class="filter-popover-body">
            {{-- Shop Selection (Standard Select2) --}}
            <div class="filter-field-group">
                <label for="shop_id">{{ __('order.table.shop') }}</label>
                <div class="select2Group">
                    <select name="shop_id" id="shop_id" class="SelectField select2" x-init="fetchSelectShop()">
                        <option value="">{{ __('order.select_shop') }}</option>
                    </select>
                    <div class="select2Reset" x-show="formData.shop_id" @click.stop="resetShopField()" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Date Range (From Date & To Date) --}}
            <div class="filter-dates-grid">
                <div class="filter-field-group">
                    <label for="from_date">{{ __('order.filter.from_date') }}</label>
                    <div class="filter-date-input" @click="$('#from_date').focus()">
                        <input type="text" name="from_date" id="from_date" class="filter-input"
                            value="{!! request('from_date') !!}" autocomplete="off" placeholder="YYYY-MM-DD">
                        <i class='bx bx-calendar' aria-hidden="true"></i>
                    </div>
                </div>
                <div class="filter-field-group">
                    <label for="to_date">{{ __('order.filter.to_date') }}</label>
                    <div class="filter-date-input" @click="$('#to_date').focus()">
                        <input type="text" name="to_date" id="to_date" class="filter-input"
                            value="{!! request('to_date') !!}" autocomplete="off" placeholder="YYYY-MM-DD">
                        <i class='bx bx-calendar' aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-popover-footer">
            <button type="button" class="btn-popover-reset" @click="resetAdvancedFilters()">
                <i class='bx bx-reset'></i>
                <span>{{ __('global.button.reset') }}</span>
            </button>
            <button type="submit" class="btn-popover-apply">
                <i class='bx bx-check'></i>
                <span>{{ __('order.button.search') }}</span>
            </button>
        </div>
    </div>
</div>

@if($hasAnyFilter)
    <a href="{!! route('admin-' . ($routeName ?? 'order') . '-list', $status ?? 'Pending') !!}" class="btn-clear-filter" title="{{ __('global.button.reset') }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </a>
@endif

