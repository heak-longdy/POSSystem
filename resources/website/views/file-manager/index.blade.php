@extends('admin::index')
@section('index')
    <template x-data="{}" x-if="$store.page.active == 'all_files'">
        @include('admin::file-manager.all_files')
    </template>
    <template x-data="{}" x-if="$store.page.active == 'trash_bin'">
        @include('admin::file-manager.trash_bin')
    </template>
    <template x-data="{}" x-if="$store.page.active == 'settings'">
        @include('admin::file-manager.settings')
    </template>
    <script>
        Alpine.store('page', {
            active: 'all_files',
            full_page: true,
            options: {
                multiple: {{ request('multiple') ?? 'false' }},
                returnUrl: `{{ request('returnUrl') ?? null }}`,
                afterClose: (data, base_url) => {
                    const returnUrl = Alpine.store('page').options.returnUrl;
                    if (returnUrl) {
                        const files = JSON.stringify({
                            data: data,
                            base_url: base_url
                        });
                        window.location.href = returnUrl + '?data=' + files;
                    }
                },
            },
        });
        Alpine.store('animate', {
            enter: (target, fn) => {
                if (!target) return;
                anime({
                    targets: target, //this.$root.children[0]
                    scale: [0.9, 1],
                    opacity: [0, 1],
                    direction: 'forwards',
                    easing: 'easeInSine',
                    duration: 200,
                    complete: (res) => {
                        fn ? fn(res) : null;
                    },
                });
            },
            leave: (target, fn) => {
                if (!target) return;
                anime({
                    targets: target,
                    scale: [1, 0.9],
                    opacity: [1, 0],
                    direction: 'forwards',
                    easing: 'easeOutSine',
                    duration: 200,
                    complete: (res) => {
                        fn ? fn(res) : null;
                    },
                });
            }
        })  ;
    </script>
@stop
