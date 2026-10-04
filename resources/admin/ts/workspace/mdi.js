/**
 * Enterprise Multiple Document Interface (MDI) Workspace Manager
 * Powering persistent tabs, zero-reload tab switching, dirty state tracking, and keyboard navigation.
 */

import { initSEvents, initSMask } from "./s-events";

// Global Alpine lifecycle hook:
// If a component registers via document.addEventListener('alpine:init', cb) or window.addEventListener('alpine:init', cb),
// and Alpine has already initialized/started, invoke cb() synchronously!
const originalDocumentAddEventListener = document.addEventListener.bind(document);
document.addEventListener = function (type, listener, options) {
    if (type === 'alpine:init') {
        if (window.AlpineStarted || (window.Alpine && (window.Alpine.initialized || window.Alpine.version))) {
            try {
                listener();
            } catch (err) {
                console.error('Error executing alpine:init listener:', err);
            }
            return;
        }
    }
    return originalDocumentAddEventListener(type, listener, options);
};

const originalWindowAddEventListener = window.addEventListener.bind(window);
window.addEventListener = function (type, listener, options) {
    if (type === 'alpine:init') {
        if (window.AlpineStarted || (window.Alpine && (window.Alpine.initialized || window.Alpine.version))) {
            try {
                listener();
            } catch (err) {
                console.error('Error executing alpine:init listener on window:', err);
            }
            return;
        }
    }
    return originalWindowAddEventListener(type, listener, options);
};

