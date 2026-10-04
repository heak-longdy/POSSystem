<script>
    window.workspaceTranslations = {
        confirmCloseTab: @json(__('global.workspace.confirm_close_tab')),
        confirmCloseAll: @json(__('global.workspace.confirm_close_all')),
        unsaved: @json(__('global.workspace.unsaved')),
        unsavedChanges: @json(__('global.workspace.unsaved_changes')),
        closeTab: @json(__('global.workspace.close_tab')),
        closeTabShortcut: @json(__('global.workspace.close_tab_shortcut')),
        closeAllTabs: @json(__('global.workspace.close_all_tabs')),
        closeAll: @json(__('global.workspace.close_all')),
        openTabs: @json(__('global.workspace.open_tabs')),
        previousTab: @json(__('global.workspace.previous_tab') ?? 'Previous Tab'),
        nextTab: @json(__('global.workspace.next_tab') ?? 'Next Tab'),
        reload: @json(__('global.workspace.reload') ?? 'Reload Tab'),
        closeOthers: @json(__('global.workspace.close_others') ?? 'Close Other Tabs'),
        closeToRight: @json(__('global.workspace.close_to_right') ?? 'Close Tabs to Right'),
        dashboard: @json(__('dashboard.title') ?? __('dashboard.dashboard') ?? 'Dashboard'),
        savedSuccessfully: @json(__('global.message.save_success') ?? __('global.workspace.saved_successfully') ?? 'Saved successfully!'),
        checkRequiredFields: @json(__('global.workspace.check_required_fields') ?? 'Please check the required fields.'),
        submitError: @json(__('global.workspace.submit_error') ?? 'Could not submit form. Please try again.'),
        success: @json(__('global.workspace.success') ?? 'Success'),
        warning: @json(__('global.workspace.warning') ?? 'Warning'),
        error: @json(__('global.workspace.error') ?? 'Error'),
        cancel: @json(__('action_button.cancel') ?? __('global.cancel') ?? 'Cancel'),
        confirm: @json(__('global.confirm') ?? __('dialog.button.confirm') ?? 'Confirm'),
        discardAndClose: @json(__('global.workspace.close_tab') ?? __('dialog.button.close') ?? 'Close Tab'),
    };
</script>

