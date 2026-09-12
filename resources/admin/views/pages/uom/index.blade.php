@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('uom.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('uom.button.create'),
            'filterStatus' => true,
            'exportUrl' => $exportUrl ?? '',
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                // ['field' => 'image_url', 'title' => 'Image', 'class' => 'text left', 'colVal' => 10],
                ['field' => 'name', 'title' => __('global.table.name'), 'class' => 'text left', 'colVal' => 90],
                // ['field' => 'sector_title', 'title' => 'Sector', 'class' => 'text left', 'colVal' => 15],
                // ['field' => 'month', 'title' => 'Month', 'class' => 'text left', 'colVal' => 10],
                // ['field' => 'post_date_for', 'title' => 'Start Date', 'class' => 'text left', 'colVal' => 10],
                // ['field' => 'close_date_for', 'title' => 'Close Date', 'class' => 'text left', 'colVal' => 10],
                // ['field' => 'salary_from', 'title' => 'Amount ($)', 'class' => 'text left', 'colVal' => 10],
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
                    // const data = @json($data ?? '');
                    // console.log(data,'dd');
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
                                reloadData(Url)
                            }
                        }
                    });
                },
                
            }))
        });
    </script>
@stop
