<template x-if="fileManagerState.active == 'all_files'">
    @include('admin::file-manager.all_files')
</template>
<template x-if="fileManagerState.active == 'trash_bin'">
    @include('admin::file-manager.trash_bin')
</template>
<template x-if="fileManagerState.active == 'settings'">
    @include('admin::file-manager.settings')
</template>
