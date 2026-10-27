@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('other_income.title')])
    <div class="content-wrapper" id="app" x-data="xOtherIncomeApp">

        {{-- Main Table Listing Component --}}
        @component('admin::components.listingData', [
            'routeName'        => $routeName,
            'createName'       => __('other_income.button.create'),
            'createPermission' => 'other-income-create',
            'filterStatus'     => true,
            'exportAction'     => 'excel()',
            'exportLabel'      => __('global.button.export'),
            'exportUrl'        => $exportUrl ?? '',
            'data'             => $data,
            'status'           => $status,
            'tbHeader'         => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'income_number', 'title' => __('other_income.table.income_number'), 'class' => 'text left', 'colVal' => 14],
                ['field' => 'formatted_date', 'title' => __('other_income.table.income_date'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'title', 'title' => __('other_income.table.title'), 'class' => 'text left', 'colVal' => 22],
                ['field' => 'category_name', 'title' => __('other_income.table.category'), 'class' => 'text left', 'colVal' => 15],
                ['field' => 'supplier_name', 'title' => __('other_income.table.supplier'), 'class' => 'text left', 'colVal' => 15],
                ['field' => 'formatted_amount', 'title' => __('other_income.table.amount'), 'class' => 'text left', 'colVal' => 12],
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

        {{-- Full Screen Loading Spinner Overlay --}}
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
    <script>
        (function() {
            const registerXIndex = () => {
                Alpine.data('xOtherIncomeApp', () => ({
                    exportLoading: false,

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
                        const sheetName = @json(__('other_income.name'));
                        const worksheet = workbook.addWorksheet(sheetName);

                        const rows = Array.isArray(reportData) ? reportData : (reportData?.rows || []);

                        worksheet.columns = [
                            { header: @json(__('global.table.no')), key: 'index', width: 8 },
                            { header: @json(__('other_income.table.income_number')), key: 'income_number', width: 16 },
                            { header: @json(__('other_income.table.income_date')), key: 'income_date', width: 14 },
                            { header: @json(__('other_income.table.title')), key: 'title', width: 28 },
                            { header: @json(__('other_income.table.category')), key: 'category_name', width: 20 },
                            { header: @json(__('other_income.table.supplier')), key: 'supplier_name', width: 22 },
                            { header: @json(__('other_income.table.shop')), key: 'shop_name', width: 18 },
                            { header: @json(__('other_income.table.amount')), key: 'amount', width: 18 },
                            { header: @json(__('other_income.table.reference_no')), key: 'reference_no', width: 18 },
                            { header: @json(__('other_income.table.remarks')), key: 'description', width: 30 },
                            { header: @json(__('other_income.table.recorded_by')), key: 'created_by_name', width: 18 },
                        ];

                        rows.forEach((item, index) => {
                            worksheet.addRow({
                                index: index + 1,
                                income_number: item?.income_number || '',
                                income_date: item?.income_date ? moment(item.income_date).format('YYYY-MM-DD') : '',
                                title: item?.title || '',
                                category_name: item?.category?.name || item?.category_name || '---',
                                supplier_name: item?.supplier?.name || item?.supplier_name || '---',
                                shop_name: item?.shop?.name || item?.shop_name || @json(__('other_income.all_shops')),
                                amount: Number(item?.received_amount || 0),
                                reference_no: item?.reference_no || '',
                                description: item?.description || '',
                                created_by_name: item?.created_by?.name || item?.created_by_name || '---',
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
                        const filename = 'Other_Income_Report_' + moment().format('YYYY_MM_DD_HHmmss') + '.xlsx';
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
