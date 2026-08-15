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
        $this->middleware('permission:stock-on-hand-view', ['only' => ['index', 'onView', 'report']]);
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
