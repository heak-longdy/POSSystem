@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => __('other_income.category.title')])
    <div class="content-wrapper" id="app" x-data="xIndex">
        @component('admin::components.listingData', [
            'routeName' => $routeName,
            'createName' => __('other_income.category.create'),
            'createPermission' => 'other-income-category-create',
            'filterStatus' => true,
            'data' => $data,
            'status' => $status,
            'tbHeader' => [
                ['field' => 'index', 'title' => __('global.table.no'), 'class' => '', 'colVal' => 5],
                ['field' => 'name', 'title' => __('other_income.category.name'), 'class' => 'text left', 'colVal' => 25],
                ['field' => 'code', 'title' => __('other_income.category.code'), 'class' => 'text left', 'colVal' => 20],
                ['field' => 'description', 'title' => __('other_income.category.description'), 'class' => 'text left', 'colVal' => 30],
                ['field' => 'ordering', 'title' => __('other_income.category.ordering'), 'class' => 'text left', 'colVal' => 10],
                [
                    'field' => 'action',
                    'title' => '',
                    'class' => '',
                    'colVal' => 10,
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
    <script>
        (function() {
            const registerXIndex = () => {
                Alpine.data('xIndex', () => ({
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
