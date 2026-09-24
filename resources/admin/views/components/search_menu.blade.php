@php
    $rawMenu = config('menu', []);
    $allAdminMenuItems = [];

    // 1. Dashboard
    $allAdminMenuItems[] = [
        'id' => 'menu-dashboard',
        'title' => \App\Support\Language::translatedValue(['en' => 'Dashboard', 'km' => 'ផ្ទាំងគ្រប់គ្រង']),
        'title_en' => 'Dashboard',
        'title_km' => 'ផ្ទាំងគ្រប់គ្រង',
        'category' => \App\Support\Language::translatedValue(['en' => 'General', 'km' => 'ទូទៅ']),
        'category_en' => 'General',
        'category_km' => 'ទូទៅ',
        'icon' => 'bx-tachometer',
        'path' => 'admin/dashboard',
        'url' => route('admin-dashboard'),
        'keywords' => 'dashboard overview home ផ្ទាំងគ្រប់គ្រង',
    ];

    // 2. Normalize and flatten all menu items from config('menu')
    foreach ($rawMenu as $mIndex => $item) {
        $type = $item['type'] ?? 'single';

        if ($type === 'dropdown-multiple') {
            $groupLabelEn = is_array($item['label'] ?? null) ? ($item['label']['en'] ?? '') : ($item['label'] ?? '');
            $groupLabelKm = is_array($item['label'] ?? null) ? ($item['label']['km'] ?? $groupLabelEn) : ($item['label'] ?? '');
            $groupLabel = \App\Support\Language::translatedValue($item['label'] ?? []);

            $subList = $item['listMenu'] ?? $item['list-menu'] ?? [];
            foreach ($subList as $sIndex => $subItem) {
                if (!empty($subItem['children'])) {
                    $parentName = \App\Support\Language::translatedValue($subItem['name'] ?? []);
                    $parentNameEn = is_array($subItem['name'] ?? null) ? ($subItem['name']['en'] ?? '') : ($subItem['name'] ?? '');
                    $parentNameKm = is_array($subItem['name'] ?? null) ? ($subItem['name']['km'] ?? '') : ($subItem['name'] ?? '');

                    foreach ($subItem['children'] as $cIndex => $child) {
                        $cName = \App\Support\Language::translatedValue($child['name'] ?? []);
                        $cNameEn = is_array($child['name'] ?? null) ? ($child['name']['en'] ?? '') : ($child['name'] ?? '');
                        $cNameKm = is_array($child['name'] ?? null) ? ($child['name']['km'] ?? '') : ($child['name'] ?? '');

                        $allAdminMenuItems[] = [
                            'id' => "menu-{$mIndex}-{$sIndex}-{$cIndex}",
                            'title' => $cName,
                            'title_en' => $cNameEn,
                            'title_km' => $cNameKm,
                            'category' => "{$groupLabel} › {$parentName}",
                            'category_en' => "{$groupLabelEn} › {$parentNameEn}",
                            'category_km' => "{$groupLabelKm} › {$parentNameKm}",
                            'icon' => !empty($child['icon']) ? $child['icon'] : (!empty($subItem['icon']) ? $subItem['icon'] : 'bx-wrench'),
                            'path' => $child['path'] ?? '#',
                            'url' => isset($child['path']) && $child['path'] !== '#' ? url($child['path']) : '#',
                            'keywords' => "{$cNameEn} {$cNameKm} {$parentNameEn} {$parentNameKm} {$groupLabelEn} {$groupLabelKm}",
                        ];
                    }
                } else {
                    $subName = \App\Support\Language::translatedValue($subItem['name'] ?? []);
                    $subNameEn = is_array($subItem['name'] ?? null) ? ($subItem['name']['en'] ?? '') : ($subItem['name'] ?? '');
                    $subNameKm = is_array($subItem['name'] ?? null) ? ($subItem['name']['km'] ?? '') : ($subItem['name'] ?? '');

                    $allAdminMenuItems[] = [
                        'id' => "menu-{$mIndex}-{$sIndex}",
                        'title' => $subName,
                        'title_en' => $subNameEn,
                        'title_km' => $subNameKm,
                        'category' => $groupLabel,
                        'category_en' => $groupLabelEn,
                        'category_km' => $groupLabelKm,
                        'icon' => !empty($subItem['icon']) ? $subItem['icon'] : 'bx-circle',
                        'path' => $subItem['path'] ?? '#',
                        'url' => isset($subItem['path']) && $subItem['path'] !== '#' ? url($subItem['path']) : '#',
                        'keywords' => "{$subNameEn} {$subNameKm} {$groupLabelEn} {$groupLabelKm}",
                    ];
                }
            }
        } elseif ($type === 'dropdown-single') {
            $groupLabelEn = is_array($item['label'] ?? null) ? ($item['label']['en'] ?? '') : ($item['label'] ?? '');
            $groupLabelKm = is_array($item['label'] ?? null) ? ($item['label']['km'] ?? $groupLabelEn) : ($item['label'] ?? '');
            $groupLabel = \App\Support\Language::translatedValue($item['label'] ?? []);

            if (!empty($item['children'])) {
                foreach ($item['children'] as $cIndex => $child) {
                    $cName = \App\Support\Language::translatedValue($child['name'] ?? []);
                    $cNameEn = is_array($child['name'] ?? null) ? ($child['name']['en'] ?? '') : ($child['name'] ?? '');
                    $cNameKm = is_array($child['name'] ?? null) ? ($child['name']['km'] ?? '') : ($child['name'] ?? '');

                    $allAdminMenuItems[] = [
                        'id' => "menu-{$mIndex}-{$cIndex}",
                        'title' => $cName,
                        'title_en' => $cNameEn,
                        'title_km' => $cNameKm,
                        'category' => $groupLabel,
                        'category_en' => $groupLabelEn,
                        'category_km' => $groupLabelKm,
                        'icon' => !empty($child['icon']) ? $child['icon'] : 'bx-wrench',
                        'path' => $child['path'] ?? '#',
                        'url' => isset($child['path']) && $child['path'] !== '#' ? url($child['path']) : '#',
                        'keywords' => "{$cNameEn} {$cNameKm} {$groupLabelEn} {$groupLabelKm}",
                    ];
                }
            }
        } else {
            $name = \App\Support\Language::translatedValue($item['name'] ?? []);
            $nameEn = is_array($item['name'] ?? null) ? ($item['name']['en'] ?? '') : ($item['name'] ?? '');
            $nameKm = is_array($item['name'] ?? null) ? ($item['name']['km'] ?? '') : ($item['name'] ?? '');
            $catLabel = \App\Support\Language::translatedValue(['en' => 'General', 'km' => 'ទូទៅ']);
            $catLabelEn = 'General';
            $catLabelKm = 'ទូទៅ';

            $allAdminMenuItems[] = [
                'id' => "menu-{$mIndex}",
                'title' => $name,
                'title_en' => $nameEn,
                'title_km' => $nameKm,
                'category' => $catLabel,
                'category_en' => $catLabelEn,
                'category_km' => $catLabelKm,
                'icon' => !empty($item['icon']) ? $item['icon'] : 'bx-circle',
                'path' => $item['path'] ?? '#',
                'url' => isset($item['path']) && $item['path'] !== '#' ? url($item['path']) : '#',
                'keywords' => "{$nameEn} {$nameKm} {$catLabelEn} {$catLabelKm}",
            ];
        }
    }
