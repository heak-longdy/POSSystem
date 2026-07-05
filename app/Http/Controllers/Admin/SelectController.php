<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Customer;
use App\Models\PlacementType;
use App\Models\Position;
use App\Models\Product;
use App\Models\Sector;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\ShopService;
use App\Models\StockOnHand;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class SelectController extends Controller
{
    public function index()
    {
        $data = Product::where('status', 1)->orderBy('id', 'asc')->get();
        return response()->json($data);
    }
    public function service()
    {
        $data = Service::where('status', 1)->orderBy('id', 'asc')->get();
        return response()->json($data);
    }
    public function customer()
    {
        $data = Customer::where('status', 1)->orderBy('id', 'asc')->limit(50)->get();
        return response()->json($data);
    }
    public function shop()
    {
        $data = Shop::where('status', 1)->orderBy('id', 'asc')->get();
        return response()->json($data);
    }

    public function selectProduct(Request $req)
    {
        $query = $req->type == "product" ?
            ShopProduct::where('shop_id', $req->shop_id)->with(['product'])->whereHas('product', function ($product) use ($req) {
                if ($req->search) {
                    $product->where('name', 'LIKE', '%' . $req->search . '%');
                    $product->where('status', 1);
                }
            })
            : ShopService::where('shop_id', $req->shop_id)->with(['service'])->whereHas('service', function ($product) use ($req) {
                if ($req->search) {
                    $product->where('name', 'LIKE', '%' . $req->search . '%');
                    $product->where('status', 1);
                }
            });

        $data = $query->orderBy('created_at', 'desc')->take(12)->get();

        $currentDate = Carbon::now()->toDate();
        $numberCurrentDate = (int)Carbon::parse($currentDate)->format('Ymd');
        if (count($data) > 0) {
            foreach ($data as $val) {
                $from_date = $val->from_date ? (int)Carbon::parse($val->from_date)->format('Ymd') : null;
                $to_date = $val->to_date ? (int)Carbon::parse($val->to_date)->format('Ymd') : null;
                if ($from_date && $to_date) {
                    if ($from_date > $numberCurrentDate) {
                        $val->discount = 0;
                    } else if ($numberCurrentDate > $to_date) {
                        $val->discount = 0;
                    }
                }
            }
        }
        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function selectCustomer(Request $req)
    {
        $data = Customer::where('status', 1)->where(function (Builder $q) use ($req) {
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('created_at', 'desc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function selectSupplier(Request $req)
    {
        $data = Supplier::where('status', 1)->where(function (Builder $q) use ($req) {
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('ordering', 'asc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function brand()
    {
        $data = Brand::where('status', 1)->orderBy('id', 'asc')->get();
        return response()->json($data);
    }
    public function stockSelectProduct(Request $req)
    {
        $productID = json_decode($req->product_id);
        $data = Product::with("stockIn")->where(function ($q) use ($req, $productID) {
            $q->where('status', 1);
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
            }
            if (count($productID) > 0) {
                $q->whereNotIn('id', $productID);
            }
        })->orderBy('id', 'asc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function stockSelectShop(Request $req)
    {
        $data = Shop::where(function ($q) use ($req) {
            $q->where('status', 1);
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
                $q->orWhere('nick_name', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('id', 'asc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function stockSelectShopNotInID(Request $req)
    {
        $shopID = json_decode($req->shop_id);
        $data = Shop::where(function ($q) use ($req, $shopID) {
            $q->where('status', 1);
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
            }
            if (count($shopID) > 0) {
                $q->whereNotIn('id', $shopID);
            }
        })->orderBy('id', 'asc')->take(12)->get();
        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function selectShopProduct(Request $req)
    {
        $productID = $req->product_id ? json_decode($req->product_id) : [];
        $shopID = $req->shop_id;
        $data = ShopProduct::where(function ($q) use ($req, $shopID, $productID) {
            $q->where('status', 1);
            $q->where('shop_id', $shopID);
            $q->whereHas('product', function ($pro) use ($productID) {
                if (request('search')) {
                    $pro->where('name', 'LIKE', '%' . request('search') . '%');
                }
                if (count($productID) > 0) {
                    $pro->whereNotIn('id', $productID);
                }
            });
        })->orderBy('id', 'asc')->take(12)->get();
        if (count($data) > 0) {
            foreach ($data as $val) {
                $val->product = Product::with(["uom", "category"])->find($val->product_id);
            }
        }
        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function findShopProduct(Request $req)
    {
        $stockOnHand = StockOnHand::where('shop_id', $req->shop_id)->where('product_id', $req->product_id)->first();
        return response()->json($stockOnHand);
    }
    public function SelectShop(Request $req)
    {
        $data = Shop::where(function ($q) use ($req) {
            $q->where('status', 1);
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
                $q->orWhere('nick_name', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('id', 'asc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function SelectBarber(Request $req)
    {
        $data = Barber::where(function ($q) use ($req) {
            $q->where('status', 1);
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
                $q->orWhere('number_id', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('id', 'asc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function SelectProductSearch(Request $req)
    {
        $data = Product::where(function ($q) use ($req) {
            $q->where('status', 1);
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('id', 'asc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function SelectServiceSearch(Request $req)
    {
        $data = Service::where(function ($q) use ($req) {
            $q->where('status', 1);
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('id', 'asc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }

    public function productInShop(Request $req)
    {

        $shop_id = $req->shop_id ? json_decode($req->shop_id) : [];
        $data = ShopProduct::with('product')->where(function ($q) use ($req, $shop_id) {
            $q->where('status', 1);
            $q->whereIn('shop_id', $shop_id);
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('id', 'asc')->take(12)->get();
        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function SelectPositionSearch(Request $req)
    {
        $data = Position::where('status', 1)->where(function (Builder $q) use ($req) {
            if ($req->search) {
                $q->where('title', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('created_at', 'desc')->take(12)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }

    public function SelectSectorSearch(Request $req)
    {
        $data = Sector::where('status', 1)->where(function (Builder $q) use ($req) {
            if ($req->position_id) {
                $q->where('position_id', $req->position_id);
            }
            if ($req->search) {
                $q->where('title', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('created_at', 'desc')->take(50)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function SelectPlacementTypeSearch()
    {
        $data = PlacementType::where('status', 1)->orderBy('created_at', 'desc')->take(50)->get();
        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
}
