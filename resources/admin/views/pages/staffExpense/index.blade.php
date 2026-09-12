@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('staff_expense.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        <!-- Financial Summary Metric Cards -->
        <div class="expense-summary-cards">
            <div class="card-col">
                <div class="expense-card salary">
                    <div class="card-info">
                        <span class="card-label">{{ __('staff_expense.summary.base_salary') }}</span>
                        <h4 class="card-value">${{ number_format($summary['salary'] ?? 0, 2) }}</h4>
                    </div>
                    <i class="bx bx-money card-icon"></i>
                </div>
            </div>
            <div class="card-col">
                <div class="expense-card bonus">
                    <div class="card-info">
                        <span class="card-label">{{ __('staff_expense.summary.bonus_rewards') }}</span>
                        <h4 class="card-value">${{ number_format($summary['bonus'] ?? 0, 2) }}</h4>
                    </div>
                    <i class="bx bx-gift card-icon"></i>
                </div>
            </div>
            <div class="card-col">
                <div class="expense-card deduction">
                    <div class="card-info">
                        <span class="card-label">{{ __('staff_expense.summary.deductions') }}</span>
                        <h4 class="card-value">${{ number_format($summary['deduction'] ?? 0, 2) }}</h4>
                    </div>
                    <i class="bx bx-minus-circle card-icon"></i>
                </div>
            </div>
            <div class="card-col">
                <div class="expense-card net-total">
                    <div class="card-info">
                        <span class="card-label">{{ __('staff_expense.summary.net_total_expense') }}</span>
                        <h4 class="card-value">${{ number_format($summary['netTotal'] ?? 0, 2) }}</h4>
                    </div>
                    <i class="bx bx-calculator card-icon"></i>
                </div>
            </div>
        </div>

        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('staff_expense.button.record_expense'),
            'createPermission' => 'staff-expense-create',
            'filterStatus' => true,
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
    </div>
@stop

@section('script')
    <script lang="ts">
        document.addEventListener('alpine:init', () => {
            Alpine.data('xIndex', () => ({
                loading: null,
                async init() {
                    this.loading = false;
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
        });
    </script>
@stop
