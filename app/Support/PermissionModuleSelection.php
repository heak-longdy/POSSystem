<?php

namespace App\Support;

use Illuminate\Support\Collection;

class PermissionModuleSelection
{
    public static function fromMenu(Collection $modules): Collection
    {
        return static::flatten(static::treeFromMenu($modules));
    }

    public static function treeFromMenu(Collection $modules): Collection
    {
        $modulesByPermission = [];
        foreach ($modules as $module) {
            foreach ($module->permission as $permission) {
                $modulesByPermission[$permission->name] = $module;
            }
        }

        // Dashboard is rendered directly by the sidebar, outside config/menu.php.
        $items = array_merge([
            ['path' => 'admin/dashboard', 'permission' => 'dashboard-view'],
        ], config('menu', []));
        $seen = [];

        return static::buildTree($items, $modulesByPermission, $seen);
    }

    public static function flatten(Collection $menu): Collection
    {
        return $menu->flatMap(fn ($item) => isset($item['module'])
            ? [$item['module']]
            : static::flatten($item['children'])->all())->values();
    }

    private static function buildTree(array $items, array $modulesByPermission, array &$seen): Collection
    {
        $menu = collect();
        foreach ($items as $item) {
            if (!filter_var($item['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN)
                || !filter_var($item['visible'] ?? true, FILTER_VALIDATE_BOOLEAN)
                || filter_var($item['disabled'] ?? false, FILTER_VALIDATE_BOOLEAN)
                || filter_var($item['hidden'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $children = $item['listMenu'] ?? $item['list-menu'] ?? $item['children'] ?? [];
            if ($children) {
                // Container permissions are navigation checks, not module entries.
                $children = static::buildTree($children, $modulesByPermission, $seen);
                if ($children->isNotEmpty()) {
                    $label = $item['label'] ?? $item['name'] ?? null;
                    if ($label) {
                        $menu->push(['label' => $label, 'children' => $children]);
                    } else {
                        $menu = $menu->concat($children);
                    }
                }
                continue;
            }

            $permission = static::modulePermission($item);
            $module = $modulesByPermission[$permission] ?? null;
            if (!$module || isset($seen[$module->id])) {
                continue;
            }

            // Shared screens use the module's first position in the menu.
            $seen[$module->id] = true;
            $menu->push(['label' => $item['name'] ?? null, 'module' => $module]);
        }

        return $menu;
    }

    private static function modulePermission(array $item): ?string
    {
        if (empty($item['path']) || empty($item['permission'])) {
            return null;
        }

        $candidates = (array) $item['permission'];
        $permission = reset($candidates);
        $path = trim(parse_url($item['path'], PHP_URL_PATH) ?? '', '/');
        $path = str_replace('/', '-', preg_replace('#^admin/#', '', $path));
        $matchLength = 0;
        foreach ($candidates as $candidate) {
            $module = substr($candidate, 0, strrpos($candidate, '-'));
            if (str_starts_with($path . '-', $module . '-') && strlen($module) > $matchLength) {
                $permission = $candidate;
                $matchLength = strlen($module);
            }
        }

        // Prefer the linked module over alternative access permissions (e.g.
        // Order View also opens Sales Report). Shared screens without their
        // own permission, such as Remaining Amount, use the first declaration.
        return $permission;
    }
}
