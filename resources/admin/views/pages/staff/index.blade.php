@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('staff.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('staff.button.create'),
            'filterStatus' => true,
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                [
                    'field' => 'image_url',
                    'title' => __('global.table.image'),
                    'class' => 'text left',
                    'colVal' => 6,
                    'rowClass' => 'thumbnailIcon',
                ],
                ['field' => 'name', 'title' => __('global.table.name'), 'class' => 'text left', 'colVal' => 20],
                ['field' => 'position.title', 'title' => __('global.table.position'), 'class' => 'text left', 'colVal' => 15],
                ['field' => 'phone_number', 'title' => __('global.table.phone'), 'class' => 'text left', 'colVal' => 15],
                ['field' => 'email', 'title' => __('global.table.email'), 'class' => 'text left', 'colVal' => 18],
                ['field' => 'address', 'title' => __('global.table.address'), 'class' => 'text left', 'colVal' => 16],
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
                                reloadData(Url)
                            }
                        }
                    });
                },
            }))
        });
    </script>
@stop
