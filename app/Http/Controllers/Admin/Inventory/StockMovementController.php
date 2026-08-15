<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\StockHistory;
use App\Models\StockType;
use App\Models\Supplier;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    protected $layout = 'admin::pages.inventoryManagement.stockMovement.';
    private $routeName = 'stock-movement';

    public function __construct()
    {
        $this->middleware('permission:stock-movement-view', ['only' => ['index', 'onView', 'report']]);
    }

    public function index(Request $req)
    {
        $data['status'] = 1;
        $data['routeName'] = $this->routeName;
        $data['shop'] = $req->shop_id ? Shop::find($req->shop_id) : null;
        $data['data'] = $this->queryData('index');

        return view($this->layout . 'index', $data);
    }

    public function onView(Request $req)
    {
        $stockMovement = StockHistory::with([
            'shop',
            'product.category',
            'product.uom',
            'user',
            'barber',
        ])->find($req->id);

        if (!$stockMovement) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $this->setDestination($stockMovement);

        return view($this->layout . 'view', [
            'id' => $stockMovement->id,
            'data' => $stockMovement,
            'routeName' => $this->routeName,
        ]);
    }

    public function report(Request $req)
    {
        $data = $this->queryData('report');
        return response()->json($data);
    }

    public function queryData($type)
    {
        $query = StockHistory::with([
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
            ->when(request('from_date') && !request('to_date'), function ($query) {
                $query->whereDate('created_at', request('from_date'));
            })
            ->when(request('from_date') && request('to_date'), function ($query) {
                $query->whereDate('created_at', '>=', request('from_date'))
                    ->whereDate('created_at', '<=', request('to_date'));
            })
            ->when(request('status_type'), function ($query) {
                $query->where('status', request('status_type'));
            })
            ->orderBy('id', 'desc');

        $data = $type === 'report'
            ? $getData->get()
            : $getData->paginate(50)->appends(request()->query());

        foreach ($data as $item) {
            $this->setDestination($item);
            $item->category = $item->product ? $item->product->category : null;
            $item->uom = $item->product ? $item->product->uom : null;
        }

        return $data;
    }

    private function setDestination($item)
    {
        if ($item->type === 'customer') {
            $item->data_to = Customer::find($item->to_id);
        } elseif ($item->type === 'shop' && $item->status !== 'stock_in') {
            $item->data_to = Shop::find($item->to_id);
        } elseif ($item->type === 'shop' && $item->status === 'stock_in') {
            $item->data_to = Supplier::find($item->to_id);
        } elseif ($item->type === 'stock_type') {
            $item->data_to = StockType::where('key', $item->to_id)->first();
        }
    }
}
