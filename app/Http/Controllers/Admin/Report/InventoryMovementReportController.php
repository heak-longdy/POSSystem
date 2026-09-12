<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockHistory;
use App\Models\StockType;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryMovementReportController extends Controller
{
    protected $layout = 'admin::pages.report.inventoryMovement.';
    private $routeName = 'report-inventory-movement';

    public function __construct()
    {
        $this->middleware('permission:report-inventory-view|stock-movement-view|report-sales-view|booking-view', [
            'only' => ['index', 'daily', 'monthly', 'report', 'details']
        ]);
    }

    /**
     * Entry point: Default to Daily view
     */
    public function index(Request $req)
    {
        return redirect()->route('admin-' . $this->routeName . '-daily', $req->query());
    }

    /**
     * Daily Inventory Movement Report View
     */
    public function daily(Request $req)
    {
        $dates = $this->resolveDailyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'daily');

        $baseQuery = $this->buildFilteredMovementQuery($req, $dates['from'], $dates['to']);

        // Overall summary statistics for filtered date range
        $summary = $this->calculateSummaryMetrics(clone $baseQuery, $dates['from'], $dates['to']);

        // Daily aggregated breakdown
        $dailyRows = $this->aggregateDailyMovements(clone $baseQuery);

        $filterOptions = $this->getFilterOptions();

        return view($this->layout . 'index', [
            'viewMode' => 'daily',
            'routeName' => $this->routeName,
            'from_date' => $dates['from'],
            'to_date' => $dates['to'],
            'filters' => $appliedFilters,
            'summary' => $summary,
            'rows' => $dailyRows,
            'shops' => $filterOptions['shops'],
            'categories' => $filterOptions['categories'],
            'products' => $filterOptions['products'],
            'stockTypes' => $filterOptions['stockTypes'],
            'staffUsers' => $filterOptions['staffUsers'],
            'movementTypes' => $filterOptions['movementTypes'],
        ]);
    }

    /**
     * Monthly Inventory Movement Report View
     */
    public function monthly(Request $req)
    {
        $monthRange = $this->resolveMonthlyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'monthly');

        $baseQuery = $this->buildFilteredMovementQuery($req, $monthRange['from'], $monthRange['to']);

        // Overall summary statistics for filtered monthly range
        $summary = $this->calculateSummaryMetrics(clone $baseQuery, $monthRange['from'], $monthRange['to']);

        // Monthly aggregated breakdown
        $monthlyRows = $this->aggregateMonthlyMovements(clone $baseQuery);

        $filterOptions = $this->getFilterOptions();

        return view($this->layout . 'index', [
            'viewMode' => 'monthly',
            'routeName' => $this->routeName,
            'selectedYear' => $monthRange['year'],
            'from_month' => $monthRange['from_month'],
            'to_month' => $monthRange['to_month'],
            'from_date' => $monthRange['from'],
            'to_date' => $monthRange['to'],
            'filters' => $appliedFilters,
            'summary' => $summary,
            'rows' => $monthlyRows,
            'shops' => $filterOptions['shops'],
            'categories' => $filterOptions['categories'],
            'products' => $filterOptions['products'],
            'stockTypes' => $filterOptions['stockTypes'],
            'staffUsers' => $filterOptions['staffUsers'],
            'movementTypes' => $filterOptions['movementTypes'],
            'availableYears' => $this->getAvailableYears(),
        ]);
    }

    /**
     * API / JSON endpoint for export and charts
     */
    public function report(Request $req)
    {
        $mode = $req->get('view_mode', 'daily');

        if ($mode === 'monthly') {
            $monthRange = $this->resolveMonthlyDateRange($req);
            $baseQuery = $this->buildFilteredMovementQuery($req, $monthRange['from'], $monthRange['to']);
            $summary = $this->calculateSummaryMetrics(clone $baseQuery, $monthRange['from'], $monthRange['to']);
            $rows = $this->aggregateMonthlyMovements(clone $baseQuery);
        } else {
            $dates = $this->resolveDailyDateRange($req);
            $baseQuery = $this->buildFilteredMovementQuery($req, $dates['from'], $dates['to']);
            $summary = $this->calculateSummaryMetrics(clone $baseQuery, $dates['from'], $dates['to']);
            $rows = $this->aggregateDailyMovements(clone $baseQuery);
        }

        return response()->json([
            'status' => 'success',
            'view_mode' => $mode,
            'summary' => $summary,
            'rows' => $rows,
        ]);
    }

    /**
     * AJAX endpoint to retrieve all itemized movement records for a period (e.g. '2026-08-30' or '2026-08')
     */
    public function details(Request $req, $period)
    {
        $query = StockHistory::query()
            ->with([
                'shop:id,name,phone,address',
                'product' => function ($p) {
                    $p->with(['category:id,name', 'uom:id,name']);
                },
                'user:id,name,phone',
                'barber:id,name,phone',
            ]);

        // Period filter: check if daily (YYYY-MM-DD) or monthly (YYYY-MM)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            $query->whereDate('created_at', $period);
        } elseif (preg_match('/^\d{4}-\d{2}$/', $period)) {
            $startOfMonth = Carbon::createFromFormat('Y-m', $period)->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::createFromFormat('Y-m', $period)->endOfMonth()->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(created_at)'), [$startOfMonth, $endOfMonth]);
        }

        // Apply any active secondary filters
        $this->applySecondaryFilters($query, $req);

        $histories = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $formatted = $histories->map(function ($item) {
            $isSales = ($item->status === 'stock_out') && ($item->transfer_type === 'booking' || $item->type === 'customer');
            $isTransfer = ($item->status === 'stock_transfer');
            $isStockIn = ($item->status === 'stock_in');
            $isInternalOut = ($item->status === 'stock_out') && !$isSales;

            $movementLabel = __('inventory_movement.movement_badge.movement');
            $badgeClass = 'primary';

            if ($isStockIn) {
                $movementLabel = __('inventory_movement.movement_badge.stock_in');
                $badgeClass = 'success';
            } elseif ($isSales) {
                $movementLabel = __('inventory_movement.movement_badge.pos_sale');
                $badgeClass = 'primary';
            } elseif ($isTransfer) {
                $movementLabel = __('inventory_movement.movement_badge.stock_transfer');
                $badgeClass = 'purple';
            } elseif ($isInternalOut) {
                $movementLabel = __('inventory_movement.movement_badge.stock_out');
                $badgeClass = 'danger';
            }

            // Resolve destination / source title
            $fromTitle = $item->from_title ?: ($item->shop?->name ?: '---');
            $toTitle = $item->to_title;
            if (!$toTitle) {
                if ($isSales) {
                    $toTitle = __('inventory_movement.modal.customer_order');
                } elseif ($isStockIn) {
                    $toTitle = $item->shop?->name ?: __('inventory_movement.modal.warehouse_shop');
                } elseif ($item->type === 'stock_type') {
                    $toTitle = __('inventory_movement.modal.adjustment_internal');
                } else {
                    $toTitle = '---';
                }
            }

            // Requested by title
            $requesterName = $item->request_by_title;
            if (!$requesterName) {
                if ($item->user) {
                    $requesterName = $item->user->name;
                } elseif ($item->barber) {
                    $requesterName = $item->barber->name;
                } else {
                    $requesterName = __('inventory_movement.modal.system');
                }
            }

            return [
                'id' => $item->id,
                'created_at' => $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d H:i:s') : '---',
                'created_at_formatted' => $item->created_at ? Carbon::parse($item->created_at)->format('d M Y, h:i A') : '---',
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?: ($item->product_title ?: 'Unknown Product'),
                'product_image' => $item->product?->image_url ?: null,
                'category_name' => $item->product?->category?->name ?: ($item->category_title ?: 'General'),
                'uom_name' => $item->product?->uom?->name ?: ($item->uom_title ?: 'Unit'),
                'shop_name' => $item->shop?->name ?: ($item->shop_title ?: '---'),
                'status' => $item->status,
                'movement_type' => $isSales ? 'sales' : ($isTransfer ? 'transfer' : ($isStockIn ? 'stock_in' : 'internal_out')),
                'movement_label' => $movementLabel,
                'badge_class' => $badgeClass,
                'from_title' => $fromTitle,
                'to_title' => $toTitle,
                'qty' => (int) ($item->qty ?: 0),
                'stock_in' => (int) ($item->stock_in ?: 0),
                'stock_out' => (int) ($item->stock_out ?: 0),
                'current_stock' => (int) ($item->current_stock ?: 0),
                'request_by_name' => $requesterName,
                'remark' => $item->remark ?: ($item->transfer_type ? 'Transfer Type: ' . $item->transfer_type : '---'),
            ];
        });

        $totalIn = $formatted->where('status', 'stock_in')->sum('qty');
        $totalOut = $formatted->where('status', 'stock_out')->sum('qty');
        $totalSales = $formatted->where('movement_type', 'sales')->sum('qty');
        $totalInternalOut = $formatted->where('movement_type', 'internal_out')->sum('qty');
        $totalTransfer = $formatted->where('movement_type', 'transfer')->sum('qty');
        $netMovement = $totalIn - $totalOut;

        $isDailyPeriod = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $period);
        $periodLabel = '';
        if ($isDailyPeriod) {
            $parsedDate = Carbon::parse($period);
            if (app()->getLocale() === 'km') {
                $dayKey = strtolower($parsedDate->format('D'));
                $periodLabel = 'ថ្ងៃ' . __('inventory_movement.days.' . $dayKey) . ' ទី' . $parsedDate->format('d') . ' ' . __('inventory_movement.months.' . $parsedDate->month) . ' ឆ្នាំ' . $parsedDate->format('Y');
            } else {
                $periodLabel = $parsedDate->format('l, d F Y');
            }
        } else {
            $parsedDate = Carbon::createFromFormat('Y-m', $period);
            if (app()->getLocale() === 'km') {
                $periodLabel = __('inventory_movement.months.' . $parsedDate->month) . ' ឆ្នាំ' . $parsedDate->format('Y');
            } else {
                $periodLabel = $parsedDate->format('F Y');
            }
        }

        return response()->json([
            'status' => 'success',
            'period' => $period,
            'period_label' => $periodLabel,
            'count' => $formatted->count(),
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'total_sales' => $totalSales,
            'total_internal_out' => $totalInternalOut,
            'total_transfer' => $totalTransfer,
            'net_movement' => $netMovement,
            'movements' => $formatted,
        ]);
    }

    /**
     * Build filtered base query on StockHistory
     */
    private function buildFilteredMovementQuery(Request $req, $fromDate, $toDate)
    {
        $query = StockHistory::query()
            ->with([
                'product' => function ($p) {
                    $p->with(['category:id,name', 'uom:id,name']);
                },
                'shop:id,name',
                'user:id,name',
                'barber:id,name',
            ]);

        if ($fromDate && $toDate) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $this->applySecondaryFilters($query, $req);

        return $query;
    }

    /**
     * Apply secondary filters (shop, category, product, movement type, staff, search)
     */
    private function applySecondaryFilters($query, Request $req)
    {
        // Shop / Branch filter
        if ($req->filled('shop_id')) {
            $shopId = $req->shop_id;
            $query->where(function ($q) use ($shopId) {
                $q->where('shop_id', $shopId)
                    ->orWhere(function ($q2) use ($shopId) {
                        $q2->where('status', 'stock_transfer')
                            ->where('to_id', $shopId);
                    });
            });
        }

        // Product Category filter
        if ($req->filled('category_id')) {
            $categoryId = $req->category_id;
            $query->whereHas('product', function ($p) use ($categoryId) {
                $p->where('category_id', $categoryId);
            });
        }

        // Specific Product filter
        if ($req->filled('product_id')) {
            $query->where('product_id', $req->product_id);
        }

        // Movement Status / Type filter
        if ($req->filled('movement_type') && $req->movement_type !== 'all') {
            $mType = $req->movement_type;
            if ($mType === 'stock_in') {
                $query->where('status', 'stock_in');
            } elseif ($mType === 'stock_out') {
                $query->where('status', 'stock_out');
            } elseif ($mType === 'sales') {
                $query->where('status', 'stock_out')
                    ->where(function ($q) {
                        $q->where('transfer_type', 'booking')
                            ->orWhere('type', 'customer');
                    });
            } elseif ($mType === 'internal_out') {
                $query->where('status', 'stock_out')
                    ->where(function ($q) {
                        $q->whereNull('transfer_type')
                            ->orWhere('transfer_type', '!=', 'booking');
                    })
                    ->where(function ($q) {
                        $q->whereNull('type')
                            ->orWhere('type', '!=', 'customer');
                    });
            } elseif ($mType === 'stock_transfer') {
                $query->where('status', 'stock_transfer');
            }
        }

        // Requester / Staff filter
        if ($req->filled('request_by')) {
            $query->where('request_by', $req->request_by);
        }

        // Keyword Search
        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('shop', function ($s) use ($search) {
                    $s->where('name', 'like', "%{$search}%");
                })
                ->orWhere('remark', 'like', "%{$search}%")
                ->orWhere('to_id', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Calculate comprehensive summary metrics
     */
    private function calculateSummaryMetrics($query, $fromDate, $toDate)
    {
        $items = $query->get();

        $totalTransactions = $items->count();
        $totalStockInQty = 0;
        $totalStockInCount = 0;
        $totalStockOutQty = 0;
        $totalStockOutCount = 0;
        $totalSalesQty = 0;
        $totalSalesCount = 0;
        $totalInternalOutQty = 0;
        $totalInternalOutCount = 0;
        $totalTransferQty = 0;
        $totalTransferCount = 0;

        $productCounts = [];

        foreach ($items as $item) {
            $qty = (int) ($item->qty ?: 0);
            $status = $item->status;
            $isSales = ($status === 'stock_out') && ($item->transfer_type === 'booking' || $item->type === 'customer');
            $isTransfer = ($status === 'stock_transfer');
            $isStockIn = ($status === 'stock_in');
            $isInternalOut = ($status === 'stock_out') && !$isSales;

            if ($item->product_id) {
                $productCounts[$item->product_id] = ($productCounts[$item->product_id] ?? 0) + $qty;
            }

            if ($isStockIn) {
                $totalStockInQty += $qty;
                $totalStockInCount++;
            } elseif ($isTransfer) {
                $totalTransferQty += $qty;
                $totalTransferCount++;
            } elseif ($status === 'stock_out') {
                $totalStockOutQty += $qty;
                $totalStockOutCount++;

                if ($isSales) {
                    $totalSalesQty += $qty;
                    $totalSalesCount++;
                } else {
                    $totalInternalOutQty += $qty;
                    $totalInternalOutCount++;
                }
            }
        }

        $netMovement = $totalStockInQty - $totalStockOutQty;
        $distinctProductsCount = count($productCounts);

        // Find most moved product
        $mostMovedProductId = null;
        $mostMovedProductQty = 0;
        if (!empty($productCounts)) {
            arsort($productCounts);
            $mostMovedProductId = array_key_first($productCounts);
            $mostMovedProductQty = $productCounts[$mostMovedProductId];
        }

        $mostMovedProduct = $mostMovedProductId ? Product::find($mostMovedProductId)?->name : 'N/A';

        return [
            'total_transactions' => $totalTransactions,
            'total_stock_in_qty' => $totalStockInQty,
            'total_stock_in_count' => $totalStockInCount,
            'total_stock_out_qty' => $totalStockOutQty,
            'total_stock_out_count' => $totalStockOutCount,
            'total_sales_qty' => $totalSalesQty,
            'total_sales_count' => $totalSalesCount,
            'total_internal_out_qty' => $totalInternalOutQty,
            'total_internal_out_count' => $totalInternalOutCount,
            'total_transfer_qty' => $totalTransferQty,
            'total_transfer_count' => $totalTransferCount,
            'net_movement' => $netMovement,
            'distinct_products_count' => $distinctProductsCount,
            'most_moved_product' => $mostMovedProduct,
            'most_moved_product_qty' => $mostMovedProductQty,
        ];
    }

    /**
     * Aggregate daily movements grouped by DATE(created_at)
     */
    private function aggregateDailyMovements($query)
    {
        $items = $query->orderBy('created_at', 'desc')->get();

        // Group by Date YYYY-MM-DD
        $grouped = $items->groupBy(function ($item) {
            return $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d') : 'unknown';
        });

        $rows = [];
        $index = 1;

        foreach ($grouped as $dateKey => $dayItems) {
            if ($dateKey === 'unknown') continue;

            $dateObj = Carbon::parse($dateKey);
            $stockInQty = 0;
            $stockOutQty = 0;
            $salesOutQty = 0;
            $internalOutQty = 0;
            $transferQty = 0;
            $dayProductCounts = [];

            foreach ($dayItems as $item) {
                $qty = (int) ($item->qty ?: 0);
                $status = $item->status;
                $isSales = ($status === 'stock_out') && ($item->transfer_type === 'booking' || $item->type === 'customer');
                $isTransfer = ($status === 'stock_transfer');
                $isStockIn = ($status === 'stock_in');

                if ($item->product_id) {
                    $dayProductCounts[$item->product_id] = ($dayProductCounts[$item->product_id] ?? 0) + $qty;
                }

                if ($isStockIn) {
                    $stockInQty += $qty;
                } elseif ($isTransfer) {
                    $transferQty += $qty;
                } elseif ($status === 'stock_out') {
                    $stockOutQty += $qty;
                    if ($isSales) {
                        $salesOutQty += $qty;
                    } else {
                        $internalOutQty += $qty;
                    }
                }
            }

            $topProductName = '---';
            if (!empty($dayProductCounts)) {
                arsort($dayProductCounts);
                $topId = array_key_first($dayProductCounts);
                $p = Product::find($topId);
                if ($p) {
                    $topProductName = $p->name . ' (' . $dayProductCounts[$topId] . ')';
                }
            }

            $netMovement = $stockInQty - $stockOutQty;

            $rows[] = (object) [
                'index' => $index++,
                'date' => $dateKey,
                'date_formatted' => $dateObj->format('d M Y'),
                'day_name' => $dateObj->format('l'),
                'stock_in_qty' => $stockInQty,
                'stock_out_qty' => $stockOutQty,
                'sales_out_qty' => $salesOutQty,
                'internal_out_qty' => $internalOutQty,
                'transfer_qty' => $transferQty,
                'net_movement' => $netMovement,
                'total_transactions' => $dayItems->count(),
                'top_product' => $topProductName,
            ];
        }

        return $rows;
    }

    /**
     * Aggregate monthly movements grouped by DATE_FORMAT(created_at, '%Y-%m')
     */
    private function aggregateMonthlyMovements($query)
    {
        $items = $query->orderBy('created_at', 'desc')->get();

        // Group by Month YYYY-MM
        $grouped = $items->groupBy(function ($item) {
            return $item->created_at ? Carbon::parse($item->created_at)->format('Y-m') : 'unknown';
        });

        $rows = [];
        $index = 1;

        foreach ($grouped as $monthKey => $monthItems) {
            if ($monthKey === 'unknown') continue;

            $dateObj = Carbon::createFromFormat('Y-m', $monthKey);
            $stockInQty = 0;
            $stockOutQty = 0;
            $salesOutQty = 0;
            $internalOutQty = 0;
            $transferQty = 0;
            $monthProductCounts = [];
            $shopCounts = [];
            $activeDays = [];

            foreach ($monthItems as $item) {
                $qty = (int) ($item->qty ?: 0);
                $status = $item->status;
                $isSales = ($status === 'stock_out') && ($item->transfer_type === 'booking' || $item->type === 'customer');
                $isTransfer = ($status === 'stock_transfer');
                $isStockIn = ($status === 'stock_in');

                if ($item->created_at) {
                    $activeDays[Carbon::parse($item->created_at)->format('Y-m-d')] = true;
                }

                if ($item->shop_id) {
                    $shopCounts[$item->shop_id] = ($shopCounts[$item->shop_id] ?? 0) + $qty;
                }

                if ($item->product_id) {
                    $monthProductCounts[$item->product_id] = ($monthProductCounts[$item->product_id] ?? 0) + $qty;
                }

                if ($isStockIn) {
                    $stockInQty += $qty;
                } elseif ($isTransfer) {
                    $transferQty += $qty;
                } elseif ($status === 'stock_out') {
                    $stockOutQty += $qty;
                    if ($isSales) {
                        $salesOutQty += $qty;
                    } else {
                        $internalOutQty += $qty;
                    }
                }
            }

            $topProductName = '---';
            if (!empty($monthProductCounts)) {
                arsort($monthProductCounts);
                $topPId = array_key_first($monthProductCounts);
                $p = Product::find($topPId);
                if ($p) {
                    $topProductName = $p->name . ' (' . $monthProductCounts[$topPId] . ')';
                }
            }

            $topShopName = '---';
            if (!empty($shopCounts)) {
                arsort($shopCounts);
                $topSId = array_key_first($shopCounts);
                $s = Shop::find($topSId);
                if ($s) {
                    $topShopName = $s->name;
                }
            }

            $netMovement = $stockInQty - $stockOutQty;

            $rows[] = (object) [
                'index' => $index++,
                'month_key' => $monthKey,
                'month_name' => $dateObj->format('F Y'),
                'year' => $dateObj->format('Y'),
                'active_days' => count($activeDays),
                'stock_in_qty' => $stockInQty,
                'stock_out_qty' => $stockOutQty,
                'sales_out_qty' => $salesOutQty,
                'internal_out_qty' => $internalOutQty,
                'transfer_qty' => $transferQty,
                'net_movement' => $netMovement,
                'total_transactions' => $monthItems->count(),
                'top_product' => $topProductName,
                'top_shop_name' => $topShopName,
            ];
        }

        return $rows;
    }

    /**
     * Resolve Daily Date Range from request
     */
    private function resolveDailyDateRange(Request $req)
    {
        $preset = $req->get('preset');

        if ($preset === 'today') {
            return [
                'from' => Carbon::today()->format('Y-m-d'),
                'to' => Carbon::today()->format('Y-m-d'),
            ];
        }

        if ($preset === 'yesterday') {
            return [
                'from' => Carbon::yesterday()->format('Y-m-d'),
                'to' => Carbon::yesterday()->format('Y-m-d'),
            ];
        }

        if ($preset === '7days') {
            return [
                'from' => Carbon::today()->subDays(6)->format('Y-m-d'),
                'to' => Carbon::today()->format('Y-m-d'),
            ];
        }

        if ($preset === '30days') {
            return [
                'from' => Carbon::today()->subDays(29)->format('Y-m-d'),
                'to' => Carbon::today()->format('Y-m-d'),
            ];
        }

        if ($preset === 'this_month') {
            return [
                'from' => Carbon::today()->startOfMonth()->format('Y-m-d'),
                'to' => Carbon::today()->format('Y-m-d'),
            ];
        }

        if ($preset === 'last_month') {
            return [
                'from' => Carbon::today()->subMonth()->startOfMonth()->format('Y-m-d'),
                'to' => Carbon::today()->subMonth()->endOfMonth()->format('Y-m-d'),
            ];
        }

        // Custom range or default to Current Month
        $from = $req->get('from_date');
        $to = $req->get('to_date');

        if (!$from || !$to) {
            $from = Carbon::today()->startOfMonth()->format('Y-m-d');
            $to = Carbon::today()->format('Y-m-d');
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * Resolve Monthly Date Range from request
     */
    private function resolveMonthlyDateRange(Request $req)
    {
        $currentYear = Carbon::now()->year;
        $selectedYear = (int) $req->get('year', $currentYear);
        $fromMonth = (int) $req->get('from_month', 1);
        $toMonth = (int) $req->get('to_month', Carbon::now()->month);

        if ($fromMonth < 1 || $fromMonth > 12) $fromMonth = 1;
        if ($toMonth < 1 || $toMonth > 12) $toMonth = 12;
        if ($fromMonth > $toMonth) {
            $temp = $fromMonth;
            $fromMonth = $toMonth;
            $toMonth = $temp;
        }

        $startDate = Carbon::create($selectedYear, $fromMonth, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::create($selectedYear, $toMonth, 1)->endOfMonth()->format('Y-m-d');

        return [
            'year' => $selectedYear,
            'from_month' => $fromMonth,
            'to_month' => $toMonth,
            'from' => $startDate,
            'to' => $endDate,
        ];
    }

    /**
     * Extract sanitized filter array from request
     */
    private function extractFilters(Request $req, $mode = 'daily')
    {
        return [
            'shop_id' => $req->get('shop_id', ''),
            'category_id' => $req->get('category_id', ''),
            'product_id' => $req->get('product_id', ''),
            'movement_type' => $req->get('movement_type', 'all'),
            'request_by' => $req->get('request_by', ''),
            'search' => $req->get('search', ''),
            'preset' => $req->get('preset', ''),
        ];
    }

    /**
     * Get lookup options for filter dropdowns
     */
    private function getFilterOptions()
    {
        return [
            'shops' => Shop::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'category_id']),
            'stockTypes' => StockType::orderBy('name')->get(['id', 'key', 'name']),
            'staffUsers' => User::orderBy('name')->get(['id', 'name', 'phone']),
            'movementTypes' => [
                'all' => __('inventory_movement.filter.movement_types.all'),
                'stock_in' => __('inventory_movement.filter.movement_types.stock_in'),
                'stock_out' => __('inventory_movement.filter.movement_types.stock_out'),
                'sales' => __('inventory_movement.filter.movement_types.sales'),
                'internal_out' => __('inventory_movement.filter.movement_types.internal_out'),
                'stock_transfer' => __('inventory_movement.filter.movement_types.stock_transfer'),
            ],
        ];
    }

    /**
     * Get available years with activity for dropdown
     */
    private function getAvailableYears()
    {
        $currentYear = (int) Carbon::now()->year;
        $dbYears = StockHistory::select(DB::raw('YEAR(created_at) as yr'))
            ->whereNotNull('created_at')
            ->groupBy('yr')
            ->pluck('yr')
            ->map(fn($y) => (int) $y)
            ->toArray();

        $years = array_unique(array_merge([$currentYear, $currentYear - 1, $currentYear - 2], $dbYears));
        rsort($years);

        return $years;
    }
}
