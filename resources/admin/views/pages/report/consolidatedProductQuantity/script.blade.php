<script>
    (() => {
        const register = () => Alpine.data('xConsolidatedProductQuantityReport', () => ({
            exportLoading: false,
            filterValues: {},
            init() {
                this.$nextTick(() => {
                    this.initDatepickers();
                    if (!window.jQuery || !$.fn.select2) return;
                    $(this.$refs.filterForm).find('select.select2').each((index, element) => {
                        const $select = $(element);
                        this.filterValues[element.name] = $select.val() || '';
                        if (!$select.data('select2')) {
                            $select.select2({ placeholder: $select.find('option[value=""]').text(), width: '100%' });
                        }
                        $select.off('.consolidatedQuantity')
                            .on('select2:open.consolidatedQuantity', () => {
                                if (typeof window.$select2FocusInputSearch === 'function') window.$select2FocusInputSearch();
                            }).on('change.consolidatedQuantity select2:select.consolidatedQuantity select2:clear.consolidatedQuantity select2:unselect.consolidatedQuantity', () => {
                                this.filterValues[element.name] = $select.val() || '';
                            });
                    });
                });
            },
            initDatepickers() {
                if (!window.jQuery || !$.fn.datepicker || !this.$refs.fromDate || !this.$refs.toDate) return;
                const $from = $(this.$refs.fromDate), $to = $(this.$refs.toDate);
                const options = {
                    dateFormat: 'yy-mm-dd', changeYear: true, changeMonth: true, gotoCurrent: true, yearRange: '-50:+10',
                    beforeShow(input, instance) { instance.dpDiv.addClass('consolidated-product-datepicker'); },
                    onClose(date, instance) { instance.dpDiv.removeClass('consolidated-product-datepicker'); },
                };
                const syncFrom = () => $to.datepicker('option', 'minDate', $from.val() ? $from.datepicker('getDate') : null);
                const syncTo = () => $from.datepicker('option', 'maxDate', $to.val() ? $to.datepicker('getDate') : null);
                if (!$from.hasClass('hasDatepicker')) $from.datepicker({ ...options, onSelect: syncFrom });
                if (!$to.hasClass('hasDatepicker')) $to.datepicker({ ...options, onSelect: syncTo });
                $from.off('change.consolidatedQuantity').on('change.consolidatedQuantity', syncFrom);
                $to.off('change.consolidatedQuantity').on('change.consolidatedQuantity', syncTo);
                syncFrom(); syncTo();
            },
            destroy() {
                if (!window.jQuery) return;
                $(this.$refs.filterForm).find('select.select2').each((index, element) => {
                    const $select = $(element).off('.consolidatedQuantity');
                    if ($select.data('select2')) $select.select2('destroy');
                });
                [this.$refs.fromDate, this.$refs.toDate].forEach(element => {
                    if (element && $(element).hasClass('hasDatepicker')) {
                        const $input = $(element);
                        if ($.datepicker._curInst?.input?.[0] === element) {
                            $input.datepicker('hide');
                            $.datepicker.dpDiv.stop(true, true).hide();
                        }
                        $input.off('.consolidatedQuantity').datepicker('destroy');
                    }
                });
            },
            resetFilter(name) {
                $(this.$refs.filterForm).find(`select[name="${name}"]`).val('').trigger('change');
            },
            @include('admin::pages.report.partials.excel-request')
            getExportParams() {
                const params = new URLSearchParams(window.location.search);
                if (window.MDI && Array.isArray(window.MDI.tabs)) {
                    const tab = window.MDI.tabs.find(tab => tab.key === window.MDI.activeTabKey);
                    if (tab?.url) {
                        new URL(tab.url, window.location.href).searchParams.forEach((value, key) => params.set(key, value));
                    }
                }
                const initialQuery = @json(request()->query());
                Object.entries(initialQuery).forEach(([key, value]) => {
                    if (value !== null && !params.has(key)) params.set(key, value);
                });
                const setValue = (key, value) => {
                    const text = String(value ?? '').trim();
                    if (text) params.set(key, text); else params.delete(key);
                };
                if (this.$refs.filterForm) {
                    for (const [key, value] of new FormData(this.$refs.filterForm)) setValue(key, value);
                }
                Object.entries(this.filterValues).forEach(([key, value]) => setValue(key, value));
                if (this.$refs.fromDate) setValue('from_date', this.$refs.fromDate.value);
                if (this.$refs.toDate) setValue('to_date', this.$refs.toDate.value);
                params.delete('page');
                const allowed = ['from_date', 'to_date', 'shop_id', 'category_id', 'product_id', 'search'];
                for (const key of [...params.keys()]) if (!allowed.includes(key)) params.delete(key);
                return params;
            },
            async excel() {
                if (this.exportLoading) return;
                this.exportLoading = true;
                try {
                    const data = await this.requestReportExport(this.getExportParams());
                    await this.runExcelExport(data);
                } catch (error) {
                    this.showReportExportError(error);
                } finally {
                    this.exportLoading = false;
                }
            },
            async exportExcel() { return this.excel(); },
            async runExcelExport(reportData) {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet(@json(__('consolidated_product_quantity.sheet_name')));
                worksheet.columns = [
                    { header: @json(__('global.table.no')), key: 'index', width: 8 },
                    { header: @json(__('consolidated_product_quantity.product_code')), key: 'product_code', width: 20 },
                    { header: @json(__('consolidated_product_quantity.product_name')), key: 'product_name', width: 36 },
                    { header: @json(__('consolidated_product_quantity.category_name')), key: 'category_name', width: 24 },
                    { header: @json(__('consolidated_product_quantity.uom_name')), key: 'uom_name', width: 16 },
                    { header: @json(__('consolidated_product_quantity.order_count')), key: 'order_count', width: 16 },
                    { header: @json(__('consolidated_product_quantity.total_quantity')), key: 'total_quantity', width: 20 },
                ];
                reportData.rows.forEach((row, index) => worksheet.addRow({
                    index: index + 1,
                    product_code: row.product_code || '—',
                    product_name: row.product_name || @json(__('consolidated_product_quantity.unknown_product')).replace(':id', row.product_id),
                    category_name: row.category_name || '—', uom_name: row.uom_name || '—',
                    order_count: Number(row.order_count), total_quantity: Number(row.total_quantity),
                }));
                const total = worksheet.addRow({
                    product_code: @json(__('consolidated_product_quantity.total')),
                    order_count: Number(reportData.summary.order_count), total_quantity: Number(reportData.summary.total_quantity),
                });
                total.font = { bold: true };
                for (const key of ['order_count', 'total_quantity']) {
                    worksheet.getColumn(key).numFmt = '#,##0';
                    worksheet.getColumn(key).alignment = { horizontal: 'right' };
                }
                worksheet.getRow(1).font = { bold: true };
                worksheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE2E8F0' } };
                worksheet.views = [{ state: 'frozen', ySplit: 1 }];
                const buffer = await workbook.xlsx.writeBuffer();
                saveAs(new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }),
                    'Consolidated_Product_Quantity_' + moment().format('YYYY_MM_DD_HHmmss') + '.xlsx');
            },
        }));
        if (window.Alpine) register(); else document.addEventListener('alpine:init', register, { once: true });
    })();
</script>