window.workspaceMdi = function () {
    return {
        tabs: [],
        activeTabKey: '',
        showOverflowDropdown: false,
        showProfileDropdown: false,
        showNotificationDropdown: false,
        canScrollLeft: false,
        canScrollRight: false,
        draggedTabKey: null,
        dragOverTabKey: null,
        contextMenu: {
            show: false,
            x: 0,
            y: 0,
            tab: null
        },
        tooltip: {
            show: false,
            text: '',
            top: 0,
            left: 0,
            timer: null
        },

        showTooltip(el, text) {
            if (!text || !el) return;
            clearTimeout(this.tooltip.timer);
            const rect = el.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            this.tooltip.text = text;
            this.tooltip.top = rect.bottom + 6;
            this.tooltip.left = Math.max(50, Math.min(window.innerWidth - 50, centerX));
            this.tooltip.show = true;
        },

        hideTooltip() {
            this.tooltip.show = false;
            clearTimeout(this.tooltip.timer);
        },

        openContextMenu(e, tab) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.hideTooltip();
            this.showOverflowDropdown = false;
            this.showProfileDropdown = false;
            this.showNotificationDropdown = false;
            this.contextMenu.tab = tab;
            this.contextMenu.x = Math.max(10, Math.min(window.innerWidth - 210, e.clientX));
            this.contextMenu.y = Math.min(window.innerHeight - 200, e.clientY + 5);
            this.contextMenu.show = true;
        },

        closeContextMenu() {
            this.contextMenu.show = false;
            this.contextMenu.tab = null;
        },

        confirmAction(message, options = {}) {
            return new Promise((resolve) => {
                const store = (this.$store && this.$store.confirmDialog)
                    || (window.Alpine && typeof window.Alpine.store === 'function' ? window.Alpine.store('confirmDialog') : null);

                if (store && typeof store.open === 'function') {
                    store.open({
                        data: {
                            message: message,
                            btnClose: options.cancelText || (window.workspaceTranslations && window.workspaceTranslations.cancel) || 'Cancel',
                            btnSave: options.confirmText || (window.workspaceTranslations && window.workspaceTranslations.closeTab) || 'Close tab',
                            btnSaveClass: options.btnSaveClass || '',
                            typeAction: 'manual',
                            digPosition: 'posTop',
                            class: 'deleteDialog',
                            width: options.width || '18rem',
                            ...(options.data || {})
                        },
                        afterClosed: (result) => {
                            resolve(!!result);
                        }
                    });
                    return;
                }

                // Fallback to native confirm if confirmDialog store not available
                const plainMessage = (message || '').replace(/<br\s*\/?>/gi, '\n').replace(/<[^>]*>/g, '');
                resolve(window.confirm(plainMessage));
            });
        },

        canCloseTabsToRight(tab) {
            if (!tab) return false;
            const idx = this.tabs.findIndex(t => t.key === tab.key);
            if (idx === -1) return false;
            return this.tabs.slice(idx + 1).some(t => !t.isPinned);
        },

        async closeOtherTabs(targetTab) {
            const keepTab = targetTab || this.contextMenu.tab || this.tabs.find(t => t.key === this.activeTabKey);
            if (!keepTab) return;

            const toClose = this.tabs.filter(t => !t.isPinned && t.key !== keepTab.key);
            if (toClose.length === 0) return;

            const hasDirty = toClose.some(t => t.isDirty && this.isFormTab(t));
            if (hasDirty) {
                const rawMsg = (window.workspaceTranslations && window.workspaceTranslations.confirmCloseAll)
                    || 'You have unsaved changes in some tabs. Are you sure you want to close them?';
                const message = rawMsg.replace('". ', '".<br>').replace('. ', '.<br>').replace('។ ', '។<br>');
                const confirmed = await this.confirmAction(message, {
                    confirmText: (window.workspaceTranslations && window.workspaceTranslations.closeOthers) || (window.workspaceTranslations && window.workspaceTranslations.closeTab) || 'Close other tabs',
                    cancelText: (window.workspaceTranslations && window.workspaceTranslations.cancel) || 'Cancel'
                });
                if (!confirmed) return;
            }

            const currentToClose = this.tabs.filter(t => !t.isPinned && t.key !== keepTab.key);
            currentToClose.forEach(tab => {
                const pane = document.getElementById('tab-pane-' + tab.key);
                if (pane) pane.remove();
            });

            this.tabs = this.tabs.filter(t => t.isPinned || t.key === keepTab.key);
            this.saveSession();
            this.switchTab(keepTab);
        },

        async closeTabsToRight(targetTab) {
            const pivotTab = targetTab || this.contextMenu.tab || this.tabs.find(t => t.key === this.activeTabKey);
            if (!pivotTab) return;

            const idx = this.tabs.findIndex(t => t.key === pivotTab.key);
            if (idx === -1) return;

            const toClose = this.tabs.slice(idx + 1).filter(t => !t.isPinned);
            if (toClose.length === 0) return;

            const hasDirty = toClose.some(t => t.isDirty && this.isFormTab(t));
            if (hasDirty) {
                const rawMsg = (window.workspaceTranslations && window.workspaceTranslations.confirmCloseAll)
                    || 'You have unsaved changes in some tabs. Are you sure you want to close them?';
                const message = rawMsg.replace('". ', '".<br>').replace('. ', '.<br>').replace('។ ', '។<br>');
                const confirmed = await this.confirmAction(message, {
                    confirmText: (window.workspaceTranslations && window.workspaceTranslations.closeToRight) || (window.workspaceTranslations && window.workspaceTranslations.closeTab) || 'Close tabs to right',
                    cancelText: (window.workspaceTranslations && window.workspaceTranslations.cancel) || 'Cancel'
                });
                if (!confirmed) return;
            }

            const currentIdx = this.tabs.findIndex(t => t.key === pivotTab.key);
            if (currentIdx === -1) return;

            const currentToClose = this.tabs.slice(currentIdx + 1).filter(t => !t.isPinned);
            currentToClose.forEach(tab => {
                const pane = document.getElementById('tab-pane-' + tab.key);
                if (pane) pane.remove();
            });

            const keepKeys = new Set(this.tabs.slice(0, currentIdx + 1).map(t => t.key));
            this.tabs = this.tabs.filter(t => keepKeys.has(t.key) || t.isPinned);
            this.saveSession();

            if (!this.tabs.some(t => t.key === this.activeTabKey)) {
                this.switchTab(pivotTab);
            }
        },

        onTabDragStart(e, tab) {
            if (tab.isPinned) {
                e.preventDefault();
                return;
            }
            this.hideTooltip();
            this.closeContextMenu();
            this.draggedTabKey = tab.key;
            e.dataTransfer.effectAllowed = 'move';
            try {
                e.dataTransfer.setData('text/plain', tab.key);
            } catch (err) {}
        },

        onTabDragOver(e, tab) {
            if (!this.draggedTabKey || this.draggedTabKey === tab.key || tab.isPinned) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.dragOverTabKey = tab.key;
        },

        onTabDragLeave(e, tab) {
            if (this.dragOverTabKey === tab.key) {
                this.dragOverTabKey = null;
            }
        },

        onTabDrop(e, targetTab) {
            e.preventDefault();
            if (!this.draggedTabKey || this.draggedTabKey === targetTab.key || targetTab.isPinned) {
                this.draggedTabKey = null;
                this.dragOverTabKey = null;
                return;
            }
            const fromIndex = this.tabs.findIndex(t => t.key === this.draggedTabKey);
            const toIndex = this.tabs.findIndex(t => t.key === targetTab.key);
            if (fromIndex !== -1 && toIndex !== -1) {
                const movedTab = this.tabs.splice(fromIndex, 1)[0];
                this.tabs.splice(toIndex, 0, movedTab);
                this.saveSession();
            }
            this.draggedTabKey = null;
            this.dragOverTabKey = null;
        },

        onTabDragEnd(e) {
            this.draggedTabKey = null;
            this.dragOverTabKey = null;
        },

        init() {
            // 1. Detect current page information
            const currentPath = window.location.pathname;
            const currentFullUrl = this.normalizeUrl(window.location.href);
            
            // Extract page title from header metadata, header, form header, or document title
            let currentTitle = document.querySelector('.header-name-meta')?.getAttribute('data-header-name')?.trim()
                || document.querySelector('.navHeaderRight')?.innerText?.trim() 
                || document.querySelector('.form-header h3')?.innerText?.trim()
                || document.title.replace('ADMIN |', '').replace('ADMIN', '').trim() 
                || 'Workspace';
            
            currentTitle = currentTitle.replace(/^(visibility|edit|delete|remove|add|more-vertical|view|arrow-left)\s+/i, '').trim();
            if (!currentTitle) currentTitle = 'Workspace';

            // Find active sidebar icon if available
            const activeSidebarItem = document.querySelector('#sidebar .side-menu a.active');
            let currentIcon = 'bx bx-file';
            if (activeSidebarItem) {
                const iconEl = activeSidebarItem.querySelector('i.icon');
                if (iconEl) {
                    const iconClasses = Array.from(iconEl.classList).filter(c => c !== 'icon');
                    if (iconClasses.length > 0) {
                        currentIcon = iconClasses.join(' ');
                    }
                }
            } else if (currentPath.includes('dashboard')) {
                currentIcon = 'bx bx-tachometer';
            } else if (currentPath.includes('product')) {
                currentIcon = 'bx bx-package';
            } else if (currentPath.includes('order')) {
                currentIcon = 'bx bx-receipt';
            } else if (currentPath.includes('customer')) {
                currentIcon = 'bx bx-user';
            } else if (currentPath.includes('shop')) {
                currentIcon = 'bx bx-store';
            }

            const currentKey = this.generateKey(currentPath);

            // 2. Associate the server-rendered initial pane with current tab key
            const initialPane = document.getElementById('tab-pane-initial');
            if (initialPane) {
                initialPane.id = 'tab-pane-' + currentKey;
                initialPane.setAttribute('data-tab-key', currentKey);
                initialPane.classList.add('active');
                initialPane.style.display = 'flex';
            }

            // 3. Load stored tabs from sessionStorage
            let savedTabs = [];
            try {
                const stored = sessionStorage.getItem('pos_workspace_tabs');
                if (stored) {
                    savedTabs = JSON.parse(stored);
                }
            } catch (e) {
                savedTabs = [];
            }

            // Always ensure all restored tabs are NOT loading, have normalized URLs, and remove legacy malformed keys
            if (Array.isArray(savedTabs)) {
                savedTabs = savedTabs.filter(t => t.key !== '-admin-dashboard');
                savedTabs.forEach(t => {
                    t.isLoading = false;
                    t.url = this.normalizeUrl(t.url);
                    // Ensure listing tabs are NEVER dirty
                    if (!this.isFormTab(t)) {
                        t.isDirty = false;
                    }
                });
            } else {
                savedTabs = [];
            }

            // If we landed on a listing page (e.g. after saving in a create tab), clean up the create tab
            if (currentKey.endsWith('-list')) {
                const modulePrefix = currentKey.replace(/-list$/, '');
                savedTabs = savedTabs.filter(t => t.key !== modulePrefix + '-create');
            }

            // Ensure Dashboard tab is always pinned
            const dashboardUrl = '/admin/dashboard';
            const dashboardKey = this.generateKey(dashboardUrl);
            const hasDashboard = savedTabs.some(t => t.key === dashboardKey);

            if (!hasDashboard) {
                savedTabs.unshift({
                    id: 'tab-' + Math.random().toString(36).substr(2, 9),
                    key: dashboardKey,
                    title: 'Dashboard',
                    url: dashboardUrl,
                    icon: 'bx bx-tachometer',
                    isPinned: true,
                    isDirty: false,
                    isLoading: false,
                    closable: false
                });
            } else {
                const dash = savedTabs.find(t => t.key === dashboardKey);
                if (dash) {
                    dash.isPinned = true;
                    dash.isLoading = false;
                }
            }

            // Register or update current page tab
            const existingCurrentIndex = savedTabs.findIndex(t => t.key === currentKey);
            if (existingCurrentIndex >= 0) {
                savedTabs[existingCurrentIndex].title = currentKey === dashboardKey ? 'Dashboard' : currentTitle;
                savedTabs[existingCurrentIndex].icon = currentIcon;
                savedTabs[existingCurrentIndex].url = currentFullUrl;
                savedTabs[existingCurrentIndex].isLoading = false;
            } else {
                savedTabs.push({
                    id: 'tab-' + Math.random().toString(36).substr(2, 9),
                    key: currentKey,
                    title: currentTitle,
                    url: currentFullUrl,
                    icon: currentIcon,
                    isPinned: currentKey === dashboardKey,
                    isDirty: false,
                    isLoading: false,
                    closable: currentKey !== dashboardKey
                });
            }

            this.tabs = savedTabs;
            this.activeTabKey = currentKey;
            this.saveSession();

            // 4. Initialize s-event and s-mask on the initial page
            if (typeof initSEvents === 'function') {
                initSEvents(document);
            }
            if (typeof initSMask === 'function') {
                initSMask(document);
            }

            // 5. Register global keyboard shortcuts
            this.setupKeyboardShortcuts();

            // 6. Setup form dirty detection
            this.setupDirtyTracking();

            // 7. Setup horizontal mouse wheel scrolling and scroll state detection on tab bar
            this.$nextTick(() => {
                const scrollWrapper = this.$el.querySelector('.mdi-tabs-scroll-wrapper');
                if (scrollWrapper) {
                    scrollWrapper.addEventListener('wheel', (e) => {
                        if (e.deltaY !== 0) {
                            e.preventDefault();
                            scrollWrapper.scrollLeft += e.deltaY;
                            this.checkScrollState();
                        }
                    }, { passive: false });
                }
                this.checkScrollState();
            });

            window.addEventListener('resize', () => {
                this.checkScrollState();
            });

            // 8. Listen to browser Back/Forward (popstate)
            window.addEventListener('popstate', (e) => {
                const targetPath = window.location.pathname + window.location.search;
                const targetKey = this.generateKey(targetPath);
                const targetTab = this.tabs.find(t => t.key === targetKey);
                if (targetTab) {
                    this.switchTab(targetTab, false);
                } else {
                    this.openUrlInTab(targetPath);
                }
            });

            // 9. Intercept internal links and s-click-link buttons inside viewport
            this.setupLinkInterceptor();

            // 10. Intercept form submissions inside workspace tabs (zero-reload saves)
            this.setupFormInterceptor();

            // Make instance accessible globally
            window.MDI = this;
        },

        normalizeUrl(url) {
            if (!url) return '';
            try {
                const parsed = new URL(url, window.location.origin);
                return parsed.pathname + parsed.search;
            } catch(e) {
                return url;
            }
        },

        generateKey(url) {
            if (!url) return 'home';
            let clean = url.replace(/^https?:\/\/[^\/]+/, '').split('?')[0];
            clean = clean.replace(/^\/+|\/+$/g, '');
            if (!clean || clean === 'admin' || clean === 'admin/dashboard') {
                return 'admin-dashboard';
            }
            // For listing views with status tabs (e.g., admin/product/list/1, admin/product/list/trash),
            // group them under the module list tab (admin-product-list) so switching Active/Disable/Trash
            // updates the current listing tab rather than opening duplicate listing tabs
            const listMatch = clean.match(/^admin\/([a-zA-Z0-9_-]+)\/list\/(.+)$/);
            if (listMatch) {
                return 'admin-' + listMatch[1] + '-list';
            }
            return clean.replace(/[^a-zA-Z0-9]/g, '-') || 'home';
        },

        setTabLoading(key, isLoading) {
            const index = this.tabs.findIndex(t => t.key === key);
            if (index !== -1) {
                this.tabs[index].isLoading = isLoading;
            }
        },

        saveSession() {
            try {
                // Ensure ephemeral isLoading state is never saved as true
                const sanitized = this.tabs.map(t => ({
                    ...t,
                    isLoading: false
                }));
                sessionStorage.setItem('pos_workspace_tabs', JSON.stringify(sanitized));
                sessionStorage.setItem('pos_workspace_active_tab', this.activeTabKey);
            } catch (e) {}
        },

        /**
         * Open a URL in a tab without reloading the page
         */
        openUrlInTab(url, title = '', icon = '') {
            const normalizedUrl = this.normalizeUrl(url);
            const targetKey = this.generateKey(normalizedUrl);
            const existingTab = this.tabs.find(t => t.key === targetKey);

            // Determine icon if not provided
            let defaultIcon = icon || 'bx bx-file';
            const lowerUrl = normalizedUrl.toLowerCase();
            if (!icon) {
                if (lowerUrl.includes('/create')) {
                    defaultIcon = 'bx bx-plus-circle';
                } else if (lowerUrl.includes('/edit')) {
                    defaultIcon = 'bx bx-edit';
                } else if (lowerUrl.includes('/detail') || lowerUrl.includes('/view')) {
                    defaultIcon = 'bx bx-show';
                } else if (lowerUrl.includes('/list')) {
                    defaultIcon = 'bx bx-list-ul';
                }
            }

            // Determine initial title if not provided
            let initialTitle = title;
            if (!initialTitle) {
                const parts = lowerUrl.split('/');
                const modIdx = parts.indexOf('admin') + 1;
                const modName = modIdx > 0 && parts[modIdx] ? parts[modIdx].charAt(0).toUpperCase() + parts[modIdx].slice(1) : '';
                if (lowerUrl.includes('/create')) {
                    initialTitle = modName ? `Create ${modName}` : 'Create';
                } else if (lowerUrl.includes('/edit')) {
                    initialTitle = modName ? `Edit ${modName}` : 'Edit';
                } else if (lowerUrl.includes('/detail') || lowerUrl.includes('/view')) {
                    initialTitle = modName ? `${modName} Details` : 'Details';
                } else if (lowerUrl.includes('/list')) {
                    initialTitle = modName ? `${modName} List` : 'List';
                } else {
                    initialTitle = 'Loading...';
                }
            }

            if (existingTab) {
                if (title && (!existingTab.title || existingTab.title === 'Workspace' || existingTab.title === 'Loading...')) {
                    existingTab.title = title;
                }
                if (defaultIcon) {
                    existingTab.icon = defaultIcon;
                }

                // If URL has changed (e.g., pagination ?page=2 or filters or sub-tab switch), reload pane content
                // BUT NEVER destroy or discard create/edit tabs or tabs with unsaved data!
                const isFormTab = existingTab.key.includes('-create') || existingTab.key.includes('-edit') || existingTab.isDirty;
                if (!isFormTab && existingTab.url !== normalizedUrl) {
                    existingTab.url = normalizedUrl;
                    const existingPane = document.getElementById('tab-pane-' + existingTab.key);
                    if (existingPane) {
                        existingPane.remove();
                    }
                }
                this.switchTab(existingTab);
            } else {
                const newTab = {
                    id: 'tab-' + Math.random().toString(36).substr(2, 9),
                    key: targetKey,
                    title: initialTitle,
                    url: normalizedUrl,
                    icon: defaultIcon,
                    isPinned: false,
                    isDirty: false,
                    isLoading: true,
                    closable: true
                };

                this.tabs.push(newTab);
                this.saveSession();
                this.switchTab(newTab);
            }
        },

        /**
         * Switch active tab and toggle pane visibility (0ms DOM swap)
         */
        switchTab(tab, updateHistory = true) {
            this.hideTooltip();
            const existingPaneCheck = document.getElementById('tab-pane-' + tab.key);
            if (tab.key === this.activeTabKey && existingPaneCheck && existingPaneCheck.style.display !== 'none') {
                return;
            }

            // Notify components that tab is about to change (e.g. save drafts)
            try {
                window.dispatchEvent(new CustomEvent('workspace:tab-change', {
                    detail: {
                        previousKey: this.activeTabKey,
                        nextKey: tab.key
                    }
                }));
            } catch (e) {}

            // Hide all tab panes
            const allPanes = document.querySelectorAll('#workspace-viewport .workspace-tab-pane, #workspace-viewport > [id^="tab-pane-"]');
            allPanes.forEach(pane => {
                pane.classList.remove('active');
                pane.style.setProperty('display', 'none', 'important');
            });

            this.activeTabKey = tab.key;
            this.showOverflowDropdown = false;
            this.showProfileDropdown = false;
            this.showNotificationDropdown = false;
            this.saveSession();

            // Update browser URL & title
            if (updateHistory && tab.url) {
                history.pushState({ tabKey: tab.key }, tab.title, tab.url);
            }
            if (tab.title) {
                document.title = 'ADMIN | ' + tab.title;
            }

            // Update sidebar active highlights
            this.updateSidebarActive(tab.url);

            // Check if pane already exists in DOM
            const targetPane = document.getElementById('tab-pane-' + tab.key);
            if (targetPane) {
                targetPane.style.setProperty('display', 'flex', 'important');
                targetPane.classList.add('active');
                this.setTabLoading(tab.key, false);

                // Notify components that pane has become visible again
                try {
                    window.dispatchEvent(new CustomEvent('workspace:tab-activated', {
                        detail: {
                            key: tab.key
                        }
                    }));
                } catch (e) {}

                // Refresh feather icons and trigger resize for responsive sticky columns & charts
                if (window.feather && typeof window.feather.replace === 'function') {
                    try { window.feather.replace(); } catch (e) {}
                }
                window.dispatchEvent(new Event('resize'));
            } else {
                // Fetch and mount tab content
                this.fetchAndMountTab(tab);
            }

            // Scroll tab header into view within tab bar
            this.scrollToTab(tab.key);
        },

        /**
         * Smoothly scroll a tab into view within the tab bar
         */
        scrollToTab(tabKey) {
            const key = tabKey || this.activeTabKey;
            this.$nextTick(() => {
                const scrollWrapper = document.querySelector('#workspace-tab-bar .mdi-tabs-scroll-wrapper')
                    || (this.$el && this.$el.querySelector ? this.$el.querySelector('.mdi-tabs-scroll-wrapper') : null);
                if (!scrollWrapper) return;

                const activeEl = scrollWrapper.querySelector(`.mdi-tab-item[data-tab-key="${key}"]`)
                    || scrollWrapper.querySelector('.mdi-tab-item.active');

                if (activeEl) {
                    const wrapperRect = scrollWrapper.getBoundingClientRect();
                    const elRect = activeEl.getBoundingClientRect();

                    if (elRect.left < wrapperRect.left) {
                        scrollWrapper.scrollTo({
                            left: scrollWrapper.scrollLeft + (elRect.left - wrapperRect.left) - 16,
                            behavior: 'smooth'
                        });
                    } else if (elRect.right > wrapperRect.right) {
                        scrollWrapper.scrollTo({
                            left: scrollWrapper.scrollLeft + (elRect.right - wrapperRect.right) + 16,
                            behavior: 'smooth'
                        });
                    }
                    setTimeout(() => this.checkScrollState(), 350);
                } else {
                    this.checkScrollState();
                }
            });
        },

        /**
         * Update horizontal scroll indicators (canScrollLeft, canScrollRight)
         */
        checkScrollState() {
            this.$nextTick(() => {
                const scrollWrapper = document.querySelector('#workspace-tab-bar .mdi-tabs-scroll-wrapper')
                    || (this.$el && this.$el.querySelector ? this.$el.querySelector('.mdi-tabs-scroll-wrapper') : null);
                if (!scrollWrapper) return;

                const { scrollLeft, scrollWidth, clientWidth } = scrollWrapper;
                this.canScrollLeft = scrollLeft > 2;
                this.canScrollRight = (scrollLeft + clientWidth) < (scrollWidth - 2);
            });
        },

        /**
         * Scroll tab strip horizontally by step (YouTube style)
         */
        scrollTabStrip(direction) {
            const scrollWrapper = document.querySelector('#workspace-tab-bar .mdi-tabs-scroll-wrapper')
                || (this.$el && this.$el.querySelector ? this.$el.querySelector('.mdi-tabs-scroll-wrapper') : null);
            if (!scrollWrapper) return;

            const scrollAmount = Math.max(200, Math.floor(scrollWrapper.clientWidth * 0.5));
            if (direction === 'left') {
                scrollWrapper.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                scrollWrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
            setTimeout(() => this.checkScrollState(), 350);
        },

        /**
         * Check if moving to previous tab is possible
         */
        canGoPrevious() {
            if (!this.tabs || this.tabs.length <= 1) return false;
            const currentIndex = this.tabs.findIndex(t => t.key === this.activeTabKey);
            return currentIndex > 0;
        },

        /**
         * Check if moving to next tab is possible
         */
        canGoNext() {
            if (!this.tabs || this.tabs.length <= 1) return false;
            const currentIndex = this.tabs.findIndex(t => t.key === this.activeTabKey);
            return currentIndex >= 0 && currentIndex < this.tabs.length - 1;
        },

        /**
         * Move to previous tab
         */
        goToPreviousTab() {
            if (!this.canGoPrevious()) return;
            const currentIndex = this.tabs.findIndex(t => t.key === this.activeTabKey);
            if (currentIndex > 0) {
                const targetTab = this.tabs[currentIndex - 1];
                this.switchTab(targetTab);
            }
        },

        /**
         * Move to next tab
         */
        goToNextTab() {
            if (!this.canGoNext()) return;
            const currentIndex = this.tabs.findIndex(t => t.key === this.activeTabKey);
            if (currentIndex >= 0 && currentIndex < this.tabs.length - 1) {
                const targetTab = this.tabs[currentIndex + 1];
                this.switchTab(targetTab);
            }
        },

        /**
         * Reload/refresh active tab pane cleanly
         */
        refreshTab(key) {
            const tabKey = key || this.activeTabKey;
            const tab = this.tabs.find(t => t.key === tabKey);
            if (!tab) return;
            const existingPane = document.getElementById('tab-pane-' + tab.key);
            if (existingPane) {
                existingPane.remove();
            }
            this.setTabLoading(tab.key, true);
            this.fetchAndMountTab(tab);
        },

        /**
         * Dynamically fetch page HTML and mount into #workspace-viewport
         */
        fetchAndMountTab(tab) {
            this.setTabLoading(tab.key, true);

            fetch(tab.url, {
                headers: {
                    'X-MDI-Partial': '1'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP error ' + res.status);
                // Check if redirected to login/auth
                if (res.url && (res.url.includes('/login') || res.url.includes('/auth'))) {
                    window.location.href = res.url;
                    return null;
                }
                return res.text();
            })
            .then(html => {
                if (html === null) return;
                this.setTabLoading(tab.key, false);

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Extract title and clean any icon ligatures (e.g. "visibility View Details", "arrow-left Create Product")
                let docTitle = doc.querySelector('.header-name-meta')?.getAttribute('data-header-name')?.trim()
                    || doc.querySelector('.navHeaderRight')?.innerText?.trim() 
                    || doc.querySelector('.form-header h3')?.innerText?.trim()
                    || doc.title.replace('ADMIN |', '').replace('ADMIN', '').trim();
                if (docTitle) {
                    docTitle = docTitle.replace(/^(visibility|edit|delete|remove|add|more-vertical|view|arrow-left)\s+/i, '').trim();
                    const idx = this.tabs.findIndex(t => t.key === tab.key);
                    if (idx !== -1 && docTitle) {
                        this.tabs[idx].title = docTitle;
                    }
                }

                // Extract external stylesheet and font links (e.g., Google Fonts, ApexCharts)
                doc.querySelectorAll('link[rel="stylesheet"], link[rel="preconnect"]').forEach(linkEl => {
                    const href = linkEl.getAttribute('href');
                    if (href && !document.querySelector(`link[href="${href}"]`)) {
                        document.head.appendChild(linkEl.cloneNode(true));
                    }
                });

                // Extract content: prefer #workspace-viewport .workspace-tab-pane or #content
                let contentHtml = '';
                const initialPane = doc.querySelector('#workspace-viewport .workspace-tab-pane');
                if (initialPane) {
                    contentHtml = initialPane.innerHTML;
                } else {
                    const fetchedContent = doc.querySelector('#content');
                    if (fetchedContent) {
                        fetchedContent.querySelectorAll('.mdi-workspace-tabs-container, #jsScroll, .scroll').forEach(el => el.remove());
                        contentHtml = fetchedContent.innerHTML;
                    }
                }

                if (!contentHtml || !contentHtml.trim()) {
                    throw new Error('Could not parse tab content');
                }

                const viewport = document.getElementById('workspace-viewport');
                if (!viewport) return;

                const isActive = this.activeTabKey === tab.key;

                // Create new tab pane container
                const newPane = document.createElement('div');
                newPane.id = 'tab-pane-' + tab.key;
                newPane.className = 'workspace-tab-pane' + (isActive ? ' active' : '');
                newPane.setAttribute('data-tab-key', tab.key);
                newPane.style.width = '100%';
                newPane.style.flex = '1';
                newPane.style.flexDirection = 'column';
                newPane.style.setProperty('display', isActive ? 'flex' : 'none', 'important');
                newPane.innerHTML = contentHtml;

                // Strip any duplicate global singletons inside the pane
                newPane.querySelectorAll('#file-manager-popup-container, [data-file-manager-popup], [x-data*="confirmDialog"], [x-data*="verifyConfirmDialog"], template[x-if*="confirmDialog"], template[x-if*="componentSearchMenu"], template[x-if*="page?.active"]').forEach(el => {
                    el.closest('.dialog')?.remove() || el.remove();
                });

                // Extract and attach all <style> tags (including page-specific styles in @section('script'))
                doc.querySelectorAll('style').forEach(styleEl => {
                    newPane.appendChild(styleEl.cloneNode(true));
                });

                // If this tab IS active, ensure all other panes are hidden
                if (isActive) {
                    const allPanes = viewport.querySelectorAll('.workspace-tab-pane, [id^="tab-pane-"]');
                    allPanes.forEach(pane => {
                        pane.classList.remove('active');
                        pane.style.setProperty('display', 'none', 'important');
                    });
                }

                // Remove any existing stale pane for this tab key
                const existingSamePane = document.getElementById('tab-pane-' + tab.key);
                if (existingSamePane) {
                    existingSamePane.remove();
                }

                // Strip dormant <script> tags from newPane so inert script elements don't clutter the DOM
                newPane.querySelectorAll('script').forEach(s => s.remove());

                // 1. Extract and execute scripts from fetched document BEFORE mounting newPane to the DOM!
                // This guarantees Alpine components (Alpine.data) and page functions are registered
                // BEFORE Alpine's DOM mutation observer scans and initializes the new elements.
                this.executeScriptsFromDoc(doc, newPane);

                // 2. Mount newPane into the live DOM
                if (window.Alpine && typeof window.Alpine.mutateDom === 'function') {
                    window.Alpine.mutateDom(() => {
                        viewport.appendChild(newPane);
                    });
                } else {
                    viewport.appendChild(newPane);
                }

                // Initialize s-event and s-mask on the newly mounted pane
                if (typeof initSEvents === 'function') {
                    try {
                        initSEvents(newPane);
                    } catch (e) {}
                }
                if (typeof initSMask === 'function') {
                    try {
                        initSMask(newPane);
                    } catch (e) {}
                }

                // Initialize Feather icons & Alpine
                if (window.feather && typeof window.feather.replace === 'function') {
                    try {
                        window.feather.replace();
                    } catch (e) {}
                }
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    try {
                        window.Alpine.initTree(newPane);
                    } catch (e) {
                        console.warn('Alpine.initTree warning:', e);
                    }
                }

                this.saveSession();
            })
            .catch(err => {
                this.setTabLoading(tab.key, false);
                console.error('Error loading tab content:', err);
                const viewport = document.getElementById('workspace-viewport');
                if (viewport) {
                    const isActive = this.activeTabKey === tab.key;
                    if (isActive) {
                        const allPanes = viewport.querySelectorAll('.workspace-tab-pane, [id^="tab-pane-"]');
                        allPanes.forEach(pane => {
                            pane.classList.remove('active');
                            pane.style.setProperty('display', 'none', 'important');
                        });
                    }
                    const errorPane = document.createElement('div');
                    errorPane.id = 'tab-pane-' + tab.key;
                    errorPane.className = 'workspace-tab-pane' + (isActive ? ' active' : '');
                    errorPane.style.width = '100%';
                    errorPane.style.flex = '1';
                    errorPane.style.flexDirection = 'column';
                    errorPane.style.setProperty('display', isActive ? 'flex' : 'none', 'important');
                    errorPane.style.padding = '40px';
                    errorPane.style.textAlign = 'center';
                    errorPane.innerHTML = `
                        <div style="font-size: 40px; margin-bottom: 12px;">⚠️</div>
                        <h4 style="color: #1e293b;">Could not load ${tab.title}</h4>
                        <p style="color: #64748b; font-size: 14px;">An error occurred while fetching the tab content.</p>
                        <button type="button" class="btn btn-primary" onclick="window.MDI.fetchAndMountTab(window.MDI.tabs.find(t => t.key === '${tab.key}'))" style="margin-top: 15px; border-radius: 20px;">Retry</button>
                    `;
                    const existingSameErrorPane = document.getElementById('tab-pane-' + tab.key);
                    if (existingSameErrorPane) {
                        existingSameErrorPane.remove();
                    }
                    viewport.appendChild(errorPane);
                }
            })
            .finally(() => {
                this.setTabLoading(tab.key, false);
            });
        },

        executeScriptsFromDoc(doc, targetPane) {
            if (!doc) return;
            const allScripts = [];

            // 1. Collect scripts from the tab's content container
            const contentContainer = doc.querySelector('#workspace-viewport .workspace-tab-pane') || doc.querySelector('#content');
            if (contentContainer) {
                contentContainer.querySelectorAll('script').forEach(s => allScripts.push(s));
            }

            // 2. Also collect page-specific scripts that were in @section('script') at the end of body or elsewhere
            doc.querySelectorAll('body script').forEach(s => {
                if (allScripts.includes(s)) return;
                const text = s.textContent || '';
                const src = s.getAttribute('src') || '';
                if (src.includes('app.js') || src.includes('body.js') || src.includes('jquery') || src.includes('feather') || src.includes('tinymce') || src.includes('select2')) return;

                // Page-specific Alpine component definitions must NEVER be skipped!
                const isPageComponent = (text.includes('Alpine.data(') || text.includes('Alpine.data("') || text.includes('XDatacreateorder') || text.includes('xIndex') || text.includes('xComponent')) && !text.includes('xHeader') && !text.includes('componentSearchMenu');
                if (!isPageComponent) {
                    // Only filter out the specific global layout/framework scripts from index.blade.php / layout components
                    if (/^\s*window\.workspaceTranslations\s*=/m.test(text)) return;
                    if (text.includes('Global Keyboard Shortcuts')) return;
                    if (text.includes('var notifications =') || text.includes('let notifications =') || text.includes('const notifications =')) return;
                    if (text.includes("Alpine.store('componentSearchMenu'") || text.includes('Alpine.store("componentSearchMenu"')) return;
                    if (text.includes("Alpine.data('xHeader'") || text.includes('Alpine.data("xHeader"')) return;
                    if (text.includes('localStorage.getItem("menu")') && text.includes('sidebar')) return;
                }
                allScripts.push(s);
            });

            // 3. Also check head for page-specific scripts (e.g. pushed scripts)
            doc.querySelectorAll('head script').forEach(s => {
                if (allScripts.includes(s)) return;
                const src = s.getAttribute('src') || '';
                if (src.includes('app.js') || src.includes('body.js') || src.includes('jquery') || src.includes('feather') || src.includes('tinymce') || src.includes('select2') || src.includes('toastr') || src.includes('iziToast') || src.includes('icheck') || src.includes('jqueryUi')) return;
                allScripts.push(s);
            });

            allScripts.forEach(scriptEl => {
                const src = scriptEl.getAttribute('src');
                if (src) {
                    // Skip core bundles already loaded in index.blade.php
                    if (!src.includes('app.js') && !src.includes('body.js') && !src.includes('jquery') && !document.querySelector(`script[src="${src}"]`)) {
                        const extScript = document.createElement('script');
                        Array.from(scriptEl.attributes).forEach(attr => extScript.setAttribute(attr.name, attr.value));
                        document.head.appendChild(extScript);
                    }
                } else if (scriptEl.textContent.trim()) {
                    // Inline script (Alpine components, xIndex, xComponent, etc.)
                    // Execute in document.head so it runs synchronously in global scope BEFORE newPane is attached to DOM
                    try {
                        const inlineScript = document.createElement('script');
                        inlineScript.textContent = scriptEl.textContent;
                        document.head.appendChild(inlineScript);
                        inlineScript.remove();
                    } catch (err) {
                        try {
                            (1, eval)(scriptEl.textContent);
                        } catch (e) {
                            console.warn('Script execution warning:', e);
                        }
                    }
                }
            });

            // Dispatch alpine:init so any lingering listeners fire
            try {
                document.dispatchEvent(new CustomEvent('alpine:init'));
                window.dispatchEvent(new CustomEvent('alpine:init'));
            } catch (e) {}
        },

        async closeTab(tab, event, shouldSwitch = true) {
            this.hideTooltip();
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }

            if (!tab || tab.isPinned) return;

            // Dirty confirmation only if user clicked close button manually (event is provided)
            // and the tab is actually an editable form tab with unsaved changes
            if (tab.isDirty && event && this.isFormTab(tab)) {
                const template = (window.workspaceTranslations && window.workspaceTranslations.confirmCloseTab)
                    || 'You have unsaved changes in ":title". Do you want to close without saving?';
                const title = tab.title || (window.workspaceTranslations && window.workspaceTranslations.tab) || 'Tab';
                const safeTitle = (title + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                const message = template.replace(':title', safeTitle).replace('". ', '".<br>').replace('. ', '.<br>').replace('។ ', '។<br>');
                const confirmed = await this.confirmAction(message, {
                    confirmText: (window.workspaceTranslations && window.workspaceTranslations.closeTab) || 'Close tab',
                    cancelText: (window.workspaceTranslations && window.workspaceTranslations.cancel) || 'Cancel'
                });
                if (!confirmed) return;
            }

            const closingIndex = this.tabs.findIndex(t => t.key === tab.key);
            if (closingIndex < 0) return;

            // Remove DOM pane
            const pane = document.getElementById('tab-pane-' + tab.key);
            if (pane) {
                pane.remove();
            }

            const wasActive = this.activeTabKey === tab.key;
            this.tabs.splice(closingIndex, 1);

            if (wasActive && shouldSwitch) {
                // Switch to adjacent tab or dashboard
                const nextTab = this.tabs[Math.max(0, closingIndex - 1)] || this.tabs[0];
                if (nextTab) {
                    this.switchTab(nextTab);
                }
            } else if (wasActive && !shouldSwitch) {
                this.activeTabKey = null;
                this.saveSession();
            } else {
                this.saveSession();
            }
            this.$nextTick(() => this.checkScrollState());
        },

        async closeAllTabs() {
            const closableTabs = this.tabs.filter(t => !t.isPinned);
            if (closableTabs.length === 0) return;

            const hasDirty = closableTabs.some(t => t.isDirty && this.isFormTab(t));
            if (hasDirty) {
                const rawMsg = (window.workspaceTranslations && window.workspaceTranslations.confirmCloseAll)
                    || 'You have unsaved changes in some tabs. Are you sure you want to close all open tabs?';
                const message = rawMsg.replace('". ', '".<br>').replace('. ', '.<br>').replace('។ ', '។<br>');
                const confirmed = await this.confirmAction(message, {
                    confirmText: (window.workspaceTranslations && window.workspaceTranslations.closeAllTabs) || (window.workspaceTranslations && window.workspaceTranslations.closeTab) || 'Close all tabs',
                    cancelText: (window.workspaceTranslations && window.workspaceTranslations.cancel) || 'Cancel'
                });
                if (!confirmed) return;
            }

            const currentClosable = this.tabs.filter(t => !t.isPinned);
            // Remove DOM panes of closable tabs
            currentClosable.forEach(tab => {
                const pane = document.getElementById('tab-pane-' + tab.key);
                if (pane) {
                    pane.remove();
                }
            });

            // Retain only pinned tabs
            this.tabs = this.tabs.filter(t => t.isPinned);

            // Close dropdowns
            this.showOverflowDropdown = false;
            this.showProfileDropdown = false;
            this.showNotificationDropdown = false;

            // Switch to dashboard or first pinned tab
            if (this.tabs.length > 0) {
                this.switchTab(this.tabs[0]);
            } else {
                const dashboardUrl = '/admin/dashboard';
                const dashboardKey = this.generateKey(dashboardUrl);
                const dashTitle = (window.workspaceTranslations && window.workspaceTranslations.dashboard) || 'Dashboard';
                const dashTab = {
                    id: 'tab-' + Math.random().toString(36).substr(2, 9),
                    key: dashboardKey,
                    title: dashTitle,
                    url: dashboardUrl,
                    icon: 'bx bx-tachometer',
                    isPinned: true,
                    isDirty: false,
                    isLoading: false,
                    closable: false
                };
                this.tabs = [dashTab];
                this.switchTab(dashTab);
            }

            this.saveSession();
        },

        updateSidebarActive(url) {
            if (!url) return;
            try {
                const currentPath = new URL(url, window.location.origin).pathname;
                document.querySelectorAll('#sidebar .side-menu a').forEach(a => {
                    const linkUrl = a.getAttribute('data-url');
                    if (linkUrl) {
                        const linkPath = new URL(linkUrl, window.location.origin).pathname;
                        if (linkPath === currentPath) {
                            a.classList.add('active');
                            const parentDropdown = a.closest('.side-dropdown');
                            if (parentDropdown) {
                                parentDropdown.classList.add('show');
                                const parentA = parentDropdown.parentElement.querySelector('a:first-child');
                                if (parentA) parentA.classList.add('active');
                            }
                        } else {
                            a.classList.remove('active');
                        }
                    }
                });
            } catch(e) {}
        },

        setupLinkInterceptor() {
            document.addEventListener('click', (e) => {
                // 1. Intercept elements with [s-click-link] (buttons, divs, icons, spans)
                const sClickEl = e.target.closest('[s-click-link]');
                if (sClickEl) {
                    if (sClickEl.classList.contains('disabled') || sClickEl.hasAttribute('disabled')) {
                        e.preventDefault();
                        return;
                    }
                    const rawUrl = sClickEl.getAttribute('s-click-link');
                    if (rawUrl && !rawUrl.startsWith('#') && !rawUrl.startsWith('javascript:')) {
                        const lowerUrl = rawUrl.toLowerCase();
                        if (
                            !lowerUrl.includes('/export') && 
                            !lowerUrl.includes('/download') && 
                            !lowerUrl.includes('/print') &&
                            !lowerUrl.endsWith('.xlsx') && 
                            !lowerUrl.endsWith('.pdf') && 
                            !lowerUrl.endsWith('.csv') &&
                            !lowerUrl.includes('delete') &&
                            !lowerUrl.includes('destroy') &&
                            !sClickEl.classList.contains('delete') &&
                            !sClickEl.classList.contains('text-danger') &&
                            !sClickEl.classList.contains('sign-out-btn') &&
                            !sClickEl.hasAttribute('onclick')
                        ) {
                            try {
                                const url = new URL(rawUrl, window.location.origin);
                                if (url.origin === window.location.origin && url.pathname.startsWith('/admin') && !url.pathname.includes('logout') && !url.pathname.includes('auth')) {
                                    e.preventDefault();
                                    e.stopPropagation();

                                    const targetPath = url.pathname + url.search;
                                    const currentTab = this.tabs.find(t => t.key === this.activeTabKey);

                                    // Check if reload button for current tab
                                    if (currentTab && (url.href === window.location.href || currentTab.url === targetPath)) {
                                        this.refreshTab(this.activeTabKey);
                                        return;
                                    }

                                    let title = sClickEl.getAttribute('title') || sClickEl.innerText?.trim() || '';
                                    if (!title && (sClickEl.classList.contains('head-icon') || sClickEl.querySelector('[data-feather="arrow-left"]') || sClickEl.getAttribute('data-feather') === 'arrow-left')) {
                                        title = 'Back';
                                    }

                                    let icon = 'bx bx-file';
                                    if (sClickEl.classList.contains('btn-create') || lowerUrl.includes('/create')) {
                                        icon = 'bx bx-plus-circle';
                                    } else if (lowerUrl.includes('/list')) {
                                        icon = 'bx bx-list-ul';
                                    }

                                    this.openUrlInTab(targetPath, title, icon);
                                    return;
                                }
                            } catch(err) {}
                        }
                    }
                }

                // 2. Intercept anchor <a> tags
                const link = e.target.closest('a[href]');
                if (!link) return;

                // Skip disabled links
                if (link.classList.contains('disabled') || link.getAttribute('aria-disabled') === 'true' || link.hasAttribute('disabled')) {
                    e.preventDefault();
                    return;
                }

                // Skip if default already prevented or modified click
                if (e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

                // Skip external targets, download attributes, or empty/hash href
                if (link.target === '_blank' || link.hasAttribute('download')) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || href === '') return;

                // Skip Fancybox image lightboxes
                if (link.hasAttribute('data-fancybox') || link.closest('[data-fancybox]')) return;

                // Skip Bootstrap / MDB dropdown and modal toggles
                if (link.hasAttribute('data-toggle') || link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-mdb-toggle') || link.classList.contains('dropdown-toggle')) return;

                // Skip explicit opt-out attributes
                if (link.hasAttribute('data-no-tab') || link.hasAttribute('data-no-mdi')) return;

                // Skip export, download, print, or destructive actions
                const lowerHref = href.toLowerCase();
                if (
                    lowerHref.includes('/export') || 
                    lowerHref.includes('/download') || 
                    lowerHref.includes('/print') ||
                    lowerHref.endsWith('.xlsx') || 
                    lowerHref.endsWith('.pdf') || 
                    lowerHref.endsWith('.csv') ||
                    lowerHref.includes('delete') ||
                    lowerHref.includes('destroy') ||
                    link.classList.contains('delete') ||
                    link.classList.contains('text-danger') ||
                    link.classList.contains('sign-out-btn') ||
                    link.hasAttribute('onclick')
                ) {
                    return;
                }

                // Route internal admin links through MDI tab manager
                try {
                    const url = new URL(href, window.location.origin);
                    if (url.origin === window.location.origin && url.pathname.startsWith('/admin') && !url.pathname.includes('logout') && !url.pathname.includes('auth')) {
                        e.preventDefault();

                        let title = link.getAttribute('title') || link.innerText?.trim() || '';
                        let icon = 'bx bx-file';
                        if (lowerHref.includes('/create')) {
                            icon = 'bx bx-plus-circle';
                        } else if (lowerHref.includes('/edit')) {
                            icon = 'bx bx-edit';
                        } else if (lowerHref.includes('/detail') || lowerHref.includes('/view')) {
                            icon = 'bx bx-show';
                        }
                        this.openUrlInTab(url.pathname + url.search, title, icon);
                    }
                } catch(err) {}
            }, false); // Bubble phase ensures custom element click handlers fire first
        },

        setupFormInterceptor() {
            document.addEventListener('submit', (e) => {
                // If form submission was already cancelled by client validation, do not intercept
                if (e.defaultPrevented) {
                    return;
                }

                const form = e.target.closest('form');
                if (!form) return;

                // 1. Skip explicit opt-outs or new-tab targets
                if (form.hasAttribute('data-no-mdi') || form.target === '_blank') {
                    return;
                }

                // Native constraint validation check
                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                    return;
                }

                // 2. Handle GET filter/search forms inside active tab
                if (form.method.toUpperCase() === 'GET' || form.classList.contains('filter')) {
                    e.preventDefault();
                    const action = form.getAttribute('action') || window.location.href;
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const targetUrl = action.split('?')[0] + '?' + params.toString();
                    const normalizedTarget = this.normalizeUrl(targetUrl);
                    const currentTab = this.tabs.find(t => t.key === this.activeTabKey);

                    if (currentTab) {
                        currentTab.url = normalizedTarget;
                        const existingPane = document.getElementById('tab-pane-' + currentTab.key);
                        if (existingPane) existingPane.remove();
                        this.setTabLoading(currentTab.key, true);
                        this.fetchAndMountTab(currentTab);
                    } else {
                        this.openUrlInTab(normalizedTarget);
                    }
                    return;
                }

                // 3. For POST/PUT forms: ensure action is an internal admin route
                const action = form.getAttribute('action') || window.location.href;
                try {
                    const url = new URL(action, window.location.origin);
                    if (
                        url.origin !== window.location.origin ||
                        !url.pathname.startsWith('/admin') ||
                        url.pathname.includes('logout') ||
                        url.pathname.includes('auth')
                    ) {
                        return;
                    }
                } catch(err) {
                    return;
                }

                // Intercept standard CRUD form submission to avoid destroying open tabs
                e.preventDefault();

                // Save TinyMCE editor contents if active
                if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
                    window.tinymce.triggerSave();
                }

                const formPane = form.closest('.workspace-tab-pane');
                const currentTabKey = formPane ? formPane.getAttribute('data-tab-key') : this.activeTabKey;
                const currentTab = this.tabs.find(t => t.key === currentTabKey);

                // Capture submitter name/value (e.g. save_opt: 'save_new')
                const submitter = e.submitter;
                const formData = new FormData(form);
                if (submitter && submitter.name) {
                    formData.set(submitter.name, submitter.value);
                }

                if (submitter) {
                    submitter.disabled = true;
                    submitter.dataset.originalText = submitter.innerHTML;
                    submitter.innerHTML = `<span class="mdi-tab-loading-spinner" style="width:13px;height:13px;border-width:2px;display:inline-block;vertical-align:middle;margin-right:6px;"></span> Saving...`;
                }

                this.setTabLoading(currentTabKey, true);

                fetch(action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        'X-MDI-Form': '1'
                    }
                })
                .then(async (response) => {
                    if (submitter) {
                        submitter.disabled = false;
                        if (submitter.dataset.originalText) {
                            submitter.innerHTML = submitter.dataset.originalText;
                        }
                    }
                    this.setTabLoading(currentTabKey, false);

                    const finalUrl = response.url;
                    const responseText = await response.text();

                    // Check if redirected to login/auth
                    if (finalUrl.includes('/login') || finalUrl.includes('/auth')) {
                        window.location.href = finalUrl;
                        return;
                    }

                    // Check if JSON response
                    try {
                        const json = JSON.parse(responseText);
                        if (json.status === 'success' || json.message === 'success' || json.status === 200) {
                            if (window.iziToast) {
                                const title = (window.workspaceTranslations && window.workspaceTranslations.success) || 'Success';
                                const msg = json.message || (window.workspaceTranslations && window.workspaceTranslations.savedSuccessfully) || 'Saved successfully!';
                                window.iziToast.success({ title: title, message: msg });
                            }
                            if (currentTab) {
                                currentTab.isDirty = false;
                                this.closeTab(currentTab, null, false);
                            }
                            if (json.redirect || json.url) {
                                this.openUrlInTab(json.redirect || json.url);
                            }
                            return;
                        }
                    } catch(jsonErr) {}

                    // Parse HTML response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(responseText, 'text/html');

                    // Check if redirected to login page in document
                    if (doc.querySelector('form[action*="login"]')) {
                        window.location.href = finalUrl;
                        return;
                    }

                    // Success indicators:
                    // 1. Flashed session success message in iziToast config
                    const hasSuccessToast = responseText.includes("type: 'success'") && !responseText.includes("type: 'success', title: 'Success', method: 'success', message: null");
                    // 2. Redirected to a listing URL
                    const isRedirectedToList = finalUrl.includes('/list');

                    // Error indicators:
                    // Rendered error elements outside of template tags
                    const renderedFormErrors = doc.querySelectorAll('form .form-row > label.error, form .form-group > label.error, form .invalid-feedback:not(:empty), .alert-danger:not(:empty)');
                    const hasServerValidationErrors = renderedFormErrors.length > 0;
                    const isValidationError = !response.ok || (hasServerValidationErrors && !hasSuccessToast);

                    if (isValidationError) {
                        // Re-render form with error highlights in active pane
                        const currentPane = document.getElementById('tab-pane-' + currentTabKey);
                        if (currentPane) {
                            const newContent = doc.querySelector('#workspace-viewport .workspace-tab-pane') || doc.querySelector('#content');
                            if (newContent) {
                                // Destroy old Alpine tree first to prevent corrupted state
                                if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
                                    try {
                                        window.Alpine.destroyTree(currentPane);
                                    } catch (e) {}
                                }
                                // Execute scripts FIRST so Alpine.data is updated before new DOM is injected
                                this.executeScriptsFromDoc(doc, currentPane);
                                currentPane.innerHTML = newContent.innerHTML;
                                if (window.feather && typeof window.feather.replace === 'function') {
                                    try { window.feather.replace(); } catch (e) {}
                                }
                                if (typeof initSEvents === 'function') {
                                    try { initSEvents(currentPane); } catch (e) {}
                                }
                                if (typeof initSMask === 'function') {
                                    try { initSMask(currentPane); } catch (e) {}
                                }
                                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                                    try {
                                        window.Alpine.initTree(currentPane);
                                    } catch (e) {
                                        console.warn('Alpine.initTree warning on error re-render:', e);
                                    }
                                }
                            }
                        }
                        if (window.iziToast) {
                            const title = (window.workspaceTranslations && window.workspaceTranslations.warning) || 'Warning';
                            const msg = (window.workspaceTranslations && window.workspaceTranslations.checkRequiredFields) || 'Please check the required fields.';
                            window.iziToast.warning({ title: title, message: msg });
                        }
                    } else {
                        // Success!
                        doc.querySelectorAll('script').forEach(sc => {
                            const text = sc.textContent || '';
                            if (text.includes('iziToast') || text.includes('toastr')) {
                                try { Function(text)(); } catch(e) {}
                            }
                        });

                        if (window.iziToast && !document.querySelector('.iziToast-wrapper .iziToast')) {
                            const title = (window.workspaceTranslations && window.workspaceTranslations.success) || 'Success';
                            const msg = (window.workspaceTranslations && window.workspaceTranslations.savedSuccessfully) || 'Saved successfully!';
                            window.iziToast.success({ title: title, message: msg });
                        }

                        // If user chose "Save & New"
                        if (submitter && submitter.value === 'save_new') {
                            this.refreshTab(currentTabKey);
                            return;
                        }

                        // Determine target list URL
                        let targetUrl = finalUrl;
                        const modName = currentTabKey ? currentTabKey.replace(/^admin-/, '').replace(/-create$/, '').replace(/-edit.*$/, '') : '';
                        if (!targetUrl || targetUrl.includes('/save') || targetUrl.includes('/store')) {
                            targetUrl = `/admin/${modName}/list/1`;
                        }

                        const targetPath = this.normalizeUrl(targetUrl);

                        // Broadcast resource saved so open tabs (like Order) can refresh select dropdowns
                        try {
                            window.dispatchEvent(new CustomEvent('workspace:resource-saved', {
                                detail: {
                                    module: modName,
                                    url: targetPath
                                }
                            }));
                        } catch (e) {}

                        // Mark clean and close create/edit tab WITHOUT switching to adjacent tab
                        if (currentTab) {
                            currentTab.isDirty = false;
                            this.closeTab(currentTab, null, false);
                        }

                        // Open or refresh the listing tab
                        const listKey = this.generateKey(targetPath);
                        const existingListTab = this.tabs.find(t => t.key === listKey);
                        if (existingListTab) {
                            existingListTab.url = targetPath;
                            const listPane = document.getElementById('tab-pane-' + existingListTab.key);
                            if (listPane) listPane.remove();
                        }

                        this.openUrlInTab(targetPath);
                    }
                })
                .catch((err) => {
                    if (submitter) {
                        submitter.disabled = false;
                        if (submitter.dataset.originalText) {
                            submitter.innerHTML = submitter.dataset.originalText;
                        }
                    }
                    this.setTabLoading(currentTabKey, false);
                    console.error('Form submission error:', err);
                    if (window.iziToast) {
                        const title = (window.workspaceTranslations && window.workspaceTranslations.error) || 'Error';
                        const msg = (window.workspaceTranslations && window.workspaceTranslations.submitError) || 'Could not submit form. Please try again.';
                        window.iziToast.error({ title: title, message: msg });
                    }
                });
            });
        },

        /**
         * Determine if a tab is an editable form tab (e.g. Create Order, Create Shop, Edit Product).
         * Listing pages, reports, dashboards, logs, and transaction tables are NEVER form tabs.
         */
        isFormTab(tab) {
            if (!tab) return false;
            const key = (tab.key || '').toLowerCase();
            const url = (tab.url || '').toLowerCase();

            // 1. Explicitly check if it is a listing / report / dashboard page
            const isExplicitListing = 
                key.includes('-list') ||
                key.includes('dashboard') ||
                key.includes('report') ||
                key.includes('history') ||
                key.includes('transaction') ||
                key.includes('movement') ||
                key.endsWith('-index') ||
                url.includes('/list') ||
                url.includes('/report') ||
                url.includes('/dashboard') ||
                url.includes('/history') ||
                url.includes('/transaction');

            // If it matches a listing and has NO create/edit indicators, it is definitely a listing page
            if (isExplicitListing && !key.includes('-create') && !key.includes('-edit') && !url.includes('/create') && !url.includes('/edit')) {
                return false;
            }

            // 2. Identify known form patterns
            if (
                key.includes('-create') ||
                key.includes('-edit') ||
                key.includes('-store') ||
                key.includes('createorder') ||
                key.includes('create-order') ||
                key.includes('-password') ||
                key.includes('-top-up') ||
                url.includes('/create') ||
                url.includes('/edit') ||
                url.includes('/store') ||
                url.includes('createorder')
            ) {
                return true;
            }

            // 3. Fallback: inspect DOM pane for an actual entity form (POST/PUT/PATCH form, not a GET filter)
            const pane = document.getElementById('tab-pane-' + tab.key);
            if (pane) {
                if (pane.querySelector('.booking-pos-workspace')) {
                    return true;
                }
                const form = pane.querySelector('form:not(.filter):not(#FilterForm):not([id*="filter"]):not([id*="Filter"]):not([method="GET"]):not([method="get"])');
                if (form && (form.classList.contains('form-wrapper') || form.getAttribute('method')?.toUpperCase() === 'POST')) {
                    return true;
                }
            }

            return false;
        },

        markTabDirty(isDirty = true) {
            const current = this.tabs.find(t => t.key === this.activeTabKey);
            if (current) {
                // Listing pages cannot be dirty
                if (isDirty && !this.isFormTab(current)) {
                    current.isDirty = false;
                    return;
                }
                current.isDirty = isDirty;
                this.saveSession();
            }
        },

        setupDirtyTracking() {
            const contentArea = document.getElementById('content');
            if (contentArea) {
                const handleDirtyInput = (e) => {
                    // Only track real user interactions (ignore programmatic plugin dispatches)
                    if (e.isTrusted === false) return;

                    const target = e.target;
                    if (!target) return;

                    // Automatically clear validation error message when user starts typing
                    const formRow = target.closest('.form-row, .form-group');
                    if (formRow) {
                        const errLabel = formRow.querySelector('label.error, span.error, .invalid-feedback');
                        if (errLabel) {
                            errLabel.remove();
                        }
                    }

                    // 1. Ignore if target is a search/filter input or in a filter form
                    if (
                        target.closest('.InputContainer') ||
                        target.closest('.filter') ||
                        target.closest('#FilterForm') ||
                        target.closest('.filter-form') ||
                        target.closest('[id*="Filter"]') ||
                        target.closest('[id*="filter"]') ||
                        target.closest('.dataTables_filter') ||
                        target.closest('.table-filter') ||
                        target.closest('form[method="GET"]') ||
                        target.closest('form[method="get"]') ||
                        target.type === 'search' ||
                        target.classList.contains('inputSearch') ||
                        target.classList.contains('form-search') ||
                        target.name === 'search' ||
                        target.name === 'filter' ||
                        target.name === 'keyword'
                    ) {
                        return;
                    }

                    // 2. Active tab must be a form tab (listing pages are NEVER dirty)
                    const currentTab = this.tabs.find(t => t.key === this.activeTabKey);
                    if (!currentTab || !this.isFormTab(currentTab)) {
                        return;
                    }

                    // 3. The input must belong to the active tab's pane
                    const pane = target.closest('.workspace-tab-pane');
                    if (pane && pane.id !== 'tab-pane-' + this.activeTabKey) {
                        return;
                    }

                    // 4. Must be inside a data form or booking-pos-workspace
                    const isInDataForm = target.closest('form:not([method="GET"]):not([method="get"]):not(.filter)') || target.closest('.booking-pos-workspace') || target.closest('.form-wrapper');
                    if (!isInDataForm) {
                        return;
                    }

                    this.markTabDirty(true);
                };

                contentArea.addEventListener('input', handleDirtyInput, true);
                contentArea.addEventListener('change', handleDirtyInput, true);

                if (window.jQuery) {
                    $(contentArea).on('select2:select select2:unselect', (e) => {
                        const target = e.target;
                        if (!target) return;
                        if (target.closest('.InputContainer, .filter, #FilterForm, .filter-form')) return;
                        const currentTab = this.tabs.find(t => t.key === this.activeTabKey);
                        if (currentTab && this.isFormTab(currentTab)) {
                            this.markTabDirty(true);
                        }
                    });
                }
            }
        },

        setupKeyboardShortcuts() {
            document.addEventListener('keydown', async (e) => {
                // Ctrl+W or Cmd+W
                if ((e.ctrlKey || e.metaKey) && e.key === 'w' && !e.shiftKey) {
                    const activeTab = this.tabs.find(t => t.key === this.activeTabKey);
                    if (activeTab && !activeTab.isPinned) {
                        e.preventDefault();
                        await this.closeTab(activeTab, e);
                    }
                }

                // Ctrl+Tab to cycle tabs forward
                if (e.ctrlKey && e.key === 'Tab') {
                    e.preventDefault();
                    const currentIndex = this.tabs.findIndex(t => t.key === this.activeTabKey);
                    if (currentIndex >= 0) {
                        const nextIndex = e.shiftKey
                            ? (currentIndex - 1 + this.tabs.length) % this.tabs.length
                            : (currentIndex + 1) % this.tabs.length;
                        this.switchTab(this.tabs[nextIndex]);
                    }
                }

                // Ctrl+PageUp / Ctrl+PageDown to navigate tabs
                if (e.ctrlKey && !e.shiftKey && !e.altKey && (e.key === 'PageUp' || e.key === 'PageDown')) {
                    e.preventDefault();
                    if (e.key === 'PageUp') {
                        this.goToPreviousTab();
                    } else {
                        this.goToNextTab();
                    }
                }

                // Ctrl+1 through Ctrl+9
                if (e.ctrlKey && !e.shiftKey && !e.altKey && e.key >= '1' && e.key <= '9') {
                    const targetIndex = parseInt(e.key) - 1;
                    if (targetIndex < this.tabs.length) {
                        e.preventDefault();
                        this.switchTab(this.tabs[targetIndex]);
                    }
                }
            });
        },

        openNewTabLauncher() {
            this.openQuickSearch();
        },

        openQuickSearch() {
            if (typeof window.SearchMenu === 'function') {
                window.SearchMenu();
            } else if (Alpine.store('componentSearchMenu')) {
                Alpine.store('componentSearchMenu').active = true;
            } else if (typeof window.openQuickSearch === 'function') {
                window.openQuickSearch();
            }
        },

        toggleOverflowDropdown() {
            this.showOverflowDropdown = !this.showOverflowDropdown;
            if (this.showOverflowDropdown) {
                this.showProfileDropdown = false;
                this.showNotificationDropdown = false;
            }
        },

        toggleProfileDropdown() {
            this.showProfileDropdown = !this.showProfileDropdown;
            if (this.showProfileDropdown) {
                this.showOverflowDropdown = false;
                this.showNotificationDropdown = false;
            }
        },

        toggleNotificationDropdown() {
            this.showNotificationDropdown = !this.showNotificationDropdown;
            if (this.showNotificationDropdown) {
                this.showOverflowDropdown = false;
                this.showProfileDropdown = false;
            }
        },

        openUserProfile(userId) {
            this.showProfileDropdown = false;
            if (userId) {
                this.openUrlInTab(`/admin/user/edit/${userId}`, 'My Profile', 'bx bx-user');
            }
        },

        signOut() {
            this.showProfileDropdown = false;
            if (typeof window.logOut === 'function') {
                window.logOut({});
            } else {
                const signOutLink = document.querySelector('a[href*="sign-out"]');
                if (signOutLink) {
                    signOutLink.click();
                } else {
                    window.location.href = '/admin/sign-out';
                }
            }
        }
    };
};
