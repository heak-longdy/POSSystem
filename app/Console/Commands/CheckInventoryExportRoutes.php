<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class CheckInventoryExportRoutes extends Command
{
    protected $signature = 'inventory:check-export-routes';

    protected $description = 'Check inventory report and CSV routes without reading inventory data or changing caches';

    public function handle(Router $router): int
    {
        $this->line('Application: ' . base_path());
        $this->line('Route cache: ' . ($this->laravel->routesAreCached() ? $this->laravel->getCachedRoutesPath() : 'disabled'));
        $modules = [
            'stock-in' => 'StockInController',
            'stock-out' => 'StockOutController',
            'stock-transfer' => 'StockTransferController',
            'stock-on-hand' => 'StockOnHandController',
            'stock-movement' => 'StockMovementController',
        ];
        $rows = [];
        $failed = false;
        foreach ($modules as $module => $controller) {
            foreach (['report', 'export'] as $action) {
                $name = 'admin-' . $module . '-' . $action;
                $path = '/admin/' . $module . '/' . $action;
                $expected = 'App\\Http\\Controllers\\Admin\\Inventory\\' . $controller;
                $result = 'OK';
                try {
                    $route = $router->getRoutes()->match(Request::create(url($path), 'GET'));
                    if ($route->getName() !== $name || !$router->getRoutes()->getByName($name)
                        || $route->getActionName() !== $expected . '@' . $action) {
                        $result = 'WRONG ROUTE';
                    } elseif (!method_exists($expected, $action)) {
                        $result = 'MISSING CONTROLLER METHOD';
                    }
                } catch (HttpExceptionInterface $error) {
                    $result = 'HTTP ' . $error->getStatusCode();
                }
                $failed = $failed || $result !== 'OK';
                $rows[] = ['GET ' . $path, $result];
            }
        }
        $this->table(['Endpoint', 'Result'], $rows);
        if ($failed) {
            $this->error('Inventory export routes are incomplete. Verify the deployed source and rebuild the route cache in this application.');
            return self::FAILURE;
        }
        $this->info('All 10 routes resolve. Authenticated report data and workbook downloads still require verification.');
        return self::SUCCESS;
    }
}