<div id="workspace-tab-bar" class="mdi-workspace-tabs-container" x-data="workspaceMdi()" x-cloak>
    <!-- Scrollable Tab List Wrapper with Left/Right Nav Buttons (YouTube Style) -->
    <div class="mdi-tabs-carousel-wrapper">
        <!-- Previous Button (<) on Left -->
        <div class="mdi-tab-nav-overlay left" 
             x-show="canScrollLeft" 
             x-cloak
             x-transition.opacity.duration.150ms
             style="display: none;">
            <button type="button" 
                    class="mdi-tab-nav-btn" 
                    @click="scrollTabStrip('left')"
                    title="{{ __('global.workspace.previous_tab') ?? 'Previous Tab' }}"
                    aria-label="{{ __('global.workspace.previous_tab') ?? 'Previous Tab' }}">
                <i class='bx bx-chevron-left'></i>
            </button>
        </div>

        <!-- Scrollable Tab List -->
        <div class="mdi-tabs-scroll-wrapper" @scroll.passive="hideTooltip(); checkScrollState()">
        <template x-for="tab in tabs" :key="tab.key">
            <div class="mdi-tab-item" 
                 :data-tab-key="tab.key"
                 :class="{ 'active': activeTabKey === tab.key, 'pinned': tab.isPinned, 'drag-over': dragOverTabKey === tab.key }"
                 @click="switchTab(tab); hideTooltip(); closeContextMenu()"
                 @contextmenu.prevent="openContextMenu($event, tab); hideTooltip()"
                 @mouseenter="showTooltip($el, tab.title + (tab.isDirty ? ' (' + (window.workspaceTranslations?.unsaved || 'unsaved') + ')' : ''))"
                 @mouseleave="hideTooltip()"
                 draggable="true"
                 @dragstart="onTabDragStart($event, tab); hideTooltip(); closeContextMenu()"
                 @dragover="onTabDragOver($event, tab)"
                 @dragleave="onTabDragLeave($event, tab)"
                 @drop="onTabDrop($event, tab)"
                 @dragend="onTabDragEnd($event)">
                
                <!-- Tab Loading Spinner -->
                <div class="mdi-tab-loading-spinner" x-show="tab.isLoading" style="display: none;"></div>

                <!-- Tab Icon -->
                <i class="mdi-tab-icon" x-show="!tab.isLoading" :class="tab.icon || 'bx bx-file'"></i>
                
                <!-- Tab Title -->
                <span class="mdi-tab-title" x-text="tab.title"></span>
                
                <!-- Unsaved Changes Dot -->
                <span class="mdi-tab-dirty-dot" 
                      x-show="tab.isDirty" 
                      @mouseenter.stop="showTooltip($el, window.workspaceTranslations?.unsavedChanges || '{{ __('global.workspace.unsaved_changes') ?? __('global.unsaved_changes') ?? 'Unsaved changes' }}')"
                      @mouseleave.stop="showTooltip($el.closest('.mdi-tab-item'), tab.title + (tab.isDirty ? ' (' + (window.workspaceTranslations?.unsaved || 'unsaved') + ')' : ''))">
                </span>
                
                <!-- Close Button -->
                <button type="button" 
                        class="mdi-tab-close-btn" 
                        x-show="!tab.isPinned" 
                        @click.stop="closeTab(tab, $event); hideTooltip()"
                        @mouseenter.stop="showTooltip($el, window.workspaceTranslations?.closeTabShortcut || '{{ __('global.workspace.close_tab_shortcut') ?? 'Close tab (Ctrl+W)' }}')"
                        @mouseleave.stop="showTooltip($el.closest('.mdi-tab-item'), tab.title + (tab.isDirty ? ' (' + (window.workspaceTranslations?.unsaved || 'unsaved') + ')' : ''))">
                    &times;
                </button>
            </div>
        </template>
    </div>

        <!-- Next Button (>) on Right -->
        <div class="mdi-tab-nav-overlay right" 
             x-show="canScrollRight"
             x-cloak
             x-transition.opacity.duration.150ms
             style="display: none;">
            <button type="button" 
                    class="mdi-tab-nav-btn" 
                    @click="scrollTabStrip('right')"
                    title="{{ __('global.workspace.next_tab') ?? 'Next Tab' }}"
                    aria-label="{{ __('global.workspace.next_tab') ?? 'Next Tab' }}">
                <i class='bx bx-chevron-right'></i>
            </button>
        </div>
    </div>

    <!-- Right Controls -->
    <div class="mdi-tabs-actions">
        <!-- Overflow Dropdown Button (⌄) -->
        <div class="mdi-overflow-action-wrapper" @click.outside="showOverflowDropdown = false">
            <button type="button" 
                    class="mdi-action-btn" 
                    :class="{ 'active': showOverflowDropdown }"
                    @click="toggleOverflowDropdown()" 
                    data-tooltip="{{ __('global.workspace.open_tabs') ?? 'Open Tabs' }}"
                    aria-label="{{ __('global.workspace.open_tabs') ?? 'Open Tabs' }}">
                <i class='bx bx-chevron-down'></i>
            </button>

            <!-- Overflow Dropdown Menu -->
            <div class="mdi-overflow-dropdown" 
                 x-show="showOverflowDropdown" 
                 x-transition
                 style="display: none;">
                
                <div style="padding: 4px 8px 8px; display: flex; align-items: center; justify-content: space-between; font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #f1f5f9; margin-bottom: 4px;">
                    <span>{{ __('global.workspace.open_tabs') ?? 'Open Tabs' }} (<span x-text="tabs.length"></span>)</span>
                    <button type="button" 
                            x-show="tabs.filter(t => !t.isPinned).length > 0"
                            @click="closeAllTabs(); showOverflowDropdown = false;" 
                            style="background: none; border: none; color: #ef4444; font-size: 11px; font-weight: 600; cursor: pointer; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;"
                            title="{{ __('global.workspace.close_all_tabs') ?? 'Close All Tabs' }}">
                        {{ __('global.workspace.close_all') ?? 'Close All' }}
                    </button>
                </div>

                <template x-for="tab in tabs" :key="'dropdown-' + tab.key">
                    <div class="mdi-dropdown-item" 
                         :class="{ 'active': activeTabKey === tab.key }"
                         @click="switchTab(tab); showOverflowDropdown = false;">
                        
                        <div class="item-left">
                            <i :class="tab.icon || 'bx bx-file'" style="font-size: 15px;"></i>
                            <span x-text="tab.title"></span>
                        </div>

                        <div class="item-actions">
                            <span class="mdi-tab-dirty-dot" x-show="tab.isDirty" style="margin: 0;"></span>
                            <button type="button" 
                                    class="mdi-tab-close-btn" 
                                    x-show="!tab.isPinned" 
                                    @click.stop="closeTab(tab, $event)"
                                    title="{{ __('global.workspace.close_tab') ?? 'Close tab' }}">
                                &times;
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Close All Tabs Button -->
        <button type="button" 
                class="mdi-action-btn close-all-btn" 
                @click="closeAllTabs()" 
                :disabled="tabs.filter(t => !t.isPinned).length === 0"
                :style="tabs.filter(t => !t.isPinned).length === 0 ? 'opacity: 0.35; cursor: not-allowed;' : ''"
                data-tooltip="{{ __('global.workspace.close_all_tabs') ?? 'Close All Tabs' }}"
                aria-label="{{ __('global.workspace.close_all_tabs') ?? 'Close All Tabs' }}">
            <i class="bx bx-x"></i>
        </button>

        <span class="mdi-actions-divider"></span>

        <!-- Search Menu (Icon only) -->
        <button type="button" 
                class="mdi-action-btn search-btn" 
                @click="openQuickSearch()" 
                data-tooltip="{{ __('global.header.quick_search') ?? 'Quick Search' }} (⌘K)"
                aria-label="{{ __('global.header.quick_search') ?? 'Quick Search' }} (⌘K)">
            <i class='bx bx-search'></i>
        </button>

        <!-- Notification Bell & Dropdown -->
        <div class="notificationGp" style="position: relative;" @click.outside="showNotificationDropdown = false">
            <a href="#" 
               class="notification" 
               :class="{ 'active': showNotificationDropdown }"
               @click.prevent.stop="toggleNotificationDropdown()" 
               data-tooltip="{{ __('global.workspace.notifications') ?? 'Notifications' }}"
               aria-label="{{ __('global.workspace.notifications') ?? 'Notifications' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                </svg>
            </a>
            <ul class="notification-body" :class="{ 'show': showNotificationDropdown }">
                <div class="notification-card" style="width: 100%;">
                    <div class="notification-header">
                        <h2>{{ __('global.workspace.notifications') ?? 'Notifications' }}</h2>
                        <div class="badge">0 {{ __('global.workspace.new') ?? 'new' }}</div>
                    </div>
                    <ul class="notification-list">
                        <div style="padding: 35px;">
                            <img src="{{ asset('images/logo/em.svg') }}"
                                style="width: 140px;height: 140px;margin-right: 0;border-radius: 0;" />
                            <div class="message" style="display: flex;flex-direction: column;">
                                <span class="title"
                                    style="color: #333;font-size: 18px;font-weight: 700;">{{ __('global.workspace.coming_soon') ?? 'Coming Soon...' }}</span>
                                <span class="des" style="font-size: 14px;color: #989898;">{{ __('global.workspace.coming_soon_desc') ?? 'This feature is coming soon.' }}</span>
                            </div>
                        </div>
                    </ul>
                    <div class="see-all-btn">
                        <button type="button" style="grid-gap: 10px;"><i class='bx bx-right-arrow-alt'
                                style="font-size: 23px;"></i><span>{{ __('global.workspace.see_all_notifications') ?? 'See all Notifications' }}</span></button>
                    </div>
                </div>
            </ul>
        </div>

        <span class="mdi-actions-divider"></span>

        <!-- Profile Avatar Button & Dropdown -->
        <div class="profile mdi-profile-action-wrapper" @click.outside="showProfileDropdown = false">
            <div class="profile-avatar-btn" 
                 :class="{ 'active': showProfileDropdown }"
                 @click="toggleProfileDropdown()" 
                 data-tooltip="{{ Auth::user()?->name ?? 'User Profile' }}"
                 aria-label="{{ Auth::user()?->name ?? 'User Profile' }}">
                <img src="{{ Auth::user()?->image_url ?? asset('admin-public/logo/profile.png') }}"
                     alt="{{ Auth::user()?->name ?? 'Avatar' }}"
                     style="pointer-events: none;"
                     onerror="this.onerror=null;this.src='{{ asset('admin-public/logo/profile.png') }}';" />
            </div>

            <!-- Profile Dropdown Menu (Exact original style) -->
            <ul class="profile-link" :class="{ 'show': showProfileDropdown }">
                <div class="profile-card">
                    <div class="user-info">
                        <img class="avatar"
                            src="{{ Auth::user()?->image_url ?? asset('admin-public/logo/profile.png') }}"
                            alt="Profile Picture"
                            onerror="this.onerror=null;this.src='{{ asset('admin-public/logo/profile.png') }}';">
                        <div class="user-details">
                            <h2>{{ Auth::user()?->name }}</h2>
                            <p class="title">{{ __('global.workspace.administrator') ?? 'Administrator' }}</p>
                            <p class="email"><i class="email-icon"></i>{{ Auth::user()?->email }}</p>
                        </div>
                    </div>
                    <hr>
                    <ul class="profile-menu">
                        <li @click="openUserProfile({{ Auth::user()?->id }})">
                            <i class='bx bx-user'></i>
                            <div>
                                <p>{{ __('global.workspace.my_profile') ?? 'My Profile' }}</p>
                                <span>{{ __('global.workspace.account_settings') ?? 'Account Settings' }}</span>
                            </div>
                        </li>
                        <li>
                            <i class="material-symbols-outlined"> alternate_email</i>
                            <div>
                                <p>{{ __('global.workspace.my_inbox') ?? 'My Inbox' }}</p>
                                <span>{{ __('global.workspace.messages_emails') ?? 'Messages & Emails' }}</span>
                            </div>
                        </li>
                        <li>
                            <i class='bx bx-task'></i>
                            <div>
                                <p>{{ __('global.workspace.my_tasks') ?? 'My Tasks' }}</p>
                                <span>{{ __('global.workspace.todo_daily_tasks') ?? 'To-do and Daily Tasks' }}</span>
                            </div>
                        </li>
                    </ul>
                    <button class="logout-btn" @click="signOut()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                        </svg>
                        <span>{{ __('global.workspace.logout') ?? 'Logout' }}</span>
                    </button>
                </div>
            </ul>
        </div>
    </div>

    <!-- Tab Context Menu (Right Click) -->
    <div class="mdi-context-menu" 
         x-show="contextMenu.show" 
         x-cloak
         @click.outside="closeContextMenu()"
         :style="'top: ' + contextMenu.y + 'px; left: ' + contextMenu.x + 'px;'"
         style="display: none;">
        <button type="button" class="mdi-context-item" @click="refreshTab(contextMenu.tab?.key); closeContextMenu()">
            <i class='bx bx-refresh'></i>
            <span>{{ __('global.workspace.reload') ?? 'Reload Tab' }}</span>
        </button>
        <div class="mdi-context-divider" x-show="!contextMenu.tab?.isPinned"></div>
        <button type="button" class="mdi-context-item" x-show="!contextMenu.tab?.isPinned" @click="closeTab(contextMenu.tab, $event); closeContextMenu()">
            <i class='bx bx-x'></i>
            <span>{{ __('global.workspace.close_tab') ?? 'Close Tab' }}</span>
            <kbd>Ctrl+W</kbd>
        </button>
        <button type="button" class="mdi-context-item" x-show="tabs.filter(t => !t.isPinned && t.key !== contextMenu.tab?.key).length > 0" @click="closeOtherTabs(contextMenu.tab); closeContextMenu()">
            <i class='bx bx-arrow-to-right'></i>
            <span>{{ __('global.workspace.close_others') ?? 'Close Other Tabs' }}</span>
        </button>
        <button type="button" class="mdi-context-item" x-show="canCloseTabsToRight(contextMenu.tab)" @click="closeTabsToRight(contextMenu.tab); closeContextMenu()">
            <i class='bx bx-arrow-from-left'></i>
            <span>{{ __('global.workspace.close_to_right') ?? 'Close Tabs to Right' }}</span>
        </button>
        <div class="mdi-context-divider" x-show="tabs.filter(t => !t.isPinned).length > 0"></div>
        <button type="button" class="mdi-context-item danger" x-show="tabs.filter(t => !t.isPinned).length > 0" @click="closeAllTabs(); closeContextMenu()">
            <i class='bx bx-trash'></i>
            <span>{{ __('global.workspace.close_all_tabs') ?? 'Close All Tabs' }}</span>
        </button>
    </div>

    <!-- Global Floating Tooltip for Tabs -->
    <div class="mdi-tooltip-popup"
         x-show="tooltip.show"
         x-cloak
         x-transition.opacity.duration.150ms
         :style="'top: ' + tooltip.top + 'px; left: ' + tooltip.left + 'px;'"
         x-text="tooltip.text"
         style="display: none;">
    </div>
</div>
