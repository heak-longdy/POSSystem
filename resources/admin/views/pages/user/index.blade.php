@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('user.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('user.button.create'),
            'filterStatus' => true,
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('user.table.no'), 'class' => '', 'colVal' => 5],
                [
                    'field' => 'image_url',
                    'title' => __('user.table.profile'),
                    'class' => 'text left',
                    'colVal' => 6,
                    'rowClass' => 'thumbnailIcon',
                ],
                ['field' => 'name', 'title' => __('user.table.name'), 'class' => 'text left', 'colVal' => 34],
                ['field' => 'email', 'title' => __('user.table.email'), 'class' => 'text left', 'colVal' => 30],
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
                                reloadData(Url)
                            }
                        }
                    });
                },

            }))
        });
    </script>
@stop
