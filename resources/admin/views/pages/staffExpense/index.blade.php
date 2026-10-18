@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('staff_expense.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('staff_expense.button.record_expense'),
            'createPermission' => 'staff-expense-create',
            'filterStatus' => true,
            'exportAction' => 'excel()',
            'exportLabel' => __('global.button.export'),
            'exportUrl' => $exportUrl ?? '',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'staff_name', 'title' => __('staff_expense.table.staff_name'), 'class' => 'text left', 'colVal' => 15],
                ['field' => 'shop_name', 'title' => __('staff_expense.table.shop'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'type_badge', 'title' => __('staff_expense.table.type'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'formatted_amount', 'title' => __('staff_expense.table.amount'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'formatted_date', 'title' => __('staff_expense.table.expense_date'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'description', 'title' => __('staff_expense.table.remarks'), 'class' => 'text left', 'colVal' => 18],
                ['field' => 'creator_name', 'title' => __('staff_expense.table.recorded_by'), 'class' => 'text left', 'colVal' => 11],
                [
                    'field' => 'action',
                    'title' => '',
                    'class' => '',
                    'colVal' => 5,
                    'actions' => [
                        [
                            'key' => 'active',
                            'action' => [
                                ['url' => 'edit', 'title' => __('global.action.edit'), 'icon' => 'edit', 'type' => 'link'],
                                [
                                    'url' => 'delete',
                                    'title' => __('global.action.delete'),
                                    'icon' => 'Delete',
                                    'class' => 'text-danger',
                                ],
                            ],
                        ],
                        [
                            'key' => 'disable',
                            'action' => [
                                [
                                    'url' => 'status',
                                    'title' => __('global.action.disable'),
                                    'icon' => 'hide_source',
                                    'class' => 'text-danger',
                                ],
                            ],
                        ],
                        [
                            'key' => 'enable',
                            'action' => [['url' => 'status', 'title' => __('global.action.enable'), 'icon' => 'refresh']],
                        ],
                        [
                            'key' => 'trash',
                            'action' => [
                                [
                                    'url' => 'restore',
                                    'title' => __('global.action.restore'),
                                    'icon' => 'settings_backup_restore',
                                ],
                                [
                                    'url' => 'destroy',
                                    'title' => __('global.action.destroy'),
                                    'icon' => 'Delete',
                                    'class' => 'text-danger',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])
        @endcomponent

        <template x-if="exportLoading">
            <div class="loadingFullSizeLayout">
                <div class="loading loadingSubmit">
                    <span id="spinner"></span>
                    <label>{{ __('order.export_excel') }}</label>
                </div>
            </div>
        </template>
    </div>
@stop

@section('script')
    @include('admin::pages.report.partials.excel-assets')
    <script lang="ts">
        (function() {
            const registerXIndex = () => {
                Alpine.data('xIndex', () => ({
                    exportLoading: false,
                    loading: null,
                    async init() {
                        this.loading = false;
                    },
                    @include('admin::pages.report.partials.excel-request')
                    getExportParams() {
                        const params = new URLSearchParams(window.location.search);

                        if (window.MDI && Array.isArray(window.MDI.tabs) && window.MDI.activeTabKey) {
                            const currentTab = window.MDI.tabs.find(t => t.key === window.MDI.activeTabKey);
                            if (currentTab && currentTab.url && currentTab.url.includes('?')) {
                                const tabParams = new URLSearchParams(currentTab.url.split('?')[1]);
                                for (const [key, value] of tabParams.entries()) {
                                    if (value !== '' && !params.has(key)) {
                                        params.set(key, value);
                                    }
                                }
                            }
                        }

                        const initialQuery = @json(request()->query());
                        if (initialQuery && typeof initialQuery === 'object') {
                            for (const [key, value] of Object.entries(initialQuery)) {
                                if (value !== null && value !== undefined && value !== '' && !params.has(key)) {
                                    params.set(key, value);
                                }
                            }
                        }

                        const form = (this.$root ? this.$root.querySelector('form.filter') : null)
                            || document.querySelector('form.filter');
                        if (form) {
                            const formData = new FormData(form);
                            for (const [key, value] of formData.entries()) {
                                const trimmed = typeof value === 'string' ? value.trim() : value;
                                if (trimmed !== null && trimmed !== undefined && trimmed !== '') {
                                    params.set(key, trimmed);
                                } else if (params.has(key)) {
                                    params.delete(key);
                                }
                            }
                        }

                        if (!params.has('status')) {
                            params.set('status', '{{ $status }}');
                        }

                        params.delete('page');

                        return params;
                    },
                    async excel() {
                        this.exportLoading = true;
                        try {
                            const currentParams = this.getExportParams();
                            const reportData = await this.requestReportExport(currentParams);
                            await this.runExcelExport(reportData);
                        } catch (err) {
                            this.showReportExportError(err);
                        } finally {
                            this.exportLoading = false;
                        }
                    },
                    async exportExcel() {
                        return this.excel();
                    },
                    async runExcelExport(reportData) {
                        const workbook = new ExcelJS.Workbook();
                        const sheetName = @json(__('staff_expense.name'));
                        const worksheet = workbook.addWorksheet(sheetName);

                        const rows = Array.isArray(reportData) ? reportData : (reportData?.rows || []);

                        worksheet.columns = [
                            { header: @json(__('global.table.no')), key: 'index', width: 8 },
                            { header: @json(__('staff_expense.table.staff_name')), key: 'staff_name', width: 22 },
                            { header: @json(__('staff_expense.table.shop')), key: 'shop_name', width: 20 },
                            { header: @json(__('staff_expense.table.type')), key: 'type', width: 16 },
                            { header: @json(__('staff_expense.table.amount')), key: 'amount', width: 16 },
                            { header: @json(__('staff_expense.table.expense_date')), key: 'expense_date', width: 16 },
                            { header: @json(__('staff_expense.table.remarks')), key: 'description', width: 30 },
                            { header: @json(__('staff_expense.table.recorded_by')), key: 'created_by', width: 20 },
                            { header: @json(__('global.table.created_at')), key: 'created_at', width: 20 },
                        ];

                        rows.forEach((item, index) => {
                            const typeVal = typeof item?.type === 'object' && item?.type !== null
                                ? (item.type.value || item.type.label || '')
                                : (item?.type || '');
                            worksheet.addRow({
                                index: index + 1,
                                staff_name: item?.staff?.name || item?.staff_name || '---',
                                shop_name: item?.shop?.name || item?.shop_name || @json(__('staff_expense.all_shops')),
                                type: typeVal,
                                amount: Number(item?.amount || 0),
                                expense_date: item?.expense_date ? moment(item.expense_date).format('YYYY-MM-DD') : '',
                                description: item?.description || '',
                                created_by: item?.created_by?.name || item?.creator_name || '---',
                                created_at: item?.created_at ? moment(item.created_at).format('YYYY-MM-DD HH:mm:ss') : '',
                            });
                        });

                        worksheet.getRow(1).font = { bold: true };
                        worksheet.getRow(1).fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'FFE2E8F0' }
                        };

                        const buffer = await workbook.xlsx.writeBuffer();
                        const blob = new Blob([buffer], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        });
                        const filename = 'Staff_Expense_Report_' + moment().format('YYYY_MM_DD_HHmmss') + '.xlsx';
                        saveAs(blob, filename);
                    },
                    verifyDialog(data, typeAction, btn) {
                        const confirmTemplate = `{{ __('global.dialog.confirm_action', ['action' => '__ACTION__']) }}`;
                        const confirmMsg = confirmTemplate.replace('__ACTION__', btn);
                        this.$store.confirmDialog.open({
                            data: {
                                message: confirmMsg,
                                btnClose: `{{ __('action_button.cancel') }}`,
                                btnSave: btn,
                                item: data,
                                urlName: `{{$routeName}}`,
                                typeAction: typeAction,
                                digPosition: "posTop",
                                class: "deleteDialog",
                                width: "18rem"
                            },
                            afterClosed: (result) => {
                                if (result) {
                                    let Url = `{{ url()->full() }}`;
                                    reloadData(Url);
                                }
                            }
                        });
                    },
                }));
            };

            if (window.Alpine) {
                registerXIndex();
            } else {
                document.addEventListener('alpine:init', registerXIndex);
            }
        })();
    </script>
@stop
