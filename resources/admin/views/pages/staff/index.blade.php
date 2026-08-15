@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => 'Staff Management'])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => 'Create Staff',
            'filterStatus' => true,
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => 'Nº', 'class' => '', 'colVal' => 5],
                [
                    'field' => 'image_url',
                    'title' => 'Image',
                    'class' => 'text left',
                    'colVal' => 6,
                    'rowClass' => 'thumbnailIcon',
                ],
                ['field' => 'name', 'title' => 'Name', 'class' => 'text left', 'colVal' => 20],
                ['field' => 'position.title', 'title' => 'Position', 'class' => 'text left', 'colVal' => 15],
                ['field' => 'phone_number', 'title' => 'Phone Number', 'class' => 'text left', 'colVal' => 15],
                ['field' => 'email', 'title' => 'Email', 'class' => 'text left', 'colVal' => 18],
                ['field' => 'address', 'title' => 'Address', 'class' => 'text left', 'colVal' => 16],
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
                                reloadData(Url)
                            }
                        }
                    });
                },
            }))
        });
    </script>
@stop
