<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Services\OrderPricing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConsolidatedProductQuantityReportController extends Controller
{
    private $routeName = 'report-consolidated-product-quantity';

    public function __construct()
    {
        $this->middleware('permission:report-consolidated-product-quantity-view');
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters);
        $amounts = $this->totalAmounts(clone $query);
        $rows = $this->groupedQuery(clone $query)->paginate(50)->withQueryString();
        $rows->getCollection()->each(function ($row) use ($amounts) {
            $row->total_amount = ($amounts[$row->product_id] ?? 0) / 100;
        });
        $summary = $this->summary(clone $query);
        $summary->total_amount = array_sum($amounts) / 100;

        return view('admin::pages.report.consolidatedProductQuantity.index', [
            'routeName' => $this->routeName,
            'filters' => $filters,
            'summary' => $summary,
            'rows' => $rows,
            'shops' => Shop::orderBy('name')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'products' => Product::withTrashed()->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function report(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters);
        $amounts = $this->totalAmounts(clone $query);
        $rows = $this->groupedQuery(clone $query)->get()->each(function ($row) use ($amounts) {
            $row->total_amount = ($amounts[$row->product_id] ?? 0) / 100;
        });
        $summary = $this->summary(clone $query);
        $summary->total_amount = array_sum($amounts) / 100;

        return response()->json([
            'grouping' => 'product',
            'filters' => $filters,
            'summary' => $summary,
            'rows' => $rows,
        ]);
    }

    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d'],
            'shop_id' => ['nullable', 'integer', 'min:1'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'product_id' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        // Match the Sales report's default and partially supplied date ranges.
        $now = Carbon::now();
        $hasDates = !empty($filters['from_date']) || !empty($filters['to_date']);
        $filters['from_date'] = $filters['from_date'] ?? ($hasDates ? $now->copy()->startOfMonth() : $now)->format('Y-m-d');
        $filters['to_date'] = $filters['to_date'] ?? ($hasDates ? $now->copy()->endOfMonth() : $now)->format('Y-m-d');
        $filters['search'] = trim($filters['search'] ?? '');
        Validator::make($filters, ['to_date' => 'after_or_equal:from_date'])->validate();

        return $filters;
    }

    private function filteredQuery(array $filters)
    {
        $orders = Order::forUser()->select('orders.id', 'orders.shop_id', 'orders.invoice_number')
            ->where('orders.order_date', '>=', Carbon::parse($filters['from_date'])->startOfDay())
            ->where('orders.order_date', '<', Carbon::parse($filters['to_date'])->addDay()->startOfDay())
            ->where(function ($query) {
                $query->whereNull('orders.payment_status')
                    ->orWhereNotIn(DB::raw('LOWER(orders.payment_status)'), ['cancel', 'cancelled', 'canceled']);
            });
        if (!empty($filters['shop_id'])) {
            $orders->where('orders.shop_id', $filters['shop_id']);
        }

        // Aggregate order lines directly: payment joins would multiply quantities.
        // Keep historical product labels even when a product has been archived.
        $query = DB::table('order_details as detail')
            ->joinSub($orders, 'orders', 'orders.id', '=', 'detail.order_id')
            ->leftJoin('products as product', 'product.id', '=', 'detail.product_id')
            ->leftJoin('categories as category', 'category.id', '=', 'product.category_id')
            ->leftJoin('uoms as uom', 'uom.id', '=', 'product.uom_id')
            ->whereNull('detail.deleted_at')
            ->whereNotNull('detail.product_id')
            ->where(function ($query) {
                $query->where('detail.type', 'product')->orWhere(function ($legacy) {
                    $legacy->whereNull('detail.type')->whereNull('detail.service_id');
                });
            });
        foreach (['category_id' => 'product.category_id', 'product_id' => 'detail.product_id'] as $key => $column) {
            if (!empty($filters[$key])) {
                $query->where($column, $filters[$key]);
            }
        }
        if ($filters['search'] !== '') {
            $query->where(function ($query) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $query->where('product.name', 'like', $term)
                    ->orWhere('product.code', 'like', $term)
                    ->orWhere('orders.invoice_number', 'like', $term);
            });
        }

        return $query;
    }

    private function groupedQuery($query)
    {
        return $query->select('detail.product_id', 'product.code as product_code', 'product.name as product_name',
            'category.name as category_name', 'uom.name as uom_name')
            ->selectRaw('COUNT(DISTINCT detail.order_id) as order_count, SUM(COALESCE(detail.qty, 1)) as total_quantity')
            ->groupBy('detail.product_id', 'product.code', 'product.name', 'category.name', 'uom.name')
            ->orderByDesc('total_quantity')->orderBy('product.name')->orderBy('detail.product_id');
    }

    private function summary($query): object
    {
        return $query->selectRaw('COUNT(DISTINCT detail.product_id) as product_count,
            COUNT(DISTINCT detail.order_id) as order_count, COUNT(DISTINCT orders.shop_id) as shop_count,
            COALESCE(SUM(COALESCE(detail.qty, 1)), 0) as total_quantity')->first();
    }

    private function totalAmounts($query): array
    {
        $pricing = app(OrderPricing::class);
        $amounts = [];
        foreach ($query->select('detail.*')->cursor() as $line) {
            $detail = (new OrderDetail())->setRawAttributes((array) $line);
            // Preserve the report's quantity rules, including zero and legacy null qty.
            $qty = (int) ($detail->qty ?? 1);
            if ($detail->pricing_method === OrderPricing::AUTOMATIC) {
                $cents = $pricing->usdCents($detail->unit_price) * $qty;
            } else {
                // Use the same PHP rounding as OrderPricing::line for fractional-cent discounts.
                $cents = (int) round(round($pricing->legacyUnitPrice($detail) * $qty, 2) * 100);
            }
            $amounts[$detail->product_id] = ($amounts[$detail->product_id] ?? 0) + $cents;
        }

        return $amounts;
    }
}
