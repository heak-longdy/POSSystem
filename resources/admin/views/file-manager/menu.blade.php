<div class="file-menu-list">
    <div class="file-menu-row">
        <div class="file-menu-item" :class="{active:fileManagerState.active == 'all_files'}"
            @click="fileManagerState.active ='all_files'">
            <i data-feather="grid"></i>
            <span>@lang('file-manager.menu.all_files')</span>
        </div>
        <div class="file-menu-item" :class="{active:fileManagerState.active == 'trash_bin'}"
            @click="fileManagerState.active ='trash_bin'">
            <i data-feather="trash-2"></i>
            <span>@lang('file-manager.menu.trash_bin')</span>
        </div>
        <div class="file-menu-item" :class="{active:fileManagerState.active == 'settings'}"
            @click="fileManagerState.active ='settings'">
            <i data-feather="settings"></i>
            <span>@lang('file-manager.menu.setting')</span>
        </div>
    </div>
</div>