@endphp

<style>
    .cp-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: min(10vh, 75px);
        padding-left: 16px;
        padding-right: 16px;
        z-index: 999999;
        overflow-y: auto;
        animation: cpOverlayFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes cpOverlayFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .cp-modal {
        width: 100%;
        max-width: 580px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        margin-bottom: 40px;
        animation: cpModalSlideIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        transform-origin: center top;
    }

    @keyframes cpModalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.96) translateY(-8px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .cp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px 10px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
    }

    .cp-title {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        letter-spacing: 0.02em;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .cp-title i {
        font-size: 16px;
        color: #3b82f6;
    }

    .cp-close-btn {
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        padding: 0;
        font-size: 18px;
    }

    .cp-close-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .cp-search-bar {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        position: relative;
        gap: 12px;
    }

    .cp-search-icon {
        font-size: 22px;
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .cp-search-input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 15px;
        color: #1e293b;
        background: transparent;
        padding: 4px 0;
        font-family: inherit;
    }

    .cp-search-input::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    .cp-search-clear {
        cursor: pointer;
        color: #94a3b8;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        border-radius: 50%;
        transition: color 0.15s ease;
    }

    .cp-search-clear:hover {
        color: #475569;
    }

    .cp-esc-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        line-height: 1.3;
        letter-spacing: 0.05em;
    }

    .cp-list {
        max-height: 400px;
        overflow-y: auto;
        padding: 8px 12px;
        scroll-behavior: smooth;
    }

    .cp-list::-webkit-scrollbar {
        width: 6px;
    }

    .cp-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .cp-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .cp-list::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .cp-group {
        margin-bottom: 8px;
    }

    .cp-group-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        padding: 8px 10px 6px;
    }

    .cp-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 12px;
        border-radius: 10px;
        margin-bottom: 3px;
        cursor: pointer;
        transition: all 0.12s ease;
        border: 1px solid transparent;
        user-select: none;
    }

    .cp-item:hover,
    .cp-item.is-selected {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .cp-item-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex: 1;
    }

    .cp-item-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }

    .cp-item:hover .cp-item-icon,
    .cp-item.is-selected .cp-item-icon {
        background: #3b82f6;
        border-color: #3b82f6;
        color: #ffffff;
        transform: scale(1.04);
    }

    .cp-item-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
    }

    .cp-item-title {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cp-item:hover .cp-item-title,
    .cp-item.is-selected .cp-item-title {
        color: #1d4ed8;
    }

    .cp-item-subtitle {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cp-badge-category {
        display: inline-flex;
        align-items: center;
        padding: 1px 6px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 11px;
        font-weight: 500;
    }

    .cp-item-right {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-left: 12px;
        flex-shrink: 0;
    }

    .cp-enter-hint {
        opacity: 0;
        transform: translateX(-4px);
        transition: all 0.15s ease;
        font-size: 12px;
        color: #3b82f6;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .cp-item:hover .cp-enter-hint,
    .cp-item.is-selected .cp-enter-hint {
        opacity: 1;
        transform: translateX(0);
    }

    .cp-empty {
        padding: 40px 20px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .cp-empty-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin-bottom: 12px;
    }

    .cp-empty-title {
        font-size: 15px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
    }

    .cp-empty-desc {
        font-size: 13px;
        color: #64748b;
        max-width: 320px;
    }

    .cp-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        color: #64748b;
    }

    .cp-footer-hints {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .cp-footer-hint {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .cp-footer kbd {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.06);
        padding: 1px 5px;
        font-size: 11px;
        font-weight: 600;
        font-family: inherit;
        color: #475569;
    }

    .cp-footer-count {
        font-size: 11px;
        color: #94a3b8;
    }
</style>

<template x-data="{}" x-if="$store.componentSearchMenu.active">
    <div class="cp-overlay" x-data="xSearchMenu"
        x-bind:style="{ zIndex: ($store.libs?.getLastIndex ? $store.libs.getLastIndex() + 1 : 99999) }"
        @click.self="close()"
        @keydown.window="handleWindowKeydown($event)">
        
        <div class="cp-modal" id="cp-modal-container">
            <!-- Modal Header -->
            <div class="cp-header">
                <h3 class="cp-title">
                    <i class='bx bx-command'></i>
                    <span x-text="options?.title || 'Quick Search Menu'">Quick Search Menu</span>
                </h3>
                <button type="button" class="cp-close-btn" id="cp-close-btn" @click="close()" title="Close (Esc)">
                    <i class='bx bx-x'></i>
                </button>
            </div>

            <!-- Search Bar Input -->
            <div class="cp-search-bar">
                <i class='bx bx-search cp-search-icon'></i>
                <input type="text"
                    x-ref="searchInput"
                    x-model="searchQuery"
                    @input="onSearchInput()"
                    class="cp-search-input"
                    :placeholder="options?.placeholder || 'Type a command or search... (e.g. Stock, Booking, Sales)'"
                    autocomplete="off"
                    spellcheck="false">
                
                <template x-if="searchQuery">
                    <i class='bx bx-x-circle cp-search-clear' @click="searchQuery = ''; onSearchInput(); $refs.searchInput.focus()"></i>
                </template>
                <span class="cp-esc-badge">ESC</span>
            </div>

            <!-- Search Results List -->
            <div class="cp-list" id="cp-list-container">
                <!-- Grouped Items -->
                <template x-if="visibleItems.length > 0">
                    <div>
                        <template x-for="(group, gIdx) in groupedItems" :key="group.name">
                            <div class="cp-group">
                                <div class="cp-group-title" x-text="group.name"></div>
                                <template x-for="item in group.items" :key="item.id || item.path">
                                    <div class="cp-item"
                                        :id="'cp-item-' + item._flatIndex"
                                        :class="{ 'is-selected': selectedIndex === item._flatIndex }"
                                        @mouseenter="selectedIndex = item._flatIndex"
                                        @click="navigateTo(item)">
                                        <div class="cp-item-left">
                                            <div class="cp-item-icon">
                                                <i class='bx' :class="item.icon || 'bx-circle'"></i>
                                            </div>
                                            <div class="cp-item-info">
                                                <div class="cp-item-title" x-text="item.title || item.name?.en || item.name"></div>
                                                <div class="cp-item-subtitle">
                                                    <span class="cp-badge-category" x-text="item.category || 'General'"></span>
                                                    <template x-if="item.title_km && item.title_km !== item.title">
                                                        <span x-text="'• ' + item.title_km" style="color: #94a3b8;"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cp-item-right">
                                            <div class="cp-enter-hint">
                                                <span>Jump to</span>
                                                <i class='bx bx-right-arrow-alt'></i>
                                                <kbd>↵</kbd>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="visibleItems.length === 0">
                    <div class="cp-empty">
                        <div class="cp-empty-icon">
                            <i class='bx bx-search-alt'></i>
                        </div>
                        <div class="cp-empty-title">No matching menu found</div>
                        <div class="cp-empty-desc">
                            No menu or action matches "<span x-text="searchQuery" style="font-weight: 600; color: #1e293b;"></span>".
                            Try searching for general terms like <em>Stock</em>, <em>Booking</em>, <em>Customer</em>, or <em>Report</em>.
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer Status Bar -->
            <div class="cp-footer">
                <div class="cp-footer-hints">
                    <div class="cp-footer-hint">
                        <kbd>↑</kbd><kbd>↓</kbd>
                        <span>Navigate</span>
                    </div>
                    <div class="cp-footer-hint">
                        <kbd>↵</kbd>
                        <span>Open</span>
                    </div>
                    <div class="cp-footer-hint">
                        <kbd>ESC</kbd>
                        <span>Close</span>
                    </div>
                </div>
                <div class="cp-footer-count">
                    <span x-text="visibleItems.length + ' item' + (visibleItems.length === 1 ? '' : 's')"></span>
                </div>
            </div>
        </div>

        <script>
            Alpine.data('xSearchMenu', () => ({
                searchQuery: '',
                selectedIndex: 0,
                options: null,
                rawItems: @json($allAdminMenuItems),

                init() {
                    this.options = Alpine.store('componentSearchMenu').options || {};
                    if (Array.isArray(this.options.data) && this.options.data.length > 0) {
                        this.rawItems = this.normalizeExternalData(this.options.data);
                    }

                    this.selectedIndex = 0;
                    this.searchQuery = '';

                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (this.$refs.searchInput) {
                                this.$refs.searchInput.focus();
                                this.$refs.searchInput.select();
                            }
                        }, 50);
                    });
                },

                normalizeExternalData(data) {
                    if (!Array.isArray(data)) return [];
                    const normalized = [];
                    data.forEach((item, index) => {
                        if (item.type === 'dropdown-multiple' && Array.isArray(item.listMenu)) {
                            const groupLabel = typeof item.label === 'object' ? (item.label.en || item.label.km || 'Group') : (item.label || 'Group');
                            item.listMenu.forEach((sub, subIdx) => {
                                normalized.push({
                                    id: `ext-${index}-${subIdx}`,
                                    title: typeof sub.name === 'object' ? (sub.name.en || sub.name.km || '') : (sub.name || ''),
                                    title_en: typeof sub.name === 'object' ? (sub.name.en || '') : (sub.name || ''),
                                    title_km: typeof sub.name === 'object' ? (sub.name.km || '') : '',
                                    category: groupLabel,
                                    icon: sub.icon || 'bx-circle',
                                    path: sub.path || '#',
                                    url: sub.path && sub.path !== '#' ? (sub.path.startsWith('http') ? sub.path : '/' + sub.path.replace(/^\//, '')) : '#',
                                    keywords: `${sub.name?.en || ''} ${sub.name?.km || ''} ${groupLabel}`
                                });
                            });
                        } else {
                            const title = typeof item.name === 'object' ? (item.name.en || item.name.km || '') : (item.name || item.title || '');
                            normalized.push({
                                id: item.id || `ext-${index}`,
                                title: title,
                                title_en: typeof item.name === 'object' ? (item.name.en || '') : title,
                                title_km: typeof item.name === 'object' ? (item.name.km || '') : '',
                                category: typeof item.label === 'object' ? (item.label.en || 'General') : (item.category || 'General'),
                                icon: item.icon || 'bx-circle',
                                path: item.path || '#',
                                url: item.path && item.path !== '#' ? (item.path.startsWith('http') ? item.path : '/' + item.path.replace(/^\//, '')) : '#',
                                keywords: `${title} ${item.category || ''}`
                            });
                        }
                    });
                    return normalized;
                },

                get visibleItems() {
                    const q = this.searchQuery.toLowerCase().trim();
                    if (!q) {
                        return this.rawItems.map((item, idx) => ({ ...item, _flatIndex: idx }));
                    }

                    return this.rawItems.filter(item => {
                        const title = (item.title || '').toLowerCase();
                        const titleEn = (item.title_en || '').toLowerCase();
                        const titleKm = (item.title_km || '').toLowerCase();
                        const category = (item.category || '').toLowerCase();
                        const path = (item.path || '').toLowerCase();
                        const keywords = (item.keywords || '').toLowerCase();

                        return title.includes(q) ||
                               titleEn.includes(q) ||
                               titleKm.includes(q) ||
                               category.includes(q) ||
                               path.includes(q) ||
                               keywords.includes(q);
                    }).map((item, idx) => ({ ...item, _flatIndex: idx }));
                },

                get groupedItems() {
                    const list = this.visibleItems;
                    const groupMap = {};
                    list.forEach(item => {
                        const cat = item.category || 'General';
                        if (!groupMap[cat]) {
                            groupMap[cat] = {
                                name: cat,
                                items: []
                            };
                        }
                        groupMap[cat].items.push(item);
                    });
                    return Object.values(groupMap);
                },

                onSearchInput() {
                    this.selectedIndex = 0;
                },

                handleWindowKeydown(e) {
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        this.close();
                        return;
                    }

                    const items = this.visibleItems;
                    if (items.length === 0) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.selectedIndex = (this.selectedIndex + 1) % items.length;
                        this.scrollToSelected();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.selectedIndex = (this.selectedIndex - 1 + items.length) % items.length;
                        this.scrollToSelected();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        const selectedItem = items[this.selectedIndex];
                        if (selectedItem) {
                            this.navigateTo(selectedItem);
                        }
                    }
                },

                scrollToSelected() {
                    this.$nextTick(() => {
                        const el = document.getElementById('cp-item-' + this.selectedIndex);
                        if (el) {
                            el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                        }
                    });
                },

                navigateTo(item) {
                    if (!item || !item.url || item.url === '#') return;
                    this.close();
                    window.location.href = item.url;
                },

                close() {
                    Alpine.store('componentSearchMenu').active = false;
                    if (typeof this.options?.afterClose === 'function') {
                        this.options.afterClose();
                    }
                }
            }));
        </script>
    </div>
</template>

<script>
    Alpine.store('componentSearchMenu', {
        active: false,
        options: {
            title: 'Quick Search Menu',
            placeholder: 'Type a command or search...',
            data: null,
            afterClose: () => {}
        }
    });

    window.SearchMenu = (options = {}) => {
        Alpine.store('componentSearchMenu', {
            active: true,
            options: {
                ...Alpine.store('componentSearchMenu').options,
                ...options
            }
        });
    };
</script>
