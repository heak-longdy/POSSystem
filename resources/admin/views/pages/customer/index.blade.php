@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('customer.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('customer.button.create'),
            'filterStatus' => true,
            'exportAction' => 'excel()',
            'exportLabel' => __('global.button.export'),
            'exportUrl' => $exportUrl ?? '',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'image_url', 'title' => __('global.table.image'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'code', 'title' => __('customer.table.code'), 'class' => 'text left', 'colVal' => 12],
                ['field' => 'name', 'title' => __('global.table.name'), 'class' => 'text left', 'colVal' => 23],
                ['field' => 'phone', 'title' => __('customer.form.phone.label'), 'class' => 'text left', 'colVal' => 18],
                ['field' => 'address', 'title' => __('customer.form.address.label'), 'class' => 'text left', 'colVal' => 27],
                [
                    'field' => 'action',
                    'title' => "",
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
                    selected_id: null,
                    groupClassList: [],
                    professionList: [],
                    async init() {
                        this.loading = true;
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
                        const sheetName = @json(__('customer.name'));
                        const worksheet = workbook.addWorksheet(sheetName);

                        const rows = Array.isArray(reportData) ? reportData : (reportData?.rows || []);

                        worksheet.columns = [
                            { header: @json(__('global.table.no')), key: 'index', width: 8 },
                            { header: @json(__('customer.table.code')), key: 'code', width: 16 },
                            { header: @json(__('global.table.name')), key: 'name', width: 28 },
                            { header: @json(__('customer.form.phone.label')), key: 'phone', width: 20 },
                            { header: @json(__('customer.form.address.label')), key: 'address', width: 40 },
                            { header: @json(__('global.table.created_at')), key: 'created_at', width: 20 },
                        ];

                        rows.forEach((item, index) => {
                            const code = item?.code || (item?.id ? String(item.id).padStart(10, '0') : '');
                            worksheet.addRow({
                                index: index + 1,
                                code: code,
                                name: item?.name || '',
                                phone: item?.phone || '',
                                address: item?.address || '',
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
                        const filename = 'Customer_Report_' + moment().format('YYYY_MM_DD_HHmmss') + '.xlsx';
                        saveAs(blob, filename);
                    },
                    async fetchData(url, callback) {
                        await fetch(url, {
                                method: "GET",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Accept": "application/json",
                                }
                            })
                            .then(response => response.json())
                            .then(response => {
                                callback(response);
                            })
                            .catch((e) => {})
                            .finally(async (res) => {});
                    },
                    storeDialog(data = null, type = null) {
                        this.$store.store.open({
                            data: data,
                            type: type
                        });
                    },
                    verifyDialog(data, typeAction, btn) {
                        console.log(btn, 'btn');
                        const confirmTemplate = `{{ __('global.dialog.confirm_action', ['action' => '__ACTION__']) }}`;
                        const confirmMsg = confirmTemplate.replace('__ACTION__', btn);
                        this.$store.confirmDialog.open({
                            data: {
                                message: confirmMsg,
                                btnClose: `{{ __('action_button.cancel') }}`,
                                btnSave: btn,
                                item: data,
                                urlName:`{{$routeName}}`,
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
