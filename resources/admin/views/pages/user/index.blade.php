@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('user.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('user.button.create'),
            'filterStatus' => true,
            'exportAction' => 'excel()',
            'exportLabel' => __('global.button.export'),
            'exportUrl' => $exportUrl ?? '',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('user.table.no'), 'class' => '', 'colVal' => 5],
                [
                    'field' => 'image_url',
                    'title' => __('user.table.profile'),
                    'class' => 'text left',
                    'colVal' => 10,
                    'rowClass' => 'thumbnailIcon',
                ],
                ['field' => 'name', 'title' => __('user.table.name'), 'class' => 'text left', 'colVal' => 25],
                ['field' => 'email', 'title' => __('user.table.email'), 'class' => 'text left', 'colVal' => 20],
                ['field' => 'data_access_badge', 'title' => __('user.table.data_access'), 'class' => 'text center', 'colVal' => 15],
                ['field' => 'language_preference_label', 'title' => __('user.table.language'), 'class' => 'text left', 'colVal' => 10],
                ['field' => 'created_date', 'title' => __('user.table.post_date'), 'class' => '', 'colVal' => 10],
                [
                    'field' => 'action',
                    'title' => '',
                    'class' => '',
                    'colVal' => 5,
                    'actions' => [
                        [
                            'key' => 'active',
                            'action' => [
                                ['url' => 'edit', 'title' => __('action_button.edit'), 'icon' => 'edit', 'type' => 'link'],
                                [
                                    'url' => 'permission',
                                    'title' => __('action_button.permission'),
                                    'icon' => 'security',
                                    'type' => 'link',
                                ],
                                [
                                    'url' => 'change-password',
                                    'title' => __('action_button.change_password'),
                                    'icon' => 'password',
                                    'type' => 'link',
                                ],
                                [
                                    'url' => 'delete',
                                    'title' => __('action_button.delete'),
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
                                    'title' => __('action_button.disable'),
                                    'icon' => 'hide_source',
                                    'class' => 'text-danger',
                                ],
                            ],
                        ],
                        [
                            'key' => 'enable',
                            'action' => [['url' => 'status', 'title' => __('action_button.enable'), 'icon' => 'refresh']],
                        ],
                        [
                            'key' => 'trash',
                            'action' => [
                                [
                                    'url' => 'restore',
                                    'title' => __('action_button.restore'),
                                    'icon' => 'settings_backup_restore',
                                ],
                                [
                                    'url' => 'destroy',
                                    'title' => __('action_button.destroy'),
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
                        const sheetName = @json(__('user.name'));
                        const worksheet = workbook.addWorksheet(sheetName);

                        const rows = Array.isArray(reportData) ? reportData : (reportData?.rows || []);

                        worksheet.columns = [
                            { header: @json(__('user.table.no')), key: 'index', width: 8 },
                            { header: @json(__('user.table.name')), key: 'name', width: 25 },
                            { header: @json(__('user.table.email')), key: 'email', width: 28 },
                            { header: @json(__('user.form.phone.label')), key: 'phone', width: 18 },
                            { header: @json(__('user.table.language')), key: 'language', width: 16 },
                            { header: @json(__('user.table.role')), key: 'role', width: 18 },
                            { header: @json(__('user.table.status')), key: 'status', width: 14 },
                            { header: @json(__('user.table.post_date')), key: 'created_at', width: 22 },
                        ];

                        const rolesMap = @json(__('user.roles'));
                        const statusActiveLabel = @json(__('user.form.status.active'));
                        const statusDisableLabel = @json(__('user.form.status.disable'));

                        rows.forEach((item, index) => {
                            let roleName = '';
                            if (item?.role) {
                                roleName = rolesMap && rolesMap[item.role] ? rolesMap[item.role] : item.role;
                            }
                            const statusLabel = item?.status == 1 ? statusActiveLabel : statusDisableLabel;

                            worksheet.addRow({
                                index: index + 1,
                                name: item?.name || '',
                                email: item?.email || '',
                                phone: item?.phone || '',
                                language: item?.language_preference_label || (item?.language_preference ? item.language_preference.toUpperCase() : ''),
                                role: roleName,
                                status: statusLabel,
                                created_at: item?.created_at ? moment(item.created_at).format('YYYY-MM-DD HH:mm:ss') : (item?.created_date || ''),
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
                        const filename = 'User_Report_' + moment().format('YYYY_MM_DD_HHmmss') + '.xlsx';
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
