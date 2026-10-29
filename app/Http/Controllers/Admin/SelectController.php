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
use App\Models\Category;
use App\Models\UOM;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

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
        $data = Customer::forUser()->where('status', 1)->orderBy('id', 'asc')->limit(50)->get();
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
                    $product->where(function ($p) use ($req) {
                        $p->where('name', 'LIKE', '%' . $req->search . '%')
                          ->orWhere('code', 'LIKE', '%' . $req->search . '%');
                    });
                }
                $product->where('status', 1);
            })
            : ShopService::where('shop_id', $req->shop_id)->with(['service'])->whereHas('service', function ($product) use ($req) {
                if ($req->search) {
                    $product->where('name', 'LIKE', '%' . $req->search . '%');
                }
                $product->where('status', 1);
            });

        // The order picker opts into pagination; existing catalog callers keep their response.
        $perPage = (int) ($req->input('per_page') ?: 12);
        $page = $req->has('page')
            ? $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', (int) $req->input('page', 1))
            : null;
        $data = $page ? $page->getCollection() : $query->orderBy('created_at', 'desc')->take(12)->get();

        $currentDate = Carbon::now()->toDate();
        $numberCurrentDate = (int)Carbon::parse($currentDate)->format('Ymd');
        if (count($data) > 0) {
            if ($req->type == "product" || !$req->has('type')) {
                $productIds = $data->pluck('product_id')->filter()->unique();
                $stocks = Schema::hasTable('stock_on_hands')
                    ? StockOnHand::where('shop_id', $req->shop_id)
                        ->whereIn('product_id', $productIds)
                        ->pluck('current_stock', 'product_id')
                    : [];

                foreach ($data as $val) {
                    $val->current_stock = (int) ($stocks[$val->product_id] ?? 0);
                }
            }

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
            $response = ['data' => $data, 'message' => 200];
            if ($page) {
                $response['has_more'] = $page->hasMorePages();
                $response['total'] = $page->total();
                $response['last_page'] = $page->lastPage();
                $response['current_page'] = $page->currentPage();
                $response['from'] = $page->firstItem() ?? 0;
                $response['to'] = $page->lastItem() ?? 0;
                $response['per_page'] = $page->perPage();
            }
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function selectCustomer(Request $req)
    {
        $search = trim((string) $req->search);
        $numericId = (ctype_digit($search) || is_numeric($search)) ? (int) ltrim($search, '0') : null;

        $data = Customer::forUser()->where('status', 1)->where(function (Builder $q) use ($search, $numericId) {
            if ($search !== '') {
                $q->where(function (Builder $sub) use ($search, $numericId) {
                    $sub->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $search . '%')
                        ->orWhere('code', 'LIKE', '%' . $search . '%');
                    if ($numericId && $numericId > 0) {
                        $sub->orWhere('id', $numericId);
                    }
                });
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
        $data = Shop::where('status', 1)
            ->when($req->search, function ($q) use ($req) {
                $q->where(function ($sub) use ($req) {
                    $sub->where('name', 'LIKE', '%' . $req->search . '%')
                        ->orWhere('nick_name', 'LIKE', '%' . $req->search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $req->search . '%');
                });
            })
            ->orderBy('id', 'asc')
            ->take(50)
            ->get();

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
        $shopID = $req->shop_id;
        if (!$shopID) {
            return response()->json(['data' => [], 'message' => 200]);
        }

        $productID = $req->product_id ? json_decode($req->product_id) : [];

        $data = ShopProduct::where('status', 1)
            ->where('shop_id', $shopID)
            ->whereHas('product', function ($pro) use ($req, $productID) {
                $pro->where('status', 1);
                if (request('search')) {
                    $pro->where('name', 'LIKE', '%' . request('search') . '%');
                }
                if (is_array($productID) && count($productID) > 0) {
                    $pro->whereNotIn('id', $productID);
                }
            })
            ->with(['product.uom', 'product.category'])
            ->orderBy('id', 'asc')
            ->take($req->limit ? (int) $req->limit : 50)
            ->get()
            ->unique('product_id')
            ->values();

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
                $q->where(function ($sub) use ($req) {
                    $sub->where('name', 'LIKE', '%' . $req->search . '%')
                        ->orWhere('code', 'LIKE', '%' . $req->search . '%');
                });
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
    public function SelectCategorySearch(Request $req)
    {
        $data = Category::where('status', 1)->where(function (Builder $q) use ($req) {
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('created_at', 'asc')->take(50)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
    public function SelectUOMSearch(Request $req)
    {
        $data = UOM::where('status', 1)->where(function (Builder $q) use ($req) {
            if ($req->search) {
                $q->where('name', 'LIKE', '%' . $req->search . '%');
            }
        })->orderBy('created_at', 'asc')->take(50)->get();

        try {
            return response()->json(['data' => $data, 'message' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => 'error'
            ]);
        }
    }
}
