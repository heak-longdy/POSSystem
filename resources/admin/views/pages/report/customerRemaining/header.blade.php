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
            <button type="button" s-click-link="{{ request()->fullUrl() }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <span>{{ __('sales_report.button.reload') }}</span>
            </button>
        </div>
    </div>
</div>
