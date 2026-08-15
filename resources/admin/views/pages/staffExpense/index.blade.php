@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => 'Staff Expense Management'])
    <div class="content-wrapper" id="app" x-data="xIndex">
        <!-- Financial Summary Metric Cards -->
        <div class="expense-summary-cards">
            <div class="card-col">
                <div class="expense-card salary">
                    <div class="card-info">
                        <span class="card-label">Base Salary</span>
                        <h4 class="card-value">${{ number_format($summary['salary'] ?? 0, 2) }}</h4>
                    </div>
                    <i class="bx bx-money card-icon"></i>
                </div>
            </div>
            <div class="card-col">
                <div class="expense-card bonus">
                    <div class="card-info">
                        <span class="card-label">Bonuses / Rewards</span>
                        <h4 class="card-value">${{ number_format($summary['bonus'] ?? 0, 2) }}</h4>
                    </div>
                    <i class="bx bx-gift card-icon"></i>
                </div>
            </div>
            <div class="card-col">
                <div class="expense-card deduction">
                    <div class="card-info">
                        <span class="card-label">Deductions</span>
                        <h4 class="card-value">${{ number_format($summary['deduction'] ?? 0, 2) }}</h4>
                    </div>
                    <i class="bx bx-minus-circle card-icon"></i>
                </div>
            </div>
            <div class="card-col">
                <div class="expense-card net-total">
                    <div class="card-info">
                        <span class="card-label">Net Total Expense</span>
                        <h4 class="card-value">${{ number_format($summary['netTotal'] ?? 0, 2) }}</h4>
                    </div>
                    <i class="bx bx-calculator card-icon"></i>
                </div>
            </div>
        </div>

        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => 'Record Expense',
            'filterStatus' => true,
            'exportUrl' => $exportUrl ?? '',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => 'Nº', 'class' => '', 'colVal' => 5],
                ['field' => 'staff_name', 'title' => 'Staff Name', 'class' => 'text left', 'colVal' => 15],
                ['field' => 'shop_name', 'title' => 'Shop', 'class' => 'text left', 'colVal' => 12],
                ['field' => 'type_badge', 'title' => 'Type', 'class' => 'text left', 'colVal' => 10],
                ['field' => 'formatted_amount', 'title' => 'Amount', 'class' => 'text left', 'colVal' => 12],
                ['field' => 'formatted_date', 'title' => 'Expense Date', 'class' => 'text left', 'colVal' => 12],
                ['field' => 'description', 'title' => 'Remarks', 'class' => 'text left', 'colVal' => 18],
                ['field' => 'creator_name', 'title' => 'Recorded By', 'class' => 'text left', 'colVal' => 11],
                [
                    'field' => 'action',
                    'title' => '',
                    'class' => '',
                    'colVal' => 5,
                    'actions' => [
                        [
                            'key' => 'active',
                            'action' => [
                                ['url' => 'edit', 'title' => 'Edit', 'icon' => 'edit', 'type' => 'link'],
                                [
                                    'url' => 'delete',
                                    'title' => 'Delete',
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
                                    'title' => 'Disable',
                                    'icon' => 'hide_source',
                                    'class' => 'text-danger',
                                ],
                            ],
                        ],
                        [
                            'key' => 'enable',
                            'action' => [['url' => 'status', 'title' => 'Enable', 'icon' => 'refresh']],
                        ],
                        [
                            'key' => 'trash',
                            'action' => [
                                [
                                    'url' => 'restore',
                                    'title' => 'Restore',
                                    'icon' => 'settings_backup_restore',
                                ],
                                [
                                    'url' => 'destroy',
                                    'title' => 'Destroy',
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
                    this.$store.confirmDialog.open({
                        data: {
                            message: `Are you sure want to ${btn} ?`,
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
