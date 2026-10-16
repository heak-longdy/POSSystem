@foreach ($menu as $entry)
    @if (isset($entry['children']))
        @php
            $groupLabel = \App\Support\Language::translatedValue($entry['label']);
            $groupSearch = trim($ancestorSearch . ' ' . implode(' ', (array) $entry['label']));
        @endphp
        <div class="permission-category-block" data-group="{{ $groupLabel }}" x-show="isCategoryVisible($el)"
            style="grid-column: 1 / -1; min-width: 0; margin-top: 10px; padding-left: {{ $depth > 0 ? '16px' : '0' }};">
            <label class="parentLabel" style="font-weight: 600; font-size: {{ $depth > 0 ? '15px' : '17px' }}; color: #334155; display: block; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 14px;">
                {{ $groupLabel }}
            </label>
            <div class="permissionLayout" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; width: 100%;">
                @include('admin::pages.user.permission-menu', [
                    'menu' => $entry['children'],
                    'ancestorSearch' => $groupSearch,
                    'depth' => $depth + 1,
                ])
            </div>
        </div>
    @else
        @php
            $modul = $entry['module'];
            $moduleKey = 'permission.module.' . \Illuminate\Support\Str::slug($modul->name, '_');
            $moduleLabel = \App\Support\Language::translatedValue($entry['label'])
                ?: (__($moduleKey) !== $moduleKey ? __($moduleKey) : $modul->name);
            $moduleSearch = trim($moduleLabel . ' ' . $modul->name . ' ' . implode(' ', (array) $entry['label']));
        @endphp
        <div class="permissionItem showMenu"
            data-name="{{ $moduleSearch }}" data-group="{{ $ancestorSearch }}"
            x-show="isModuleVisible($el.dataset.name, $el.dataset.group)"
            data-module-id="{{ $modul->id }}"
            style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">

            <div class="permissionHeader arrowPermission"
                @click="toggleMenu($el)"
                style="display: flex; align-items: center; justify-content: space-between; width: 100%; cursor: pointer; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-feather="chevron-down" style="width: 18px; height: 18px; color: #64748b; transition: transform 0.2s ease;"></i>
                    <span style="font-weight: 600; font-size: 14px; color: #1e293b;">{{ $moduleLabel }}</span>
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
                        @php
                            $actionKey = 'permission.permission.' . $action->name;
                            $actionLabel = __($actionKey) !== $actionKey ? __($actionKey) : ($action->display_name ?: $action->name);
                        @endphp
                        <label class="permissionItemCh" for="perm-{{ $user->id }}-{{ $action->id }}"
                            style="display: flex; align-items: center; justify-content: space-between; padding: 6px 4px; cursor: pointer; border-bottom: 1px dashed #f1f5f9; margin: 0; user-select: none;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i data-feather="check-circle" style="width: 14px; height: 14px; color: #94a3b8;"></i>
                                <span style="font-size: 13px; color: #334155;">{{ $actionLabel }}
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
    @endif
@endforeach
