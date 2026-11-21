@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xPermission({{ json_encode(old('_permissions_form') ? (is_array(old('permission')) ? old('permission') : []) : $userPermissions) }})" x-cloak>
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-user-save-permission', $user->id) !!}" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="_permissions_form" value="1">
            {{-- Retain any existing assignments that are outside the selectable modules --}}
            @foreach ($preservedPermissions as $permission)
                <input type="hidden" name="permission[]" value="{{ $permission }}">
            @endforeach
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-user-list', 1) !!}"></i>
                    @lang('permission.title') &mdash; {{ $user->name }}
                    @if($user->role)
                        <span class="badge-role" style="font-size: 13px; font-weight: normal; margin-left: 8px; background: #e0f2fe; color: #0369a1; padding: 3px 10px; border-radius: 12px;">
                            {{ __('user.roles.' . $user->role) !== 'user.roles.' . $user->role ? __('user.roles.' . $user->role) : ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                    @endif
                </h3>
            </div>

            <div class="form-body">
                <!-- User Summary Card -->
                <div class="user-permission-card" style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 20px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 46px; height: 46px; border-radius: 50%; overflow: hidden; background: #cbd5e1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            @if($user->image)
                                <img src="{!! asset('file_manager' . $user->image) !!}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i data-feather="user" style="width: 24px; height: 24px; color: #64748b;"></i>
                            @endif
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 16px; color: #0f172a;">{{ $user->name }}</div>
                            <div style="font-size: 13px; color: #64748b;">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
                        <div style="font-size: 13px; color: #64748b;">
                            {{ __('global.table.status') }}: 
                            <span style="font-weight: 600; color: {{ $user->status == 1 ? '#16a34a' : '#dc2626' }};">
                                {{ $user->status == 1 ? __('user.form.status.active') : __('user.form.status.disable') }}
                            </span>
                        </div>
                        <div style="font-size: 13px; color: #334155; background: #ffffff; padding: 6px 14px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: 500;">
                            {{ __('permission.selected') }}: <strong style="color: #2563eb; font-size: 15px;" x-text="selectedCount"></strong> / <span x-text="totalCount"></span>
                        </div>
                    </div>
                </div>

                <!-- Toast Notification Banner -->
                <div x-show="toastMessage" x-transition style="margin-bottom: 16px; padding: 10px 16px; border-radius: 8px; font-size: 13.5px; display: flex; align-items: center; justify-content: space-between;"
                    :style="toastType === 'success' ? 'background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;' : 'background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;'">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i :class="toastType === 'success' ? 'bx bx-check-circle' : 'bx bx-error-circle'" style="font-size: 18px;"></i>
                        <span x-text="toastMessage"></span>
                    </div>
                    <button type="button" @click="toastMessage = ''" style="background: none; border: none; cursor: pointer; color: inherit; padding: 0 4px;">
                        <i class='bx bx-x' style="font-size: 18px;"></i>
                    </button>
                </div>

                @if ($user->role === 'super_admin')
                    <p style="color: #0369a1; background: #f0f9ff; border: 1px solid #bae6fd; padding: 8px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 16px;">
                        <i class='bx bx-info-circle'></i> {{ __('permission.super_admin_access') }}
                    </p>
                @endif
                @if (count($inheritedPermissions))
                    <p style="color: #64748b; font-size: 12.5px; margin-bottom: 14px;">
                        <i class='bx bx-info-circle'></i> {{ __('permission.inherited_help') }}
                    </p>
                @endif

                <!-- Permission Controls & Search Header -->
                <style>
                    .headerListPermission {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: space-between !important;
                        flex-wrap: wrap !important;
                        gap: 14px !important;
                        height: auto !important;
                        min-height: 60px !important;
                        background: #ffffff !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 12px !important;
                        padding: 12px 18px !important;
                        margin-bottom: 18px !important;
                        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05) !important;
                    }
                    .headerListPermission .btn-sync-permission {
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 7px !important;
                        height: 38px !important;
                        padding: 0 14px !important;
                        font-size: 13px !important;
                        font-weight: 600 !important;
                        border-radius: 8px !important;
                        cursor: pointer !important;
                        border: 1px solid #bfdbfe !important;
                        background: #eff6ff !important;
                        color: #1d4ed8 !important;
                        text-decoration: none !important;
                        line-height: normal !important;
                        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.06) !important;
                        transition: all 0.15s ease !important;
                        min-width: auto !important;
                        outline: none !important;
                        white-space: nowrap !important;
                    }
                    .headerListPermission .btn-sync-permission:hover:not(:disabled) {
                        background: #dbeafe !important;
                        border-color: #93c5fd !important;
                        color: #1e40af !important;
                    }
                    .headerListPermission .btn-sync-permission:active:not(:disabled) {
                        transform: translateY(1px) !important;
                    }
                    .headerListPermission .btn-sync-permission:disabled {
                        opacity: 0.65 !important;
                        cursor: not-allowed !important;
                        background: #eff6ff !important;
                        border-color: #bfdbfe !important;
                        color: #1d4ed8 !important;
                    }
                    .headerListPermission .btn-sync-permission .bx {
                        font-size: 17px !important;
                        color: #2563eb !important;
                    }
                    .headerListPermission .btn-sync-permission span {
                        color: #1d4ed8 !important;
                        font-weight: 600 !important;
                        font-size: 13px !important;
                    }
                    .headerListPermission .search-clear-btn {
                        position: absolute;
                        right: 10px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 20px;
                        height: 20px;
                        border-radius: 50%;
                        background: #e2e8f0;
                        cursor: pointer;
                        color: #64748b;
                        transition: background 0.15s ease, color 0.15s ease;
                    }
                    .headerListPermission .search-clear-btn:hover {
                        background: #cbd5e1;
                        color: #334155;
                    }
                </style>
                <div class="row" style="margin-bottom: 15px;">
                    <div class="permissionLayoutGp" style="width: 100%;">
                        <div class="headerListPermission">
                            <!-- Left: Title, Counter Badge & Sync Action -->
                            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; display: flex; align-items: center; justify-content: center; color: #2563eb; flex-shrink: 0; box-shadow: 0 1px 2px rgba(37, 99, 235, 0.08);">
                                        <i class='bx bx-shield-quarter' style="font-size: 20px;"></i>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <label class="titlePer" style="margin: 0 !important; font-weight: 700 !important; font-size: 15px !important; color: #0f172a !important; flex: initial !important; line-height: 1.3 !important;">@lang('permission.title')</label>
                                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11.5px; font-weight: 600; padding: 2px 8px; border-radius: 12px; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; height: 26px;" title="{{ __('permission.selected') }}">
                                            <i class='bx bx-check-shield' style="font-size: 13px; color: #2563eb;"></i>
                                            <span><strong style="color: #2563eb;" x-text="selectedCount"></strong> / <span x-text="totalCount"></span></span>
                                        </span>
                                    </div>
                                </div>

                                <div style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 2px;"></div>

                                <button type="button" class="btn-sync-permission" @click="syncSystemPermissions()" :disabled="syncing"
                                    title="{{ __('permission.sync_catalog') }}">
                                    <i class='bx bx-sync' :class="{ 'bx-spin': syncing }"></i>
                                    <span x-show="!syncing">{{ __('permission.sync_catalog') }}</span>
                                    <span x-show="syncing" style="display: none;">{{ __('permission.syncing') }}</span>
                                </button>
                            </div>

                            <!-- Right: Search Input & Select All Pill -->
                            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px;">
                                <!-- Live Search Filter -->
                                <div style="position: relative; display: flex; align-items: center;">
                                    <i class='bx bx-search' style="position: absolute; left: 11px; font-size: 16px; color: #94a3b8; pointer-events: none;"></i>
                                    <input type="text" x-model="search" placeholder="{{ __('permission.search_modules') }}"
                                        style="padding: 7px 32px 7px 34px; font-size: 12.5px; font-weight: 500; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; width: 230px; background: #f8fafc; color: #0f172a; transition: all 0.18s ease; height: 38px;"
                                        onfocus="this.style.background='#ffffff'; this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.12)';"
                                        onblur="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">
                                    <span role="button" tabindex="0" class="search-clear-btn" x-show="search && search.trim() !== ''" @click="search = ''" title="Clear">
                                        <i class='bx bx-x' style="font-size: 14px;"></i>
                                    </span>
                                </div>

                                <!-- Select All Toggle Pill -->
                                <label class="permissionListCheckall"
                                    style="display: inline-flex; align-items: center; gap: 8px; padding: 0 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; margin: 0; user-select: none; transition: all 0.15s ease; height: 38px;"
                                    onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#94a3b8';"
                                    onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';">
                                    <input type="checkbox" id="chk-permissionSelectAll-{{ $user->id }}" class="chk-permissionSelectAll"
                                        x-effect="$el.checked = isAllSelected(); $el.indeterminate = selectedCount > 0 && !isAllSelected()"
                                        @change="toggleSelectAll($event.target.checked)"
                                        style="width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb; margin: 0;" />
                                    <span class="span" style="font-size: 13px; font-weight: 600; color: #1e293b; letter-spacing: -0.1px;">{{ __('permission.select_all') }}</span>
                                </label>
                            </div>
                        </div>

                        @foreach ($errors->get('permission') + $errors->get('permission.*') as $messages)
                            @foreach ((array) $messages as $message)
                                <div class="error" style="color: red; margin-bottom: 10px; font-size: 13px;">{{ $message }}</div>
                            @endforeach
                        @endforeach

                        <!-- Dynamic Modules Grid -->
                        <div class="permissionLayout" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; width: 100%;">
                            @include('admin::pages.user.permission-menu', [
                                'menu' => $permissionMenu,
                                'ancestorSearch' => '',
                                'depth' => 0,
                            ])
                        </div>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="form-button" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; gap: 12px;">
                    <button type="submit" color="primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 24px; font-size: 14px; font-weight: 600;">
                        <i data-feather="save"></i>
                        <span>@lang('permission.button.update')</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-user-list', 1) !!}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px; font-size: 14px;">
                        <i data-feather="x"></i>
                        <span>@lang('permission.button.cancel')</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop

@section('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('xPermission', (permissions = []) => ({
                search: '',
                selectedPermissions: permissions,
                availablePermissions: [],
                syncing: false,
                toastMessage: '',
                toastType: 'success',

                init() {
                    this.refreshAvailablePermissions();
                },

                refreshAvailablePermissions() {
                    this.$nextTick(() => {
                        this.availablePermissions = Array.from(this.$root.querySelectorAll('input.permissionAllitem')).map(box => box.value);
                        this.selectedPermissions = this.selectedPermissions.filter(name => this.availablePermissions.includes(name));
                        if (typeof feather !== 'undefined') feather.replace();
                    });
                },

                get totalCount() { return this.availablePermissions.length; },
                get selectedCount() { return this.selectedPermissions.length; },

                showToast(msg, type = 'success') {
                    this.toastMessage = msg;
                    this.toastType = type;
                    setTimeout(() => {
                        if (this.toastMessage === msg) {
                            this.toastMessage = '';
                        }
                    }, 5000);
                },

                toggleSelectAll(checked) {
                    this.selectedPermissions = checked ? [...this.availablePermissions] : [];
                },

                isAllSelected() {
                    return this.totalCount > 0 && this.selectedCount === this.totalCount;
                },

                modulePermissions(moduleId) {
                    return Array.from(this.$root.querySelectorAll('.perm-item-' + moduleId)).map(box => box.value);
                },

                toggleModuleAll(moduleId, checked) {
                    const names = this.modulePermissions(moduleId);
                    this.selectedPermissions = checked
                        ? [...new Set([...this.selectedPermissions, ...names])]
                        : this.selectedPermissions.filter(name => !names.includes(name));
                },

                isModuleAllChecked(moduleId) {
                    const names = this.modulePermissions(moduleId);
                    return names.length > 0 && names.every(name => this.selectedPermissions.includes(name));
                },

                isModulePartial(moduleId) {
                    return !this.isModuleAllChecked(moduleId) && this.modulePermissions(moduleId).some(name => this.selectedPermissions.includes(name));
                },

                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        || this.$root.querySelector('input[name="_token"]')?.value
                        || document.querySelector('input[name="_token"]')?.value
                        || '';
                },

                async syncSystemPermissions() {
                    this.syncing = true;
                    try {
                        const token = this.getCsrfToken();
                        const res = await fetch(`{!! route('admin-user-permission-sync') !!}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();
                        if (data && data.success) {
                            this.showToast(data.message || 'System permissions synchronized successfully.');
                            // Reload page to reflect newly added modules/permissions while keeping the active MDI tab
                            setTimeout(() => {
                                window.location.reload();
                            }, 800);
                        } else {
                            this.showToast(data.message || 'Failed to sync permissions catalog.', 'error');
                        }
                    } catch (err) {
                        console.error('Sync failed', err);
                        this.showToast('Failed to sync permissions catalog.', 'error');
                    } finally {
                        this.syncing = false;
                    }
                },

                toggleMenu(el) {
                    const parent = el.closest('.permissionItem');
                    if (parent) {
                        parent.classList.toggle('showMenu');
                        const body = parent.querySelector('.permissionListItemGpCh');
                        if (body) {
                            body.style.display = parent.classList.contains('showMenu') ? 'block' : 'none';
                        }
                        const chevron = parent.querySelector('.arrowPermission svg');
                        if (chevron) {
                            chevron.style.transform = parent.classList.contains('showMenu') ? 'rotate(0deg)' : 'rotate(-90deg)';
                        }
                    }
                },

                isModuleVisible(moduleName, groupName) {
                    if (!this.search || this.search.trim() === '') return true;
                    const q = this.search.toLowerCase().trim();
                    return String(moduleName || '').toLowerCase().includes(q) || String(groupName || '').toLowerCase().includes(q);
                },

                isCategoryVisible(category) {
                    return Array.from(category.querySelectorAll('.permissionItem')).some(module =>
                        this.isModuleVisible(module.dataset.name, module.dataset.group));
                }
            }));
        });
    </script>
@stop
