<div class="file-menu-list">
    <div class="file-menu-row">
        <div class="file-menu-item" :class="{active:$store.page.active == 'all_files'}"
            @click="$store.page.active ='all_files'">
            <i data-feather="grid"></i>
            <span>@lang('file-manager.menu.all_files')</span>
        </div>
        <div class="file-menu-item" :class="{active:$store.page.active == 'trash_bin'}"
            @click="$store.page.active ='trash_bin'">
            <i data-feather="trash-2"></i>
            <span>@lang('file-manager.menu.trash_bin')</span>
        </div>
        <div class="file-menu-item" :class="{active:$store.page.active == 'settings'}"
            @click="$store.page.active ='settings'">
            <i data-feather="settings"></i>
            <span>@lang('file-manager.menu.setting')</span>
        </div>
    </div>
</div>
