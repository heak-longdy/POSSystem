<div class="header box-shadow-bottom">
    <div class="header-tab">
        <div class="header-tab-wrapper">
            <div class="menu-row">
                <div class="tabs">
                    <a href="{{ route('admin-report-customer-remaining-amount-index', ['shop_id' => $filters['shop_id'] ?? null]) }}"
                        class="{{ $detail ? '' : 'tabActive' }}">
                        <i class="bx bx-user"></i>
                        {{ __('customer_remaining_report.customer_count') }}
                    </a>
                    @if ($detail)
                        <a href="{{ request()->fullUrl() }}" class="tabActive" aria-current="page">
                            <i class="bx bx-history"></i>
                            {{ __('customer_remaining_report.details_title') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="header-action-button">
            @if ($detail)
                <button type="button" s-click-link="{{ route('admin-report-customer-remaining-amount-index', ['shop_id' => $filters['shop_id'] ?? null]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>{{ __('customer_remaining_report.back') }}</span>
                </button>
            @endif
            @if (!$detail)
                @can('report-sales-excel')
                <button type="button" @click="excel()" class="btn-excel-export" :disabled="exportLoading">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px">
                        <path d="M252.31-180Q222-180 201-201q-21-21-21-51.31v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85h455.38q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46Q780-222 759-201q-21 21-51.31 21H252.31ZM450-664.46l-76.92 76.92q-8.93 8.92-21.19 8.81-12.27-.12-21.58-9.43-8.69-9.3-9-21.07-.31-11.77 9-21.08l124.38-124.38q5.62-5.62 11.85-7.92 6.23-2.31 13.46-2.31t13.46 2.31q6.23 2.3 11.85 7.92l124.38 124.38q8.92 8.92 8.81 20.89-.12 11.96-8.81 21.26-9.31 9.31-21.38 9.62-12.08.31-21.39-9L510-664.46v306q0 12.77-8.62 21.38-8.61 8.62-21.38 8.62t-21.38-8.62q-8.62-8.61-8.62-21.38v-306Z"/>
                    </svg>
                    <span x-text="exportLoading ? '{{ __('customer_remaining_report.excel.exporting') }}' : '{{ __('global.button.export') }}'">
                        {{ __('global.button.export') }}
                    </span>
                </button>
                @endcan
            @endif
            <button type="button" s-click-link="{{ request()->fullUrl() }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>{{ __('sales_report.button.reload') }}</span>
            </button>
        </div>
    </div>
</div>
