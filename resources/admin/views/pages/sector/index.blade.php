@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => 'Sector Management'])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => 'Create Sector',
            'filterStatus' => true,
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => 'Nº', 'class' => '', 'colVal' => 5],
                ['field' => 'title', 'title' => 'Title', 'class' => 'text left', 'colVal' => 45],
                ['field' => 'pos_title', 'title' => 'Position', 'class' => 'text left', 'colVal' => 35],
                ['field' => 'created_date', 'title' => 'Post Date', 'class' => '', 'colVal' => 10],
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
                    this.$store.confirmDialog.open({
                        data: {
                            message: `Are you sure want to ${btn} ?`,
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
