@extends('admin::shared.layout')
@php
    $toastTitles = [
        'success' => __('global.workspace.success'),
        'warning' => __('global.workspace.warning'),
    ];
@endphp
@section('layout')
    @include('admin::shared.header', ['header_name' => __('position.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('position.button.create'),
            'filterStatus' => true,
            'paymentStatusLabels' => [
                'Pending' => __('order.status.pending'),
                'Paid' => __('order.status.paid'),
            ],
            'emptyTitle' => __('position.empty.title'),
            'emptyMessage' => __('position.empty.description'),
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'title', 'title' => __('position.form.name.label'), 'class' => 'text left', 'colVal' => 90],
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
                selected_id: null,
                groupClassList: [],
                professionList: [],
                async init() {
                    this.loading = true;
                    // await this.fetchData('/admin/select/group-class', (res) => {
                    //     this.groupClassList = res;
                    // });
                    // await this.fetchData('/admin/select/profession', (res) => {
                    //     this.professionList = res;
                    // });
                    this.loading = false;
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
                    const confirmTemplate = @json(__('global.dialog.confirm_action', ['action' => '__ACTION__']));
                    this.$store.confirmDialog.open({
                        data: {
                            message: confirmTemplate.replace('__ACTION__', btn),
                            btnClose: @json(__('action_button.cancel')),
                            btnSave: btn,
                            trashLabel: @json(__('dialog.button.move_to_trash')),
                            requestFailed: @json(__('position.message.request_failed')),
                            item: data,
                            urlName: @json($routeName),
                            typeAction: typeAction,
                            digPosition: "posTop",
                            class: "deleteDialog",
                            width: "18rem"
                        },
                        afterClosed: (result) => {
                            if (result) {
                                let Url = `{{ url()->full() }}`;
                                reloadData(Url)
                            }
                        }
                    });
                },
                
            }))
        });
    </script>
@stop
