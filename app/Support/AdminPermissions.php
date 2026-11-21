<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminPermissions
{
    /**
     * Standalone modules outside config('admin_permissions.modules') that participate in permission checks.
     */
    protected const STANDALONE_MODULES = [
        'remaining-amount',
        'stock-on-hand',
        'stock-movement',
    ];

    /**
     * Modules that use dedicated restore and force-delete permissions instead of update/delete.
     */
    protected const RESTORABLE_MODULES = [
        'staff',
        'staff-expense',
        'other-income',
    ];

    /**
     * Actions specific to shop product assignments that map to 'shop-product'.
     */
    protected const SHOP_PRODUCT_ACTIONS = [
        'product',
        'product-create',
        'product-edit',
        'product-save',
        'product-delete',
        'product-restore',
        'product-destroy',
    ];

    /**
     * Standard action-to-ability mapping across modules.
     */
    protected const BASE_ACTION_ABILITIES = [
        'list' => 'view',
        'view' => 'view',
        'detail' => 'view',
        'details' => 'view',
        'list-product' => 'view',
        'staff-history' => 'view',
        'supplier-history' => 'view',
        'payment-details' => 'view',
        'report' => 'view',
        'create' => 'create',
        'preview-previous-balance' => 'create',
        'edit' => 'update',
        'update' => 'update',
        'status' => 'update',
        'permission' => 'update',
        'save-permission' => 'update',
        'permission-sync' => 'update',
        'permission-store-custom' => 'update',
        'permission-get-user' => 'view',
        'change-password' => 'update',
        'save-password' => 'update',
        'add-payment' => 'update',
        'update-payment' => 'update',
        'store-payment' => 'update',
        'cancel' => 'update',
        'update-payment-status' => 'update',
        'send-payment-reminder' => 'update',
        'send-reminder' => 'update',
        'paid' => 'update',
        'receive' => 'update',
        'receive-all' => 'update',
        'full-receive' => 'update',
        'partial-receive' => 'update',
        'payment' => 'update',
        'items' => 'view',
        'print' => 'view',
        'delete' => 'delete',
        'delete-payment' => 'delete',
    ];

    /**
     * Sub-module prefix action mappings (e.g. file-manager, backup).
     */
    protected const SUBMODULE_PREFIX_ACTIONS = [
        'admin-file-manager-' => [
            'module' => 'file-manager',
            'actions' => [
                'index' => 'view',
                'first' => 'view',
                'files' => 'view',
                'folders' => 'view',
                'upload' => 'create',
                'create-folder' => 'create',
                'rename-file' => 'update',
                'rename-folder' => 'update',
                'restore-all' => 'update',
                'delete-file' => 'delete',
                'delete-folder' => 'delete',
                'delete-all' => 'delete',
            ],
        ],
        'admin-backup-' => [
            'module' => 'backup',
            'actions' => [
                'index' => 'view',
                'create' => 'create',
                'download' => 'download',
                'download-cloud' => 'download',
                'upload-cloud' => 'create',
                'restore-upload' => 'restore',
                'restore-local' => 'restore',
                'restore-cloud' => 'restore',
                'delete' => 'delete',
                'delete-cloud' => 'delete',
                'save-settings' => 'update',
                'test-cloud' => 'view',
            ],
        ],
    ];

    /**
     * Static route name to permission mapping for single standalone endpoints.
     */
    protected const STATIC_ROUTE_PERMISSIONS = [
        'admin-dashboard' => ['dashboard-view'],
        'admin-stock-in-products' => ['stock-in-view', 'stock-in-create', 'stock-in-update'],
        'admin-stock-on-hand-onFind' => [
            'stock-on-hand-view',
            'stock-out-create',
            'stock-out-update',
            'stock-transfer-create',
            'stock-transfer-update',
        ],
        'admin-setting-invoice-index' => ['setting-invoice-view'],
        'setting-invoice-index' => ['setting-invoice-view'],
        'admin-setting-invoice-save' => ['setting-invoice-update'],
        'admin-setting-invoice-preview' => ['setting-invoice-update'],
        'setting-invoice-save' => ['setting-invoice-update'],
    ];

    /**
     * Route pairs that toggle between view and update permissions based on method or route name.
     */
    protected const VIEW_UPDATE_TOGGLE_ROUTES = [
        'service' => [
            'admin-setting-setting',
            'admin-setting-save',
            'admin-our-service-index',
            'admin-our-service-store',
        ],
        'about' => [
            'view' => 'admin-about-us-index',
            'update' => 'admin-about-us-store',
        ],
        'contact' => [
            'view' => 'admin-contact-contact',
            'update' => 'admin-contact-save',
        ],
    ];

    /**
     * Explicit report export route mappings.
     */
    protected const EXPORT_ROUTE_PERMISSIONS = [
        'admin-report-consolidated-product-quantity-report' => 'report-consolidated-product-quantity-excel',
        'admin-report-sales-report' => 'report-sales-excel',
        'admin-report-order-transaction-report' => 'report-transaction-excel',
        'admin-report-inventory-movement-report' => 'report-inventory-excel',
        'admin-report-staff-expense-report' => 'report-staff-expense-excel',
        'admin-report-other-income-report' => 'report-other-income-excel',
        'admin-report-customer-remaining-amount-report' => 'report-sales-excel',
        'admin-report-product-report' => 'product-excel',
        'admin-report-shop-report' => 'shop-view',
        'admin-report-customer-report' => 'customer-view',
        'admin-report-position-report' => 'position-view',
        'admin-report-staff-report' => 'staff-view',
        'admin-report-user-report' => 'user-view',
        'admin-report-supplier-report' => 'supplier-view',
        'admin-report-uom-report' => 'uom-view',
        'admin-stock-on-hand-report' => 'stock-on-hand-excel',
        'admin-stock-movement-report' => 'stock-movement-excel',
        'admin-product-report' => 'product-excel',
        'admin-staff-expense-report' => 'staff-expense-excel',
        'admin-other-income-report' => 'other-income-excel',
    ];

    /**
     * Report modules that provide standard index/daily/monthly/details views.
     */
    protected const REPORT_MODULE_VIEWS = [
        'report-sales' => 'report-sales',
        'report-consolidated-product-quantity' => 'report-consolidated-product-quantity',
        'report-order-transaction' => 'report-transaction',
        'report-inventory-movement' => 'report-inventory',
        'report-staff-expense' => 'report-staff-expense',
        'report-other-income' => 'report-other-income',
        'report-customer-remaining-amount' => 'report-sales',
    ];

    /**
     * Standard sub-actions supported across report modules.
     */
    protected const REPORT_VIEW_ACTIONS = ['index', 'daily', 'monthly', 'details'];

    /**
     * Cached sorted module list for longest-prefix matching.
     *
     * @var array<string>|null
     */
    protected static ?array $sortedModules = null;

    /**
     * Map a module and action to the corresponding permission strings.
     */
    public static function forAction(string $module, string $action, bool $editing = false): array
    {
        $allModules = static::getSortedModules();
        if (!in_array($module, $allModules, true)) {
            return [];
        }

        // Shop product links and routes share the same assignment permissions.
        if ($module === 'shop' && in_array($action, static::SHOP_PRODUCT_ACTIONS, true)) {
            return ['shop-product'];
        }

        // Legacy URL opens the remaining-amount listing, not a creation form.
        if ($module === 'remaining-amount' && $action === 'create') {
            return ['remaining-amount-view'];
        }

        // Dynamic permission rules based on state/action
        if ($action === 'save') {
            return [$module . '-' . ($editing ? 'update' : 'create')];
        }

        if ($action === 'restore') {
            $ability = in_array($module, static::RESTORABLE_MODULES, true) ? 'restore' : 'update';
            return [$module . '-' . $ability];
        }

        if ($action === 'destroy') {
            $ability = in_array($module, static::RESTORABLE_MODULES, true) ? 'force-delete' : 'delete';
            return [$module . '-' . $ability];
        }

        if ($action === 'export') {
            $hasDedicatedExcel = in_array($module, ['stock-on-hand', 'stock-movement'], true)
                || in_array('excel', config('admin_permissions.extra_actions.' . $module, []));
            return [$module . '-' . ($hasDedicatedExcel ? 'excel' : 'view')];
        }

        if ($action === 'detail') {
            $hasDedicatedDetail = in_array('detail', config('admin_permissions.extra_actions.' . $module, []));
            if ($hasDedicatedDetail) {
                return [$module . '-detail'];
            }
        }

        if (in_array($action, ['receive', 'partial-receive', 'full-receive', 'fully-received'], true)) {
            return [$module . '-update', $module . '-create'];
        }

        if (in_array($action, ['paid', 'payment', 'update-payment-status', 'payment-status'], true)) {
            return [$module . '-update', $module . '-create'];
        }

        if (in_array($action, ['print', 'items'], true)) {
            return [$module . '-view'];
        }

        if (isset(static::BASE_ACTION_ABILITIES[$action])) {
            return [$module . '-' . static::BASE_ACTION_ABILITIES[$action]];
        }

        return [$module . '-' . $action];
    }

    /**
     * Inspect an incoming request and determine all acceptable permissions.
     */
    public static function forRequest(Request $request): array
    {
        $name = $request->route()?->getName();
        if (!$name) {
            return [];
        }

        // 1. Static single-route mappings
        if (isset(static::STATIC_ROUTE_PERMISSIONS[$name])) {
            return static::STATIC_ROUTE_PERMISSIONS[$name];
        }

        // 2. Order bulk print prefix
        if (str_starts_with($name, 'admin-order-bulk-print-')) {
            return ['order-bulk-print-view'];
        }

        // 3. Sub-module prefix action mappings (file-manager, backup)
        if ($permissions = static::resolveSubmodulePrefixRoute($name)) {
            return $permissions;
        }

        // 4. View / update toggled routes (service, about, contact, page)
        if ($permissions = static::resolveToggledRoute($name, $request)) {
            return $permissions;
        }

        // 5. Lookup select routes (admin-select-*)
        if ($permissions = static::resolveLookupRoute($name)) {
            return $permissions;
        }

        // 6. Report routes (views and exports)
        if ($permissions = static::resolveReportRoute($name)) {
            return $permissions;
        }

        // 7. General CRUD module routes (longest-prefix matched)
        return static::resolveCrudRoute($name, $request);
    }

    /**
     * Check whether the authenticated user has permission for a specific module action.
     */
    public static function allowsAction(string $module, string $action): bool
    {
        $permissions = static::forAction($module, $action);
        return $permissions && Gate::any($permissions);
    }

    /**
     * Filter navigation menu items based on the user's permissions.
     */
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
            return $permissions && Gate::any($permissions) ? $item : null;
        }, $items)));
    }

    /**
     * Generate a cryptographic fingerprint of the current user's role and permission scope.
     */
    public static function workspaceScope(): string
    {
        $user = auth()->user();
        if (!$user) {
            return '';
        }

        // Invalidate saved tabs after a user switch or permission change. Include
        // a version so tabs saved under the former permissive rules are discarded.
        return hash('sha256', json_encode([
            1,
            $user->getAuthIdentifier(),
            $user->role,
            $user->role === 'super_admin' ? ['*']
                : $user->getAllPermissions()->pluck('name')->sort()->values()->all(),
        ]));
    }

    /**
     * Resolve submodule actions (e.g., file-manager, backup).
     */
    protected static function resolveSubmodulePrefixRoute(string $name): ?array
    {
        foreach (static::SUBMODULE_PREFIX_ACTIONS as $prefix => $config) {
            if (str_starts_with($name, $prefix)) {
                $action = substr($name, strlen($prefix));
                $ability = $config['actions'][$action] ?? 'view';
                return [$config['module'] . '-' . $ability];
            }
        }

        return null;
    }

    /**
     * Resolve routes that toggle between view and update based on request method or route name.
     */
    protected static function resolveToggledRoute(string $name, Request $request): ?array
    {
        // Service routes
        if (in_array($name, static::VIEW_UPDATE_TOGGLE_ROUTES['service'], true)) {
            return [$request->isMethod('GET') ? 'service-view' : 'service-update'];
        }

        // About Us routes
        $about = static::VIEW_UPDATE_TOGGLE_ROUTES['about'];
        if ($name === $about['view'] || $name === $about['update']) {
            return [$name === $about['view'] ? 'about-view' : 'about-update'];
        }

        // Contact routes
        $contact = static::VIEW_UPDATE_TOGGLE_ROUTES['contact'];
        if ($name === $contact['view'] || $name === $contact['update']) {
            return [$name === $contact['view'] ? 'contact-view' : 'contact-update'];
        }

        // Custom CMS page routes
        if (in_array($name, ['admin-page-page', 'admin-page-save'], true)) {
            $type = str_replace('_', '-', (string) ($request->route('type') ?? $request->input('type')));
            return [$type . ($name === 'admin-page-save' ? '-update' : '-view')];
        }

        return null;
    }

    /**
     * Resolve select/lookup routes (admin-select-*).
     */
    protected static function resolveLookupRoute(string $name): ?array
    {
        if (!str_starts_with($name, 'admin-select-')) {
            return null;
        }

        $lookup = substr($name, strlen('admin-select-'));
        $modules = config('admin_permissions.lookups.' . $lookup, []);

        // Safety fallback if config cache on server is stale
        if (empty($modules) && $lookup === 'available-shop-products') {
            $modules = ['shop', 'product'];
        }

        $perms = collect($modules)->flatMap(fn ($module) => [
            $module . '-view',
            $module . '-create',
            $module . '-update',
        ]);

        if ($lookup === 'available-shop-products' || in_array('shop', (array) $modules, true)) {
            $perms->push('shop-product');
        }

        return $perms->unique()->values()->all();
    }

    /**
     * Resolve report view and export routes.
     */
    protected static function resolveReportRoute(string $name): ?array
    {
        if (isset(static::EXPORT_ROUTE_PERMISSIONS[$name])) {
            return [static::EXPORT_ROUTE_PERMISSIONS[$name]];
        }

        foreach (static::REPORT_MODULE_VIEWS as $prefix => $module) {
            foreach (static::REPORT_VIEW_ACTIONS as $action) {
                if ($name === 'admin-' . $prefix . '-' . $action) {
                    return [$module . '-view'];
                }
            }
        }

        return null;
    }

    /**
     * Match standard CRUD module routes using longest-prefix first.
     */
    protected static function resolveCrudRoute(string $name, Request $request): array
    {
        $modules = static::getSortedModules();

        foreach ($modules as $module) {
            $prefix = 'admin-' . $module . '-';
            if (str_starts_with($name, $prefix)) {
                $action = substr($name, strlen($prefix));
                $isEditing = (bool) $request->route('id');
                return static::forAction($module, $action, $isEditing);
            }
        }

        return [];
    }

    /**
     * Get the cached list of module names sorted longest-first.
     *
     * @return array<string>
     */
    public static function getSortedModules(): array
    {
        if (static::$sortedModules === null) {
            $configured = array_keys(config('admin_permissions.modules', []));
            $dbModules = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('module_permissions')) {
                    $dbModules = \App\Models\ModulePermission::pluck('name')
                        ->map(fn ($name) => \Illuminate\Support\Str::slug($name))
                        ->filter()
                        ->all();
                }
            } catch (\Throwable) {
                // Fallback to static config if DB is temporarily unreachable
            }

            $all = array_unique(array_merge($configured, static::STANDALONE_MODULES, $dbModules));
            usort($all, fn ($a, $b) => strlen($b) <=> strlen($a));
            static::$sortedModules = $all;
        }

        return static::$sortedModules;
    }
}
