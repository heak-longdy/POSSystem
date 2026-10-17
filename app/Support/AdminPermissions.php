<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminPermissions
{
    public static function forAction(string $module, string $action, bool $editing = false): array
    {
        if (!array_key_exists($module, config('admin_permissions.modules', []))) {
            return [];
        }

        $actions = [
            'list' => 'view', 'view' => 'view', 'detail' => 'view', 'list-product' => 'view',
            'staff-history' => 'view', 'payment-details' => 'view', 'report' => 'view',
            'create' => 'create', 'edit' => 'update', 'update' => 'update', 'status' => 'update',
            'save' => $editing ? 'update' : 'create',
            'permission' => 'update', 'save-permission' => 'update',
            'change-password' => 'update', 'save-password' => 'update',
            'add-payment' => 'update', 'update-payment' => 'update', 'cancel' => 'update',
            'update-payment-status' => 'update', 'send-payment-reminder' => 'update',
            'paid' => 'update',
            'delete' => 'delete', 'delete-payment' => 'delete',
            'restore' => in_array($module, ['staff', 'staff-expense']) ? 'restore' : 'update',
            'destroy' => in_array($module, ['staff', 'staff-expense']) ? 'force-delete' : 'delete',
            'export' => in_array('excel', config('admin_permissions.extra_actions.' . $module, [])) ? 'excel' : 'view',
        ];

        return isset($actions[$action]) ? [$module . '-' . $actions[$action]] : [];
    }

    public static function forRequest(Request $request): array
    {
        $name = $request->route()->getName();
        if ($name === 'admin-dashboard') {
            return ['dashboard-view'];
        }
        if (str_starts_with($name ?? '', 'admin-file-manager-')) {
            $action = substr($name, strlen('admin-file-manager-'));
            $actions = [
                'index' => 'view', 'first' => 'view', 'files' => 'view', 'folders' => 'view',
                'upload' => 'create', 'create-folder' => 'create',
                'rename-file' => 'update', 'rename-folder' => 'update', 'restore-all' => 'update',
                'delete-file' => 'delete', 'delete-folder' => 'delete', 'delete-all' => 'delete',
            ];
            return ['file-manager-' . ($actions[$action] ?? 'view')];
        }
        if (in_array($name, ['admin-setting-setting', 'admin-setting-save', 'admin-our-service-index', 'admin-our-service-store'])) {
            return [$request->isMethod('GET') ? 'service-view' : 'service-update'];
        }
        if (str_starts_with($name ?? '', 'admin-select-')) {
            $modules = config('admin_permissions.lookups.' . substr($name, strlen('admin-select-')), []);
            return collect($modules)->flatMap(fn ($module) => [
                $module . '-view', $module . '-create', $module . '-update',
            ])->all();
        }
        if ($name === 'admin-stock-in-products') {
            return ['stock-in-view', 'stock-in-create', 'stock-in-update'];
        }
        if (str_starts_with($name ?? '', 'admin-shop-product')) {
            return [$name === 'admin-shop-product' ? 'shop-view' : 'shop-update'];
        }
        if (in_array($name, ['admin-stock-on-hand-report', 'admin-stock-movement-report'])) {
            return [str_replace(['admin-', '-report'], ['', '-excel'], $name)];
        }
        if ($name === 'admin-stock-on-hand-onFind') {
            return ['stock-on-hand-view', 'stock-out-create', 'stock-out-update', 'stock-transfer-create', 'stock-transfer-update'];
        }

        // Reports retain their existing alternative view permissions. Exports also
        // require the Excel permission already offered by the permission screen.
        $exports = [
            'admin-report-sales-report' => 'report-sales-excel',
            'admin-report-order-transaction-report' => 'report-transaction-excel',
            'admin-report-inventory-movement-report' => 'report-inventory-excel',
            'admin-report-staff-expense-report' => 'report-staff-expense-excel',
        ];
        if (isset($exports[$name])) {
            return [$exports[$name]];
        }
        if (in_array($name, ['admin-about-us-index', 'admin-about-us-store'])) {
            return [$name === 'admin-about-us-index' ? 'about-view' : 'about-update'];
        }
        if (in_array($name, ['admin-contact-contact', 'admin-contact-save'])) {
            return [$name === 'admin-contact-contact' ? 'contact-view' : 'contact-update'];
        }
        if (in_array($name, ['admin-page-page', 'admin-page-save'])) {
            $type = str_replace('_', '-', (string) ($request->route('type') ?? $request->input('type')));
            return [$type . ($name === 'admin-page-save' ? '-update' : '-view')];
        }

        // Longest prefix first: customer-paid must not be treated as customer.
        $modules = array_keys(config('admin_permissions.modules', []));
        usort($modules, fn ($a, $b) => strlen($b) <=> strlen($a));
        foreach ($modules as $module) {
            $prefix = 'admin-' . $module . '-';
            if (str_starts_with($name ?? '', $prefix)) {
                return static::forAction($module, substr($name, strlen($prefix)), (bool) $request->route('id'));
            }
        }

        return [];
    }

    public static function allowsAction(string $module, string $action): bool
    {
        $permissions = static::forAction($module, $action);
        return !$permissions || Gate::any($permissions);
    }

    public static function visibleMenu(array $items): array
    {
        return array_values(array_filter(array_map(function ($item) {
            foreach (['listMenu', 'list-menu', 'children'] as $key) {
                if (!empty($item[$key])) {
                    $item[$key] = static::visibleMenu($item[$key]);
                    // A navigation group is visible if any of its children is accessible.
                    return $item[$key] ? $item : null;
                }
            }
            $permissions = (array) ($item['permission'] ?? []);
            return !$permissions || Gate::any($permissions) ? $item : null;
        }, $items)));
    }
}
