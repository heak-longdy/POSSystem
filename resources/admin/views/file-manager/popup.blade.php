@if(!defined('ADMIN_FILE_MANAGER_POPUP_LOADED'))
@php define('ADMIN_FILE_MANAGER_POPUP_LOADED', true); @endphp
<div id="file-manager-popup-container" data-file-manager-popup="true">
    <template x-data="{}" x-if="$store?.page?.active">
        <div class="dialog" style="z-index: 99999;">
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
        </div>
    </template>
</div>
@include('admin::file-manager.scripts')
@endif

