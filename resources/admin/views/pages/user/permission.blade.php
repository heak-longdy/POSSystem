@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xPermission">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-user-save-permission', $user->id) !!}" method="POST">
            {{ csrf_field() }}
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-user-list', 1) !!}"></i>
                    @lang('permission.title') &mdash; {{ $user->name }}
                    @if($user->role)
                        <span class="badge-role" style="font-size: 13px; font-weight: normal; margin-left: 8px; background: #e0f2fe; color: #0369a1; padding: 3px 10px; border-radius: 12px;">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
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
                            Status: <span style="font-weight: 600; color: {{ $user->status == 1 ? '#16a34a' : '#dc2626' }};">{{ $user->status == 1 ? 'Active' : 'Disabled' }}</span>
                        </div>
                        <div style="font-size: 13px; color: #475569; background: #fff; padding: 6px 14px; border-radius: 6px; border: 1px solid #cbd5e1;">
                            Selected: <strong style="color: #2563eb;" x-text="selectedCount"></strong> / <span x-text="totalCount"></span>
                        </div>
                    </div>
                </div>

                <!-- Permission Controls & Search -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="permissionLayoutGp" style="width: 100%;">
                        <div class="headerListPermission" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <label class="titlePer" style="margin: 0; font-weight: 600;">@lang('permission.title')</label>
                            </div>
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="position: relative;">
                                    <input type="text" x-model="search" placeholder="Search modules..." style="padding: 6px 12px 6px 32px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; width: 200px;">
                                    <i data-feather="search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #94a3b8;"></i>
                                </div>
                                <label class="permissionListCheckall" style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0; user-select: none;">
                                    <span class="span" style="font-size: 14px; font-weight: 500;">Select All</span>
                                    <input type="checkbox" id="chk-permissionSelectAll" class="chk-permissionSelectAll"
                                        :checked="isAllSelected()"
                                        @change="toggleSelectAll($event.target.checked)"
                                        style="width: 18px; height: 18px; cursor: pointer; accent-color: #2563eb;" />
                                </label>
                            </div>
                        </div>

                        @error('permission')
                            <div class="error" style="color: red; margin-bottom: 10px;">{{ $message }}</div>
                        @enderror

                        <!-- Grouped Permission Modules -->
                        @foreach ($groupedModules as $groupName => $modules)
                            <div class="permission-category-block" x-show="isCategoryVisible('{{ addslashes($groupName) }}')" style="margin-bottom: 24px;">
                                <label class="parentLabel" style="font-weight: 600; font-size: 17px; color: #334155; display: block; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 14px;">
                                    {{ $groupName }}
                                </label>

                                <div class="permissionLayout" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; width: 100%;">
                                    @foreach ($modules as $modul)
                                        <div class="permissionItem showMenu"
                                            x-show="isModuleVisible('{{ addslashes($modul->name) }}', '{{ addslashes($groupName) }}')"
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
                                                    <span style="font-size: 12px; color: #94a3b8;">All</span>
                                                    <input type="checkbox"
                                                        id="chk-group-{{ $modul->id }}"
                                                        class="chk-permission-group"
                                                        :checked="isModuleAllChecked({{ $modul->id }})"
                                                        @change="toggleModuleAll({{ $modul->id }}, $event.target.checked)"
                                                        style="width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb;" />
                                                </div>
                                            </div>

                                            <div class="permissionListItemGpCh" style="display: block; padding-top: 10px;">
                                                @if(isset($modul->permission) && $modul->permission->count() > 0)
                                                    @foreach ($modul->permission as $action)
                                                        <label class="permissionItemCh" for="perm-{{ $action->id }}"
                                                            style="display: flex; align-items: center; justify-content: space-between; padding: 6px 4px; cursor: pointer; border-bottom: 1px dashed #f1f5f9; margin: 0; user-select: none;">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <i data-feather="check-circle" style="width: 14px; height: 14px; color: #94a3b8;"></i>
                                                                <span style="font-size: 13px; color: #334155;">{{ $action->display_name ?: $action->name }}</span>
                                                            </div>
                                                            <div class="inputItem">
                                                                <input type="checkbox"
                                                                    id="perm-{{ $action->id }}"
                                                                    name="permission[]"
                                                                    value="{{ $action->name }}"
                                                                    class="permissionAllitem perm-item-{{ $modul->id }}"
                                                                    data-module-id="{{ $modul->id }}"
                                                                    @if(in_array($action->name, $userPermissions)) checked @endif
                                                                    @change="onPermissionChange()"
                                                                    style="width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb;" />
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                @else
                                                    <div style="font-size: 12px; color: #94a3b8; padding: 6px 0;">No actions defined</div>
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
            Alpine.data('xPermission', () => ({
                search: '',
                totalCount: 0,
                selectedCount: 0,

                init() {
                    this.$nextTick(() => {
                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                        this.updateCounts();
                    });
                },

                updateCounts() {
                    const allBoxes = document.querySelectorAll('input.permissionAllitem');
                    this.totalCount = allBoxes.length;
                    this.selectedCount = document.querySelectorAll('input.permissionAllitem:checked').length;
                },

                onPermissionChange() {
                    this.updateCounts();
                },

                toggleSelectAll(checked) {
                    const allBoxes = document.querySelectorAll('input.permissionAllitem');
                    allBoxes.forEach(box => {
                        box.checked = checked;
                    });
                    const groupBoxes = document.querySelectorAll('input.chk-permission-group');
                    groupBoxes.forEach(box => {
                        box.checked = checked;
                    });
                    this.updateCounts();
                },

                isAllSelected() {
                    if (this.totalCount === 0) return false;
                    return this.selectedCount === this.totalCount;
                },

                toggleModuleAll(moduleId, checked) {
                    const moduleBoxes = document.querySelectorAll('.perm-item-' + moduleId);
                    moduleBoxes.forEach(box => {
                        box.checked = checked;
                    });
                    this.updateCounts();
                },

                isModuleAllChecked(moduleId) {
                    const moduleBoxes = document.querySelectorAll('.perm-item-' + moduleId);
                    if (!moduleBoxes || moduleBoxes.length === 0) return false;
                    let allChecked = true;
                    moduleBoxes.forEach(box => {
                        if (!box.checked) allChecked = false;
                    });
                    return allChecked;
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

                isCategoryVisible(groupName) {
                    if (!this.search || this.search.trim() === '') return true;
                    const q = this.search.toLowerCase().trim();
                    if (groupName.toLowerCase().includes(q)) return true;
                    return true;
                }
            }));
        });
    </script>
@stop
