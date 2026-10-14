@if(!defined('ADMIN_FILE_MANAGER_POPUP_LOADED'))
@php define('ADMIN_FILE_MANAGER_POPUP_LOADED', true); @endphp
<div id="file-manager-popup-container" data-file-manager-popup="true">
    <template x-data="{}" x-if="$store?.page?.active && !$store.page.full_page">
        <div class="dialog" x-data="fileManagerPopup" style="z-index: 99999; opacity: 0;"
            @file-manager-close.stop="closePopup($event.detail)">
            <div class="dialog-container" x-ref="panel">
                @include('admin::file-manager.content')
            </div>
        </div>
    </template>
</div>
@include('admin::file-manager.scripts')
@endif

