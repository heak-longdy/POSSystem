@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => 'Partner Management'])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => 'partner',
            'createName' => 'Create Partner',
            'filterStatus' => true,
            'data' => $data,
            'status' => $status,
            'routeName' => 'partner',
            'tbHeader' => [
                ['field' => 'index', 'title' => 'Nº', 'class' => '', 'colVal' => 5],
                ['field' => 'image_url', 'title' => 'Logo', 'class' => 'text left', 'colVal' => 10],
                ['field' => 'created_date', 'title' => 'Post Date', 'class' => '', 'colVal' => 80],
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
                    await this.fetchData('/admin/select/group-class', (res) => {
                        this.groupClassList = res;
                    });
                    await this.fetchData('/admin/select/profession', (res) => {
                        this.professionList = res;
                    });
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
                // statusDialog(data) {
                //     this.$store.confirmDialog.open({
                //         data: {
                //             title: `{{ __('message.title') }}`,
                //             message: `{{ __('message.alert_message_delete') }}`,
                //             btnClose: `{{ __('action_button.no') }}`,
                //             btnSave: `{{ __('action_button.yes') }}`,
                //             item: data,
                //             typeAction: 'updateStatus'
                //         },
                //         afterClosed: (result) => {
                //             if (result) {
                //                 let Url = `{{ url()->full() }}`;
                //                 reloadUrl(Url)
                //             }
                //         }
                //     });
                // },
                verifyDialog(data, typeAction, btn) {
                    console.log(btn, 'btn');

                    //    const btn = 'action_button.'+typeAction;
                    //    console.log(`@lang('${btn}')`,'ddddd');
                    //    const btnSave = translations.action_button[typeAction];
                    this.$store.confirmDialog.open({
                        data: {
                            message: `Are you sure want to ${btn} ?`,
                            btnClose: `{{ __('action_button.cancel') }}`,
                            btnSave: btn,
                            item: data,
                            urlName: 'partner',
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
                // deleteDialog(item) {
                //     this.$store.confirmDialog.open({
                //         data: {
                //             title: "Delete Invoice",
                //             message: "Are you sure to delete file can't restore when delete ?",
                //             btnClose: "Close",
                //             btnSave: "Yes",
                //             digPosition:"posTop",
                //         },
                //         afterClosed: (result) => {
                //             if (result) {
                //                 this.submitLoading = true;
                //                 setTimeout(() => {
                //                     Axios({
                //                         url: `#`,
                //                         method: 'POST',
                //                         data: {
                //                             id: item?.id,
                //                         }
                //                     }).then((res) => {
                //                         this.submitLoading = false;
                //                         if (res.data == "success") {
                //                             let currentFullUrl =
                //                                 '{!! url()->full() !!}';
                //                             reloadUrl(currentFullUrl);
                //                         }
                //                     }).catch((e) => {
                //                         this.submitLoading = false;

                //                     }).finally(() => {
                //                         this.submitLoading = false;
                //                     });
                //                 }, 500);
                //             }
                //         }
                //     });
                // },
            }))
        });
        $("body").on("click", ".trash-btn", function() {
            let url = $(this).data('url');
            let id = url.split('/').pop();
            let row = $(this).closest('.column');
            Swal.fire({
                customClass: "confirm-message",
                icon: "warning",
                html: `Are you sure to delete <b>${$(this).data('name')}</b>?`,
                input: 'checkbox',
                inputValue: 1,
                inputPlaceholder: 'Move to trash.',
                confirmButtonText: "Delete",
                cancelButtonText: "Cancel",
            }).then(result => {
                if (result.isConfirmed) {
                    if (result.value == 1) {
                        $.ajax({
                            url: `/admin/partner/delete/${id}`,
                            method: 'GET',
                            success: function(data) {
                                row.remove();
                                Toast({
                                    title: 'Success Message',
                                    message: 'Delete Successfully',
                                    status: 'success',
                                    duration: 5000,
                                });
                            }
                        });
                    } else {
                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function(data) {
                                row.remove();
                                Toast({
                                    title: 'Success Message',
                                    message: 'Delete Successfully',
                                    status: 'success',
                                    duration: 5000,
                                });
                            }
                        });
                    }
                }
            });
        });
    </script>
@stop
