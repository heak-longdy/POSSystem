<script>
    Alpine.data('dashboardDateFilter', () => ({
        fromDate: '',
        toDate: '',
        draftFromDate: '',
        draftToDate: '',
        draftPreset: 'this_month',
        productMonth: 'all',
        showFilter: false,
        loading: false,
        validationError: '',
        requestError: '',
        invalidField: '',
        requestController: null,

        getRootElement() {
            return (this.$root && this.$root.nodeType === 1)
                ? this.$root
                : (document.querySelector('.dashboard-container') || document.getElementById('dashboard-container'));
        },

        getDashboardContent() {
            const rootEl = this.getRootElement();
            return (this.$refs && this.$refs.dashboardContent)
                || rootEl?.querySelector('[x-ref="dashboardContent"]')
                || document.getElementById('dashboard-content-area')
                || document.querySelector('.dashboard-container [x-ref="dashboardContent"]')
                || rootEl;
        },

        getDashboardUrl() {
            const rootEl = this.getRootElement();
            const raw = rootEl?.dataset?.dashboardUrl || '{{ route('admin-dashboard', [], false) }}';
            try {
                const parsed = new URL(raw, window.location.origin);
                return `${window.location.origin}${parsed.pathname}`;
            } catch (_) {
                return `${window.location.origin}/admin/dashboard`;
            }
        },

        init() {
            const rootEl = this.getRootElement();
            this.fromDate = rootEl?.dataset?.fromDate || '';
            this.toDate = rootEl?.dataset?.toDate || '';
            if (!this.fromDate && !this.toDate && rootEl?.dataset?.allTime !== '1') {
                const presets = this.getPresetDates();
                const [start, end] = presets.this_month;
                this.fromDate = start;
                this.toDate = end;
            }
            this.draftFromDate = this.fromDate;
            this.draftToDate = this.toDate;
            this.productMonth = rootEl?.dataset?.productMonth || 'all';
            this.updateDraftPresetFromInputs();
            this.$nextTick(() => this.initDatepickers());
        },

        get activeRangeSummary() {
            if (this.fromDate && this.toDate) {
                return `${this.fromDate} — ${this.toDate}`;
            }
            return @js(__('dashboard.filter.ranges.all_time'));
        },

        get draftRangeSummary() {
            const from = (this.draftFromDate || '').trim();
            const to = (this.draftToDate || '').trim();
            if (from && to) {
                return `${from} — ${to}`;
            }
            if (from && !to) {
                return `${from} — ...`;
            }
            if (!from && to) {
                return `... — ${to}`;
            }
            return @js(__('dashboard.filter.ranges.all_time'));
        },

        formatDate(d) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        getPresetDates() {
            const now = new Date();
            const todayStr = this.formatDate(now);

            const y = new Date(now);
            y.setDate(y.getDate() - 1);
            const yesterdayStr = this.formatDate(y);

            const d7 = new Date(now);
            d7.setDate(d7.getDate() - 6);
            const last7Str = this.formatDate(d7);

            const d30 = new Date(now);
            d30.setDate(d30.getDate() - 29);
            const last30Str = this.formatDate(d30);

            const tmStart = this.formatDate(new Date(now.getFullYear(), now.getMonth(), 1));
            const tmEnd = this.formatDate(new Date(now.getFullYear(), now.getMonth() + 1, 0));

            const lmStart = this.formatDate(new Date(now.getFullYear(), now.getMonth() - 1, 1));
            const lmEnd = this.formatDate(new Date(now.getFullYear(), now.getMonth(), 0));

            const tyStart = this.formatDate(new Date(now.getFullYear(), 0, 1));
            const tyEnd = this.formatDate(new Date(now.getFullYear(), 11, 31));

            return {
                today: [todayStr, todayStr],
                yesterday: [yesterdayStr, yesterdayStr],
                last_7_days: [last7Str, todayStr],
                last_30_days: [last30Str, todayStr],
                this_month: [tmStart, tmEnd],
                last_month: [lmStart, lmEnd],
                this_year: [tyStart, tyEnd],
                all_time: ['', ''],
            };
        },

        getInputs() {
            const from = this.$refs.fromDate || document.querySelector('.dashboard-date-dialog input[name="from_date"]');
            const to = this.$refs.toDate || document.querySelector('.dashboard-date-dialog input[name="to_date"]');
            return { from, to };
        },

        updateDraftPresetFromInputs() {
            const { from: fromEl, to: toEl } = this.getInputs();
            const from = (this.draftFromDate || (fromEl ? fromEl.value : '')).trim();
            const to = (this.draftToDate || (toEl ? toEl.value : '')).trim();
            if (!from && !to) {
                this.draftPreset = 'all_time';
                return;
            }
            const presets = this.getPresetDates();
            for (const [key, [pFrom, pTo]] of Object.entries(presets)) {
                if (key !== 'all_time' && from === pFrom && to === pTo) {
                    this.draftPreset = key;
                    return;
                }
            }
            this.draftPreset = 'custom';
        },

        selectPreset(range) {
            if (this.loading) return;
            this.validationError = '';
            this.invalidField = '';
            this.draftPreset = range;

            if (range === 'custom') {
                this.openCalendar('fromDate');
                return;
            }

            const presets = this.getPresetDates();
            if (presets[range]) {
                const [from, to] = presets[range];
                this.draftFromDate = from;
                this.draftToDate = to;
                this.setInputsDate(from, to);
            }
        },

        openCalendar(field) {
            if (this.loading) return;
            const { from: fromEl, to: toEl } = this.getInputs();
            const input = field === 'fromDate' ? fromEl : toEl;
            if (!input) return;
            input.focus();
            if (window.jQuery && $.fn.datepicker) {
                $(input).datepicker('show');
            }
        },

        setInputsDate(from, to) {
            const { from: fromEl, to: toEl } = this.getInputs();
            if (!fromEl || !toEl) return;
            const $from = $(fromEl), $to = $(toEl);
            if (window.jQuery && $.fn.datepicker) {
                $from.datepicker('option', 'maxDate', null).datepicker('setDate', from || null);
                $to.datepicker('option', 'minDate', null).datepicker('setDate', to || null);
            }
            $from.val(from || '');
            $to.val(to || '');
            this.syncDateLimits();
        },

        initDatepickers() {
            const { from: fromEl, to: toEl } = this.getInputs();
            if (!window.jQuery || !$.fn.datepicker || !fromEl || !toEl) return;
            const options = {
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                gotoCurrent: true,
                yearRange: '-50:+10',
                beforeShow(input, instance) {
                    instance.dpDiv.addClass('dashboard-datepicker');
                },
                onClose(dateText, instance) {
                    instance.dpDiv.removeClass('dashboard-datepicker');
                },
                onSelect: () => this.syncDateLimits(),
            };
            [fromEl, toEl].forEach(input => {
                if ($(input).hasClass('hasDatepicker')) return;
                $(input).datepicker(options).on('change.dashboardFilter input.dashboardFilter', () => this.syncDateLimits());
            });
        },

        validDate(value) {
            if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
            try {
                if (window.jQuery && $.datepicker) {
                    return $.datepicker.formatDate('yy-mm-dd', $.datepicker.parseDate('yy-mm-dd', value)) === value;
                }
                const [y, m, d] = value.split('-').map(Number);
                const dt = new Date(y, m - 1, d);
                return dt.getFullYear() === y && dt.getMonth() === m - 1 && dt.getDate() === d;
            } catch (_) {
                return false;
            }
        },

        syncDateLimits() {
            const { from: fromEl, to: toEl } = this.getInputs();
            if (!fromEl || !toEl) return;
            const $from = $(fromEl), $to = $(toEl);
            const from = $from.val()?.trim() || '';
            const to = $to.val()?.trim() || '';
            this.draftFromDate = from;
            this.draftToDate = to;

            // Retain manual input for validation instead of silently clamping a reversed range.
            if (window.jQuery && $.fn.datepicker && $from.hasClass('hasDatepicker') && $to.hasClass('hasDatepicker')) {
                $from.datepicker('option', 'maxDate', null).val(from);
                $to.datepicker('option', 'minDate', null).val(to);
                const fromDate = this.validDate(from) ? $from.datepicker('getDate') : null;
                const toDate = this.validDate(to) ? $to.datepicker('getDate') : null;
                $from.datepicker('option', 'maxDate', toDate).val(from);
                $to.datepicker('option', 'minDate', fromDate).val(to);
            }

            this.updateDraftPresetFromInputs();
            this.validationError = '';
            this.invalidField = '';
        },

        openFilter() {
            if (this.loading) return;
            this.validationError = '';
            this.requestError = '';
            this.invalidField = '';
            this.draftFromDate = this.fromDate || '';
            this.draftToDate = this.toDate || '';
            this.showFilter = true;
            this.$nextTick(() => {
                this.initDatepickers();
                const { from: fromEl, to: toEl } = this.getInputs();
                if (fromEl && toEl) {
                    const $from = $(fromEl), $to = $(toEl);
                    if (window.jQuery && $.fn.datepicker && $from.hasClass('hasDatepicker')) {
                        $from.datepicker('option', 'maxDate', null).datepicker('setDate', this.fromDate || null);
                    }
                    if (window.jQuery && $.fn.datepicker && $to.hasClass('hasDatepicker')) {
                        $to.datepicker('option', 'minDate', null).datepicker('setDate', this.toDate || null);
                    }
                    $from.val(this.fromDate || '');
                    $to.val(this.toDate || '');
                }
                this.syncDateLimits();
                const closeBtn = this.$refs.dialogClose || document.querySelector('.dashboard-date-dialog .dashboard-filter-close');
                closeBtn?.focus();
            });
        },

        closeFilter() {
            if (this.loading) return;
            this.hideCalendar();
            this.showFilter = false;
            this.validationError = '';
            this.invalidField = '';
            const btn = (this.$refs && this.$refs.filterButton) || document.querySelector('.dashboard-filter-button');
            this.$nextTick(() => btn?.focus());
        },

        hideCalendar() {
            const { from: fromEl, to: toEl } = this.getInputs();
            if (window.jQuery && $.datepicker && [fromEl, toEl].includes($.datepicker._curInst?.input?.[0])) {
                $($.datepicker._curInst.input).datepicker('hide');
            }
            if (window.jQuery) {
                $('#ui-datepicker-div').removeClass('dashboard-datepicker');
            }
        },

        trapDialogFocus(event) {
            const controls = Array.from(event.currentTarget.querySelectorAll('button:not(:disabled), input:not(:disabled)'));
            if (!controls.length) return;
            const first = controls[0], last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },

        async applyFilter() {
            const { from: fromEl, to: toEl } = this.getInputs();
            const from = (fromEl ? fromEl.value : '').trim();
            const to = (toEl ? toEl.value : '').trim();
            this.requestError = '';
            this.invalidField = '';

            if (!from && !to && this.draftPreset === 'all_time') {
                if (await this.refreshDashboard('', '', this.productMonth)) this.closeFilter();
                return;
            }

            if (!from || !to) {
                this.validationError = @js(__('dashboard.filter.dates_required'));
                this.invalidField = !from ? 'fromDate' : 'toDate';
                return;
            }
            if (!this.validDate(from)) {
                this.validationError = @js(__('dashboard.filter.invalid_date'));
                this.invalidField = 'fromDate';
                return;
            }
            if (!this.validDate(to)) {
                this.validationError = @js(__('dashboard.filter.invalid_date'));
                this.invalidField = 'toDate';
                return;
            }
            if (from > to) {
                this.validationError = @js(__('dashboard.filter.invalid_range'));
                this.invalidField = 'toDate';
                return;
            }
            this.validationError = '';
            this.invalidField = '';
            if (await this.refreshDashboard(from, to, this.productMonth)) {
                this.closeFilter();
            }
        },

        async resetFilter() {
            this.validationError = '';
            this.invalidField = '';
            const presets = this.getPresetDates();
            const [from, to] = presets.this_month;
            this.draftPreset = 'this_month';
            this.draftFromDate = from;
            this.draftToDate = to;
            this.setInputsDate(from, to);
            if (await this.refreshDashboard(from, to, 'all')) {
                this.closeFilter();
            }
        },

        async changeProductMonth(month) {
            if (!await this.refreshDashboard(this.fromDate, this.toDate, month, true)) {
                const select = this.getDashboardContent()?.querySelector('[data-product-month]');
                if (select) select.value = this.productMonth;
            }
        },

        async refreshDashboard(from, to, month, productOnly = false) {
            if (this.loading) return false;
            this.hideCalendar();
            this.loading = true;
            this.requestError = '';
            this.requestController = new AbortController();
            const url = new URL(this.getDashboardUrl());
            url.searchParams.set('ajax', productOnly ? 'product_performance' : 'dashboard');
            url.searchParams.set('product_month', month);
            if (from && to) {
                url.searchParams.set('from_date', from);
                url.searchParams.set('to_date', to);
                url.searchParams.delete('all_time');
            } else {
                url.searchParams.set('all_time', '1');
                url.searchParams.delete('from_date');
                url.searchParams.delete('to_date');
            }
            try {
                const response = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: this.requestController.signal,
                });
                if (response.redirected || response.status === 401 || response.status === 419) {
                    throw new Error(@js(__('dashboard.filter.session_expired')));
                }
                if (response.status === 403) throw new Error(@js(__('dashboard.filter.forbidden')));
                if (response.status === 422) {
                    const data = await response.json();
                    throw new Error(Object.values(data.errors || {}).flat()[0] || @js(__('dashboard.filter.invalid_range')));
                }
                if (!response.ok) {
                    throw new Error(@js(__('dashboard.filter.failed')));
                }
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    throw new Error(@js(__('dashboard.filter.failed')));
                }
                const data = await response.json();
                if (!data || typeof data.html !== 'string') throw new Error(@js(__('dashboard.filter.failed')));
                const contentEl = this.getDashboardContent();
                const target = productOnly
                    ? contentEl?.querySelector('[data-product-performance-rows]')
                    : contentEl;
                if (!target) {
                    console.error('Target dashboard content element not found:', contentEl);
                    throw new Error(@js(__('dashboard.filter.failed')));
                }
                target.innerHTML = data.html;
                this.fromDate = from;
                this.toDate = to;
                this.productMonth = month;
                return true;
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Dashboard refresh failed:', error);
                    this.requestError = error instanceof TypeError ? @js(__('dashboard.filter.failed')) : error.message;
                }
                return false;
            } finally {
                this.loading = false;
                this.requestController = null;
            }
        },

        destroy() {
            this.requestController?.abort();
            this.hideCalendar();
            this.showFilter = false;
            const { from: fromEl, to: toEl } = this.getInputs();
            if (!window.jQuery || !$.fn.datepicker) return;
            [fromEl, toEl].forEach(input => {
                if (input && $(input).hasClass('hasDatepicker')) {
                    $(input).off('.dashboardFilter').datepicker('destroy');
                }
            });
        },
    }));
</script>
