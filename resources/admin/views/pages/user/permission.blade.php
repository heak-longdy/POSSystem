@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xPermission({{ json_encode(old('_permissions_form') ? (is_array(old('permission')) ? old('permission') : []) : $userPermissions) }})">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-user-save-permission', $user->id) !!}" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="_permissions_form" value="1">
            {{-- Excluded modules are not editable here; retain their existing assignments. --}}
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
                <div class="user-permission-card" style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 20px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; overflow: hidden; background: #cbd5e1; display: flex; align-items: center; justify-content: center;">
                            @if($user->image)
                                <img src="{!! asset('file_manager' . $user->image) !!}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i data-feather="user" style="width: 24px; height: 24px; color: #64748b;"></i>
                            @endif
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 16px; color: #1e293b;">{{ $user->name }}</div>
                            <div style="font-size: 13px; color: #64748b;">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div style="font-size: 13px; color: #64748b;">
                            {{ __('global.table.status') }}: <span style="font-weight: 600; color: {{ $user->status == 1 ? '#16a34a' : '#dc2626' }};">{{ $user->status == 1 ? __('user.form.status.active') : __('user.form.status.disable') }}</span>
                        </div>
                        <div style="font-size: 13px; color: #475569; background: #fff; padding: 6px 14px; border-radius: 6px; border: 1px solid #cbd5e1;">
                            {{ __('permission.selected') }}: <strong style="color: #2563eb;" x-text="selectedCount"></strong> / <span x-text="totalCount"></span>
                        </div>
                    </div>
                </div>

                @if ($user->role === 'super_admin')
                    <p>{{ __('permission.super_admin_access') }}</p>
                @endif
                @if (count($inheritedPermissions))
                    <p>{{ __('permission.inherited_help') }}</p>
                @endif

                <!-- Permission Controls & Search -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="permissionLayoutGp" style="width: 100%;">
                        <div class="headerListPermission" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <label class="titlePer" style="margin: 0; font-weight: 600;">@lang('permission.title')</label>
                            </div>
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="position: relative;">
                                    <input type="text" x-model="search" placeholder="{{ __('permission.search_modules') }}" style="padding: 6px 12px 6px 32px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; width: 200px;">
                                    <i data-feather="search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #94a3b8;"></i>
                                </div>
                                <label class="permissionListCheckall" style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0; user-select: none;">
                                    <span class="span" style="font-size: 14px; font-weight: 500;">{{ __('permission.select_all') }}</span>
                                    <input type="checkbox" id="chk-permissionSelectAll-{{ $user->id }}" class="chk-permissionSelectAll"
                                        x-effect="$el.checked = isAllSelected(); $el.indeterminate = selectedCount > 0 && !isAllSelected()"
                                        @change="toggleSelectAll($event.target.checked)"
                                        style="width: 18px; height: 18px; cursor: pointer; accent-color: #2563eb;" />
                                </label>
                            </div>
                        </div>

                        @foreach ($errors->get('permission') + $errors->get('permission.*') as $messages)
                            @foreach ((array) $messages as $message)
                                <div class="error" style="color: red; margin-bottom: 10px;">{{ $message }}</div>
                            @endforeach
                        @endforeach

                        <!-- Grouped Permission Modules -->
                        @foreach ($groupedModules as $groupName => $modules)
                            <div class="permission-category-block" data-group="{{ $groupName }}" x-show="isCategoryVisible($el)" style="margin-bottom: 24px;">
                                <label class="parentLabel" style="font-weight: 600; font-size: 17px; color: #334155; display: block; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 14px;">
                                    {{ $groupName }}
                                </label>

                                <div class="permissionLayout" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; width: 100%;">
                                    @foreach ($modules as $modul)
                                        <div class="permissionItem showMenu"
                                            data-name="{{ $modul->name }}" data-group="{{ $groupName }}"
                                            x-show="isModuleVisible($el.dataset.name, $el.dataset.group)"
                                            data-module-id="{{ $modul->id }}"
                                            style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                            
                                            <div class="permissionHeader arrowPermission"
                                                @click="toggleMenu($el)"
                                                style="display: flex; align-items: center; justify-content: space-between; width: 100%; cursor: pointer; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i data-feather="chevron-down" style="width: 18px; height: 18px; color: #64748b; transition: transform 0.2s ease;"></i>
                                                    <span style="font-weight: 600; font-size: 14px; color: #1e293b;">{{ $modul->name }}</span>
                                                </div>
                                                <div class="inputItem" @click.stop="" style="display: flex; align-items: center; gap: 6px;">
                                                    <span style="font-size: 12px; color: #94a3b8;">{{ __('permission.all') }}</span>
                                                    <input type="checkbox"
                                                        id="chk-group-{{ $user->id }}-{{ $modul->id }}"
                                                        class="chk-permission-group"
                                                        x-effect="$el.checked = isModuleAllChecked({{ $modul->id }}); $el.indeterminate = isModulePartial({{ $modul->id }})"
                                                        @change="toggleModuleAll({{ $modul->id }}, $event.target.checked)"
                                                        style="width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb;" />
                                                </div>
                                            </div>

                                            <div class="permissionListItemGpCh" style="display: block; padding-top: 10px;">
                                                @if(isset($modul->permission) && $modul->permission->count() > 0)
                                                    @foreach ($modul->permission as $action)
                                                        <label class="permissionItemCh" for="perm-{{ $user->id }}-{{ $action->id }}"
                                                            style="display: flex; align-items: center; justify-content: space-between; padding: 6px 4px; cursor: pointer; border-bottom: 1px dashed #f1f5f9; margin: 0; user-select: none;">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <i data-feather="check-circle" style="width: 14px; height: 14px; color: #94a3b8;"></i>
                                                                <span style="font-size: 13px; color: #334155;">{{ $action->display_name ?: $action->name }}
                                                                    @if (in_array($action->name, $inheritedPermissions))
                                                                        <small>({{ __('permission.inherited') }})</small>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="inputItem">
                                                                <input type="checkbox"
                                                                    id="perm-{{ $user->id }}-{{ $action->id }}"
                                                                    name="permission[]"
                                                                    value="{{ $action->name }}"
                                                                    class="permissionAllitem perm-item-{{ $modul->id }}"
                                                                    data-module-id="{{ $modul->id }}"
                                                                    x-model="selectedPermissions"
                                                                    style="width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb;" />
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                @else
                                                    <div style="font-size: 12px; color: #94a3b8; padding: 6px 0;">{{ __('permission.no_actions') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="form-button" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; gap: 12px;">
                    <button type="submit" color="primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px;">
                        <i data-feather="save"></i>
                        <span>@lang('permission.button.update')</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-user-list', 1) !!}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px;">
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

                init() {
                    this.$nextTick(() => {
                        this.availablePermissions = Array.from(this.$root.querySelectorAll('input.permissionAllitem')).map(box => box.value);
                        this.selectedPermissions = this.selectedPermissions.filter(name => this.availablePermissions.includes(name));
                        if (typeof feather !== 'undefined') feather.replace();
                    });
                },

                get totalCount() { return this.availablePermissions.length; },
                get selectedCount() { return this.selectedPermissions.length; },

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
                    return moduleName.toLowerCase().includes(q) || groupName.toLowerCase().includes(q);
                },

                isCategoryVisible(category) {
                    return Array.from(category.querySelectorAll('.permissionItem')).some(module =>
                        this.isModuleVisible(module.dataset.name, module.dataset.group));
                }
            }));
        });
    </script>
@stop
