<template x-data="{}" x-if="$store?.page?.active">
    <div class="dialog">
        <div class="dialog-container">
            <template x-if="$store.page.active == 'all_files'">
                @include('admin::file-manager.all_files')
            </template>
            <template x-if="$store.page.active == 'trash_bin'">
                @include('admin::file-manager.trash_bin')
            </template>
            <template x-if="$store.page.active == 'settings'">
                @include('admin::file-manager.settings')
            </template>
        </div>
        <script>
            Alpine.store('animate').enter(".dialog-container", (res) => {
                res?.animatables[0]?.target?.removeAttribute('style');
            });
        </script>
    </div>
</template>
<script>
    moment.locale('{{ App::currentLocale() }}');
    Alpine.store('page', {
        active: false,
        options: {
            multiple: false,
            afterClose: () => {}
        }
    });

    window.fileManager = (options) => {
        Alpine.store('page', {
            active: 'all_files', //all_files
            options: options
        });
    };
</script>
