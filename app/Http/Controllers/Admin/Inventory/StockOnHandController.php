<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\StockOnHand;
use Illuminate\Http\Request;

class StockOnHandController extends Controller
{
    protected $layout = 'admin::pages.inventoryManagement.stockOnHand.';
    private $routeName = 'stock-on-hand';

    public function __construct()
    {
        $this->middleware('permission:stock-on-hand-view', ['only' => ['index', 'onView', 'report', 'export']]);
    }

    public function index(Request $req)
    {
        $data['status'] = 1;
        $data['routeName'] = $this->routeName;
        $data['shop'] = $req->shop_id ? Shop::find($req->shop_id) : null;
        $exportRoute = 'admin-' . $this->routeName . '-export';
        $data['exportUrl'] = \Illuminate\Support\Facades\Route::has($exportRoute)
            ? route($exportRoute, $req->query())
            : url('admin/' . $this->routeName . '/export' . ($req->query() ? '?' . http_build_query($req->query()) : ''));
        $data['data'] = $this->queryData('index');

        return view($this->layout . 'index', $data);
    }

    public function onView(Request $req)
    {
        $stockOnHand = StockOnHand::with([
            'shop',
            'product.category',
            'product.uom',
            'user',
            'barber',
        ])->find($req->id);

        if (!$stockOnHand) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        return view($this->layout . 'view', [
            'id' => $stockOnHand->id,
            'data' => $stockOnHand,
            'routeName' => $this->routeName,
        ]);
    }

    public function onFind($product_id = null, $shop_id = null)
    {
        try {
            $data = StockOnHand::where('product_id', $product_id)->where('shop_id', $shop_id)->first();
            return response()->json($data);
        } catch (\Exception $error) {
            return response()->json(null);
        }
    }

    public function report()
    {
        $data = $this->queryData('report');
        return response()->json($data);
    }

    public function export(Request $req)
    {
        $items = $this->queryData('report');
        $fileName = 'stock_on_hand_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $columns = [
            __('global.table.no'),
            __('stock_on_hand.table.product'),
            __('stock_on_hand.table.category'),
            __('stock_on_hand.table.uom'),
            __('stock_on_hand.table.current_stock'),
            __('stock_on_hand.table.date'),
            __('stock_on_hand.table.shop'),
            __('stock_on_hand.table.requested_by'),
        ];

        $callback = function () use ($items, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($items as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->product_title ?? ($item->product->name ?? '---'),
                    $item->category_title ?? ($item->category->name ?? '---'),
                    $item->uom_title ?? ($item->uom->name ?? '---'),
                    $item->current_stock ?? 0,
                    $item->created_date ?? '',
                    $item->shop_title ?? ($item->shop->name ?? '---'),
                    $item->request_by_title ?? ($item->user->name ?? '---'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function queryData($type)
    {
        $query = StockOnHand::with([
            'shop',
            'product.category',
            'product.uom',
            'user',
            'barber',
        ]);

        $getData = $query
            ->when(request('search'), function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('product', function ($product) {
                        $product->where('name', 'like', '%' . request('search') . '%');
                    })->orWhereHas('shop', function ($shop) {
                        $shop->where('name', 'like', '%' . request('search') . '%');
                    });
                });
            })
            ->when(request('shop_id'), function ($query) {
                $query->where('shop_id', request('shop_id'));
            })
            ->when(request('date'), function ($query) {
                $query->whereDate('created_at', request('date'));
            })
            ->orderBy('id', 'desc');

        $data = $type === 'report'
            ? $getData->get()
            : $getData->paginate(50)->appends(request()->query());

        foreach ($data as $item) {
            $item->category = $item->product ? $item->product->category : null;
            $item->uom = $item->product ? $item->product->uom : null;
        }

        return $data;
    }
}
