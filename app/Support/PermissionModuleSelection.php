<?php

namespace App\Support;

use Illuminate\Support\Collection;

class PermissionModuleSelection
{
    public static function fromMenu(Collection $modules): Collection
    {
        // Dashboard is rendered directly by the sidebar, outside config/menu.php.
        $menuPermissions = array_merge(['dashboard-view'], static::menuPermissions(config('menu', [])));

        return $modules->filter(function ($module) use ($menuPermissions) {
            return $module->permission->contains(fn ($permission) => in_array($permission->name, $menuPermissions, true));
        })->values();
    }

    private static function menuPermissions(array $items): array
    {
        $permissions = [];
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
                $permissions = array_merge($permissions, static::menuPermissions($children));
                continue;
            }
            if (empty($item['path']) || empty($item['permission'])) {
                continue;
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
            $permissions[] = $permission;
        }

        return array_values(array_unique($permissions));
    }
}
