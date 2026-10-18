<section class="order-product-picker" x-data="$store.confirmDialog.data.picker" x-id="['product-picker-title', 'product-picker-help', 'product-picker-shop']"
    role="dialog" aria-modal="true" :aria-labelledby="$id('product-picker-title')" :aria-describedby="$id('product-picker-help')"
    @keydown.escape.stop.prevent="cancel()" @keydown.tab="trapFocus($event)" @workspace:tab-change.window="cancel()">
    <header class="order-product-picker__header">
        <div class="order-product-picker__heading">
            <div class="order-product-picker__title-row">
                <span class="order-product-picker__icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21 8-9-5-9 5v9l9 5 9-5V8Z M3 8l9 5 9-5 M12 13v9 M7.5 5.5l9 5"></path>
                    </svg>
                </span>
                <h2 :id="$id('product-picker-title')">{{ __('order.product_picker.add_product') }}</h2>
                <span class="order-product-picker__shop" x-show="shopName" x-text="shopName"></span>
            </div>
            <p :id="$id('product-picker-help')">{{ __('order.product_picker.description') }}</p>
        </div>
        <button type="button" class="order-product-picker__close" @click="cancel()" :disabled="closing"
            aria-label="{{ __('order.button.close') }}">&times;</button>
    </header>

    <div class="order-product-picker__toolbar">
        <div class="order-product-picker__shop-filter sidebar-form-grid">
            <div class="form-group form-group--full">
                <label :for="$id('product-picker-shop')">{{ __('order.form.select_shop') }}</label>
                <div class="select2Group">
                    <select name="product_picker_shop_id" :id="$id('product-picker-shop')" class="SelectField select2"
                        x-ref="pickerShop" :disabled="!canChangeShop">
                        <option value="">{{ __('order.form.select_shop') }}</option>
                    </select>
                    <div class="select2Reset" x-show="shopId && canChangeShop" @click.stop="resetShop()" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <label class="order-product-picker__search">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7"></circle><path d="m16 16 4 4"></path>
            </svg>
            <input type="search" x-ref="productSearch" x-model="search" @input="searchProducts()" :disabled="!canSelect"
                placeholder="{{ __('order.product_picker.search') }}" aria-label="{{ __('order.product_picker.search') }}" autocomplete="off">
        </label>
        <span class="order-product-picker__count" aria-live="polite">
            <strong x-text="selectedItems.length"></strong> {{ __('order.product_picker.selected') }}
        </span>
    </div>

    <div class="order-product-picker__body" x-ref="pickerBody" :aria-busy="loading">
        <div class="order-product-picker__progress" x-show="loading && products.length" aria-hidden="true" style="display: none;">
            <div class="order-product-picker__progress-bar"></div>
        </div>
        <div class="order-product-picker__state" x-show="!validShop" role="status">
            <strong>{{ __('order.product_picker.select_shop_first') }}</strong>
            <p>{{ __('order.product_picker.shop_help') }}</p>
        </div>
        <div class="order-product-picker__state" x-show="validShop && loading && !products.length" role="status">
            <span class="order-product-picker__spinner" aria-hidden="true"></span>
            <p>{{ __('order.product_picker.loading') }}</p>
        </div>
        <div class="order-product-picker__state" x-show="validShop && !loading && failed" role="alert">
            <strong>{{ __('order.product_picker.load_failed') }}</strong>
            <button type="button" class="order-product-picker__secondary" @click="loadProducts()">{{ __('order.product_picker.retry') }}</button>
        </div>
        <div class="order-product-picker__state" x-show="validShop && !loading && !failed && !products.length" role="status">
            <strong>{{ __('order.product_picker.no_products') }}</strong>
            <p x-text="search ? @js(__('order.product_picker.search_help')) : @js(__('order.product_picker.empty_shop'))"></p>
        </div>
        <div class="order-product-picker__table-wrap" :class="{ 'is-loading': loading }" x-show="validShop && !failed && products.length">
            <table>
                <thead>
                    <tr>
                        <th scope="col" class="order-product-picker__check">
                            <input type="checkbox" :checked="allSelected" x-effect="$el.indeterminate = someSelected && !allSelected"
                                :disabled="!canSelect || !selectableItems.length" @change="togglePage($event.target.checked)"
                                aria-label="{{ __('order.product_picker.select_page') }}">
                        </th>
                        <th scope="col" class="order-product-picker__no">Nº</th>
                        <th scope="col" class="order-product-picker__code">{{ __('order.product_picker.code') }}</th>
                        <th scope="col" class="order-product-picker__th-product">{{ __('order.table.product') }}</th>
                        <th scope="col" class="order-product-picker__numeric">{{ __('order.unit_price') }}</th>
                        <th scope="col" class="order-product-picker__numeric">{{ __('order.product_picker.current_stock') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in products" :key="key(item)">
                        <tr :class="{ 'is-selected': isSelected(item) && !inOrder(item), 'is-added': inOrder(item) }">
                            <td class="order-product-picker__check">
                                <input type="checkbox" :checked="inOrder(item) || isSelected(item)" :disabled="!canSelect || inOrder(item)"
                                    @change="toggle(item)" :aria-label="name(item)">
                            </td>
                            <td class="order-product-picker__no" data-label="Nº" x-text="itemNo(index)"></td>
                            <td class="order-product-picker__code" data-label="{{ __('order.product_picker.code') }}">
                                <span class="order-product-picker__code-text" x-show="code(item)" x-text="code(item)"></span>
                                <span class="order-product-picker__code-empty" x-show="!code(item)">---</span>
                            </td>
                            <td class="order-product-picker__name">
                                <button type="button" class="order-product-picker__product" @click="toggle(item)" :disabled="!canSelect || inOrder(item)"
                                    :aria-pressed="isSelected(item) || inOrder(item)">
                                    <img :src="image(item)" alt="" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/logo/default.png') }}'">
                                    <span>
                                        <strong x-text="name(item)"></strong>
                                        <small x-show="inOrder(item)">{{ __('order.product_picker.already_added') }}</small>
                                    </span>
                                </button>
                            </td>
                            <td class="order-product-picker__numeric" data-label="{{ __('order.unit_price') }}" x-text="price(item)"></td>
                            <td class="order-product-picker__numeric" data-label="{{ __('order.product_picker.current_stock') }}">
                                <span :class="stock(item) <= 0 ? 'order-product-picker__stock--out' : 'order-product-picker__stock'"
                                    x-text="stock(item)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <nav class="order-product-picker__pagination paginationLayout2" x-show="validShop" aria-label="{{ __('order.product_picker.pages') }}">
        <div class="pagination">
            <div class="rows-per-page">
                <span>{{ __('pagination.rows_per_page') }}</span>
            </div>
            <div class="page-info">
                <span><span x-text="from"></span> - <span x-text="to"></span> {{ __('pagination.of') }} <span x-text="total"></span></span>
            </div>
            <div class="page-controls-tb">
                <button type="button" class="first-page btn"
                    :class="{ disabled: loading || page <= 1 || !canSelect }"
                    title="{{ __('pagination.first') }}" aria-label="{{ __('pagination.first') }}"
                    @mouseenter="prefetch(1)" @focus="prefetch(1)"
                    @click="loadProducts(1)" :disabled="loading || page <= 1 || !canSelect">
                    <i class='bx bx-first-page' aria-hidden="true"></i>
                </button>
                <button type="button" class="prev-page btn"
                    :class="{ disabled: loading || page <= 1 || !canSelect }"
                    title="{{ __('pagination.previous') }}" aria-label="{{ __('pagination.previous') }}"
                    @mouseenter="prefetch(page - 1)" @focus="prefetch(page - 1)"
                    @click="loadProducts(page - 1)" :disabled="loading || page <= 1 || !canSelect">
                    <i class='bx bx-chevron-left' aria-hidden="true"></i>
                </button>
                <button type="button" class="next-page btn"
                    :class="{ disabled: loading || !hasMore || (total > 0 && page >= lastPage) || !canSelect }"
                    title="{{ __('pagination.next') }}" aria-label="{{ __('pagination.next') }}"
                    @mouseenter="prefetch(page + 1)" @focus="prefetch(page + 1)"
                    @click="loadProducts(page + 1)" :disabled="loading || !hasMore || (total > 0 && page >= lastPage) || !canSelect">
                    <i class='bx bx-chevron-right' aria-hidden="true"></i>
                </button>
                <button type="button" class="last-page btn"
                    :class="{ disabled: loading || !hasMore || (total > 0 && page >= lastPage) || !canSelect }"
                    title="{{ __('pagination.last') }}" aria-label="{{ __('pagination.last') }}"
                    @mouseenter="prefetch(lastPage)" @focus="prefetch(lastPage)"
                    @click="loadProducts(lastPage)" :disabled="loading || !hasMore || (total > 0 && page >= lastPage) || !canSelect">
                    <i class='bx bx-last-page' aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </nav>
    <footer class="order-product-picker__footer">
        <p>{{ __('order.product_picker.edit_hint') }}</p>
        <div>
            <button type="button" class="order-product-picker__secondary" @click="cancel()" :disabled="closing">{{ __('order.action.cancel') }}</button>
            <button type="button" class="order-product-picker__primary" @click="addItems()"
                :disabled="!canSelect || loading || failed || !selectedItems.length">
                {{ __('order.product_picker.add_item') }} <span x-text="'(' + selectedItems.length + ')'"></span>
            </button>
        </div>
    </footer>
    <style>
        .dialog .dialog-container.order-product-picker-container {
            width: min(960px, calc(100vw - 32px)); padding: 0; border: 1px solid #e2e8f0;
            border-radius: 18px; overflow: hidden; box-shadow: 0 24px 72px rgba(15, 23, 42, .22);
        }
        .order-product-picker {
            position: relative; display: flex; flex-direction: column; max-height: min(840px, calc(100dvh - 48px));
            overflow-y: auto; background: #fff; color: #334155; font-size: 13px;
        }
        .order-product-picker > header, .order-product-picker > footer,
        .order-product-picker__toolbar, .order-product-picker__pagination { flex-shrink: 0; }
        .order-product-picker * { box-sizing: border-box; }
        .order-product-picker h2, .order-product-picker p { margin: 0; }
        .order-product-picker button, .order-product-picker input { font: inherit; }
        .dialog .order-product-picker button { cursor: pointer; min-width: 0; line-height: 1.5; justify-content: center; gap: 7px; }
        .order-product-picker button:disabled { cursor: not-allowed; opacity: .45; }
        .order-product-picker button:focus-visible, .order-product-picker input[type="checkbox"]:focus-visible { outline: 2px solid #2563eb; outline-offset: 3px; }
        .order-product-picker__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 22px 24px 18px; }
        .order-product-picker__heading { min-width: 0; }
        .order-product-picker__title-row { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
        .order-product-picker__icon { display: grid; place-items: center; width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; background: var(--order-accent-soft); color: var(--order-accent); }
        .order-product-picker__header h2 { font-size: 20px; line-height: 1.4; font-weight: 700; letter-spacing: -.4px; color: var(--order-ink); }
        .order-product-picker__header p { margin-top: 10px; color: var(--order-muted); font-size: 12px; line-height: 1.7; }
        .order-product-picker__shop { display: inline-flex; align-items: center; padding: 4px 9px; border: 1px solid #dbeafe; border-radius: 6px; background: var(--order-accent-soft); color: #1d4ed8; font-size: 11px; font-weight: 600; max-width: 100%; overflow-wrap: anywhere; }
        .order-product-picker button.order-product-picker__close { width: 36px; height: 36px; padding: 0; flex-shrink: 0; border: 1px solid var(--order-border); border-radius: 9px; background: #fff; color: #64748b; font-size: 23px; line-height: 1; transition: background-color .15s, color .15s; }
        .order-product-picker button.order-product-picker__close:hover { background: var(--order-soft); color: var(--order-ink); }
        .order-product-picker__toolbar { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 14px; padding: 0 24px 20px; }
        .order-product-picker__shop-filter { flex: 0 1 250px; min-width: 180px; }
        .order-product-picker__search { display: flex; align-items: center; gap: 9px; flex: 1; min-width: 0; height: 43px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #94a3b8; transition: border-color .15s, box-shadow .15s; }
        .order-product-picker__search:focus-within { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37, 99, 235, .12); }
        .order-product-picker__search svg { flex-shrink: 0; }
        .order-product-picker__search input { width: 100%; min-width: 0; height: 100%; padding: 0; border: 0; outline: none !important; background: transparent; color: var(--order-ink); font-size: 12.5px; }
        .order-product-picker__search input::placeholder { color: #94a3b8; }
        .order-product-picker__count { display: flex; align-items: center; gap: 5px; align-self: flex-end; min-height: 43px; padding: 0 10px; border: 1px solid #dbeafe; border-radius: 7px; background: var(--order-accent-soft); color: #1d4ed8; font-size: 12px; white-space: nowrap; }
        .order-product-picker__count strong { font-size: 14px; font-variant-numeric: tabular-nums; }
        .order-product-picker__body { position: relative; flex: 1 1 auto; min-height: 0; overflow: auto; overscroll-behavior: contain; border-top: 1px solid var(--order-border); border-bottom: 1px solid var(--order-border); }
        .order-product-picker__progress { position: sticky; top: 0; height: 3px; margin-bottom: -3px; background: #dbeafe; overflow: hidden; z-index: 3; }
        .order-product-picker__progress-bar { width: 100%; height: 100%; background: #2563eb; animation: order-product-picker-indeterminate 1.1s cubic-bezier(.65, .815, .735, .395) infinite; transform-origin: 0% 50%; }
        @keyframes order-product-picker-indeterminate {
            0% { transform: translateX(-100%) scaleX(.2); }
            50% { transform: translateX(0) scaleX(.5); }
            100% { transform: translateX(100%) scaleX(.2); }
        }
        .order-product-picker__table-wrap { transition: opacity .15s ease; }
        .order-product-picker__table-wrap.is-loading { opacity: .55; pointer-events: none; user-select: none; }
        .order-product-picker table { width: 100%; border-collapse: collapse; text-align: left; }
        .order-product-picker th { position: sticky; top: 0; z-index: 1; background: var(--order-soft); color: var(--order-muted); font-size: 11px; font-weight: 600; line-height: 1.5; }
        .order-product-picker th, .order-product-picker td { padding: 15px 16px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        .order-product-picker tbody tr { transition: background-color .15s ease; }
        .order-product-picker tbody tr:last-child td { border-bottom: 0; }
        .order-product-picker tbody tr:hover, .order-product-picker tbody tr:focus-within { background: #f8fbff; }
        .order-product-picker tbody tr.is-selected { background: #eff6ff; }
        .order-product-picker tbody tr.is-selected td:first-child { box-shadow: inset 3px 0 #2563eb; }
        .order-product-picker tbody tr.is-added { background: #f8fafc; }
        .order-product-picker__check { width: 52px; text-align: center; }
        .order-product-picker__no { width: 48px; text-align: center; color: var(--order-muted); font-size: 12px; font-weight: 500; font-variant-numeric: tabular-nums; padding: 15px 6px; }
        th.order-product-picker__no { font-size: 11px; font-weight: 600; }
        .order-product-picker__code { width: 124px; font-variant-numeric: tabular-nums; }
        .order-product-picker__code-text { display: inline-block; max-width: 130px; padding: 3px 7px; border: 1px solid var(--order-border); border-radius: 5px; background: var(--order-soft); color: #475569; font-size: 11px; font-weight: 500; line-height: 1.5; overflow-wrap: anywhere; }
        .order-product-picker__code-empty { color: #94a3b8; }
        .order-product-picker input[type="checkbox"] { display: inline-block; width: 18px; height: 18px; margin: 0; accent-color: #2563eb; cursor: pointer; vertical-align: middle; }
        .order-product-picker input[type="checkbox"]:disabled { cursor: not-allowed; }
        .order-product-picker__numeric { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .order-product-picker td.order-product-picker__numeric { font-size: 13px; font-weight: 600; color: var(--order-ink); }
        .order-product-picker__stock, .order-product-picker__stock--out { display: inline-block; min-width: 34px; padding: 4px 9px; border: 1px solid var(--order-border); border-radius: 6px; background: var(--order-soft); color: #475569; font-size: 12px; font-weight: 600; text-align: center; }
        .order-product-picker__stock--out { background: #fff1f2; border-color: #ffe4e6; color: #be123c; }
        .order-product-picker button.order-product-picker__product { display: flex; align-items: center; justify-content: flex-start; gap: 12px; width: 100%; min-height: 44px; padding: 0; border: 0; background: transparent; text-align: left; color: #0f172a; white-space: normal; }
        .order-product-picker button.order-product-picker__product:disabled { opacity: 1; background: transparent !important; color: #475569 !important; }
        .order-product-picker__product img { width: 44px; height: 44px; flex-shrink: 0; border: 1px solid var(--order-border); border-radius: 10px; object-fit: cover; background: var(--order-soft); }
        .order-product-picker__product > span { min-width: 0; }
        .order-product-picker__product strong { display: block; font-size: 13px; font-weight: 600; line-height: 1.6; overflow-wrap: anywhere; }
        .order-product-picker__product small { display: inline-block; margin-top: 4px; color: #0f766e; font-size: 10.5px; font-weight: 500; }
        .order-product-picker__state { min-height: 200px; padding: 48px 24px; text-align: center; color: var(--order-muted); background: var(--order-soft); }
        .order-product-picker__state strong { display: block; font-size: 14px; font-weight: 600; color: #334155; }
        .order-product-picker__state p { max-width: 420px; margin: 10px auto 0; font-size: 12px; line-height: 1.8; }
        .order-product-picker__state button { margin-top: 16px; }
        .order-product-picker__spinner { display: inline-block; width: 26px; height: 26px; border: 2px solid #dbeafe; border-top-color: #2563eb; border-radius: 50%; animation: order-product-picker-spin .7s linear infinite; }
        @keyframes order-product-picker-spin { to { transform: rotate(360deg); } }
        .order-product-picker__pagination.paginationLayout2 {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            height: auto;
            min-height: 52px;
            padding: 12px 24px;
            margin: 0;
            background: #fff;
            border-radius: 0;
            color: #475569;
            font-size: 13px;
        }
        .order-product-picker__pagination .pagination {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            font-size: 13px;
            gap: 0;
        }
        .order-product-picker__pagination .rows-per-page {
            display: flex;
            align-items: center;
        }
        .order-product-picker__pagination .rows-per-page span {
            margin-right: 8px;
            color: #475569;
            font-size: 13px;
            font-weight: 500;
        }
        .order-product-picker__pagination .page-info {
            margin: 0 20px;
            color: #334155;
            font-size: 13px;
            font-weight: 500;
            font-variant-numeric: tabular-nums;
        }
        .order-product-picker__pagination .page-controls-tb {
            display: flex;
            align-items: center;
            gap: 2px;
        }
        .dialog .order-product-picker .page-controls-tb .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: none !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 4px !important;
            width: auto !important;
            min-width: auto !important;
            height: auto !important;
            min-height: auto !important;
            font-size: 16px !important;
            color: #1e293b !important;
            cursor: pointer !important;
            transition: color .15s ease;
        }
        .order-product-picker .page-controls-tb .btn i.bx {
            font-size: 22px;
            line-height: 1;
        }
        .dialog .order-product-picker .page-controls-tb button:hover:not(:disabled) {
            color: #000 !important;
        }
        .dialog .order-product-picker .page-controls-tb .btn:disabled,
        .dialog .order-product-picker .page-controls-tb .btn.disabled {
            color: #cbd5e1 !important;
            cursor: not-allowed !important;
            opacity: 0.65;
        }
        .order-product-picker__footer > div { display: flex; gap: 8px; }
        .order-product-picker__footer { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 16px 24px; border-top: 1px solid var(--order-border); background: var(--order-soft); }
        .order-product-picker__footer p { max-width: 360px; color: var(--order-muted); font-size: 11.5px; line-height: 1.7; }
        .order-product-picker button.order-product-picker__primary, .order-product-picker button.order-product-picker__secondary { min-height: 43px; padding: 10px 16px; border: 1px solid #cbd5e1; border-radius: 9px; white-space: nowrap; background: #fff; color: #475569; font-size: 12.5px; font-weight: 600; transition: background-color .15s, border-color .15s, box-shadow .15s; }
        .order-product-picker button.order-product-picker__primary { background: #2563eb; border-color: #2563eb; color: #fff; box-shadow: 0 2px 4px rgba(37, 99, 235, .12); }
        .order-product-picker button.order-product-picker__primary:hover:not(:disabled) { background: #1d4ed8; border-color: #1d4ed8; }
        .order-product-picker button.order-product-picker__secondary:hover:not(:disabled) { background: #f1f5f9; border-color: #94a3b8; }
        .order-product-picker__primary span { display: inline-flex; align-items: center; justify-content: center; padding: 1px 6px; border-radius: 5px; background: rgba(255, 255, 255, .18); font-size: 11px; font-variant-numeric: tabular-nums; }
        @media (max-width: 640px) {
            .dialog .dialog-container.order-product-picker-container { width: calc(100vw - 24px); border-radius: 14px; }
            .order-product-picker { max-height: calc(100dvh - 32px); }
            .order-product-picker__header { padding: 16px; gap: 10px; }
            .order-product-picker__title-row { gap: 8px; }
            .order-product-picker__header h2 { font-size: 18px; }
            .order-product-picker__shop { font-size: 10px; }
            .order-product-picker__toolbar { padding: 0 16px 16px; gap: 10px; }
            .order-product-picker__shop-filter { flex-basis: 100%; }
            .order-product-picker__count { padding: 0 8px; }
            .order-product-picker table, .order-product-picker thead, .order-product-picker tbody { display: block; }
            .order-product-picker thead { position: sticky; top: 0; z-index: 2; background: var(--order-soft); border-bottom: 1px solid var(--order-border); }
            .order-product-picker thead tr { display: flex; align-items: center; }
            .order-product-picker th { position: static; border: 0; padding: 12px; }
            .order-product-picker th.order-product-picker__th-product { flex: 1; padding-left: 0; }
            .order-product-picker thead .order-product-picker__no,
            .order-product-picker thead .order-product-picker__code,
            .order-product-picker thead .order-product-picker__numeric { display: none; }
            .order-product-picker tbody { padding: 10px; background: var(--order-soft); }
            .order-product-picker tbody tr {
                display: grid; grid-template-columns: 22px repeat(3, minmax(0, 1fr)); gap: 12px 10px;
                margin-bottom: 10px; padding: 14px 12px; background: #fff; border: 1px solid var(--order-border); border-radius: 11px;
            }
            .order-product-picker tbody tr:last-child { margin-bottom: 0; }
            .order-product-picker tbody tr.is-selected { border-color: #93c5fd; }
            .order-product-picker tbody tr.is-selected td:first-child { box-shadow: none; }
            .order-product-picker tbody td { display: block; padding: 0; width: auto; min-width: 0; border: 0; }
            .order-product-picker tbody .order-product-picker__no { display: none; }
            .order-product-picker tbody .order-product-picker__check { grid-column: 1; grid-row: 1; align-self: center; }
            .order-product-picker tbody .order-product-picker__name { grid-column: 2 / 5; grid-row: 1; }
            .order-product-picker tbody .order-product-picker__code { grid-column: 2; grid-row: 2; }
            .order-product-picker tbody td:nth-child(4) { grid-column: 3; grid-row: 2; }
            .order-product-picker tbody td:nth-child(5) { grid-column: 4; grid-row: 2; }
            .order-product-picker td.order-product-picker__numeric { text-align: left; white-space: normal; overflow-wrap: anywhere; }
            .order-product-picker td[data-label]::before { content: attr(data-label); display: block; margin-bottom: 5px; font-size: 10px; font-weight: 500; line-height: 1.5; color: var(--order-muted); }
            .order-product-picker__product img { width: 38px; height: 38px; border-radius: 8px; }
            .order-product-picker__pagination.paginationLayout2 { padding: 10px 16px; justify-content: flex-end; }
            .order-product-picker__pagination .page-info { margin: 0 10px; }
            .order-product-picker__footer { flex-direction: column; align-items: stretch; gap: 12px; padding: 14px 16px; }
            .order-product-picker__footer p { max-width: none; font-size: 11px; }
            .order-product-picker__footer > div { display: grid; grid-template-columns: 1fr 1.4fr; }
            .order-product-picker__footer button { white-space: normal !important; }
        }
        @media (max-height: 600px) {
            .order-product-picker { max-height: calc(100dvh - 20px); }
            .order-product-picker__header { padding: 10px 16px; }
            .order-product-picker__header p { margin-top: 4px; font-size: 11px; }
            .order-product-picker__icon { width: 30px; height: 30px; }
            .order-product-picker__header h2 { font-size: 17px; }
            .order-product-picker__toolbar { padding: 0 16px 10px; gap: 8px; }
            .order-product-picker__pagination.paginationLayout2 { min-height: 44px; padding: 8px 16px; }
            .order-product-picker__footer { padding: 10px 16px; gap: 10px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .order-product-picker *, .order-product-picker *::before { transition: none !important; animation: none !important; }
        }
    </style>
</section>
