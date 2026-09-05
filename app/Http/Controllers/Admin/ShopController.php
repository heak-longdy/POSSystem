<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Services\Tools;
use App\Http\Requests\Admin\ShopRequest;
use App\Http\Requests\Admin\ShopProductRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Throwable;

class ShopController extends Controller
{
    protected $layout = 'admin::pages.shop.';
    private $tools;
    private $table = Shop::class;
    private $routeName = "shop";
    public function __construct(Tools $serTool)
    {
        $this->tools = $serTool;
    }
    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        if (!$req->status) {
            return redirect()->route('admin-'.$this->routeName.'-list', 1);
        }
        if ($req->status != 'trash') {
            $query = Shop::where('status', $req->status);
        } else {
            $query = Shop::onlyTrashed();
        }
        $data['data'] = $query->orderBy('id', 'desc')->paginate(50);
        $data['exportUrl'] = route('admin-shop-export');
        return view($this->layout . 'index', $data);
    }
    public function onCreate()
    {
        $data['id'] = "";
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function onEdit(Request $req)
    {
        $data['id'] = $req->id;
        $data['data'] = Shop::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function onProduct($id = "")
    {
        $shop = Shop::findOrFail($id);
        $status = request('status');
        $shopProductQuery = $status == 'trash'
            ? ShopProduct::onlyTrashed()
            : ShopProduct::query();

        $shopProducts = $shopProductQuery->with(['product.category', 'product.uom'])
            ->where('shop_id', $shop->id)
            ->where(function ($query) {
                if (request('search')) {
                    $query->whereHas('product', function ($product) {
                        $product->where('name', 'LIKE', '%' . request('search') . '%');
                    });
                }
            })
            ->where(function ($query) use ($status) {
                if ($status && $status != 'trash') {
                    $query->where('status', $status);
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        $data['id'] = $shop->id;
        $data['shop'] = $shop;
        $data['routeName'] = $this->routeName;
        $data['products'] = $shopProducts;
        $data['status'] = $status;

        return view($this->layout . 'product', $data);
    }
    public function onCreateProduct($id = "")
    {
        $shop = Shop::findOrFail($id);
        $shopProducts = ShopProduct::withTrashed()->where('shop_id', $shop->id)->get();
        $attachedProductIds = $shopProducts->pluck('product_id')->filter()->values();

        $data['id'] = $shop->id;
        $data['shop'] = $shop;
        $data['routeName'] = $this->routeName;
        $data['products'] = collect();
        $data['availableProducts'] = Product::with(['category', 'uom'])
            ->where('status', 1)
            ->when($attachedProductIds->count() > 0, function ($query) use ($attachedProductIds) {
                $query->whereNotIn('id', $attachedProductIds);
            })
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'commission' => $product->commission,
                    'image' => $product->image,
                    'uom' => $product->uom?->name,
                    'category' => $product->category?->name,
                ];
            })
            ->values();

        return view($this->layout . 'product-create', $data);
    }
    public function onEditProduct($shopId = "", $shopProductId = "")
    {
        $shop = Shop::findOrFail($shopId);
        $shopProduct = ShopProduct::with(['product.category', 'product.uom'])
            ->where('shop_id', $shop->id)
            ->findOrFail($shopProductId);

        $data['id'] = $shop->id;
        $data['shop'] = $shop;
        $data['routeName'] = $this->routeName;
        $data['availableProducts'] = collect();
        $data['products'] = collect([
            [
                'id' => $shopProduct->id,
                'product_id' => $shopProduct->product_id,
                'product_name' => $shopProduct->product?->name,
                'product_image' => $shopProduct->product?->image,
                'product_uom' => $shopProduct->product?->uom?->name,
                'product_category' => $shopProduct->product?->category?->name,
                'price' => $shopProduct->price,
                'point' => $shopProduct->point,
                'max_qty' => $shopProduct->max_qty,
                'commission' => $shopProduct->commission,
                'commission_type' => $shopProduct->commission_type,
                'status' => $shopProduct->status,
            ],
        ]);

        return view($this->layout . 'product-create', $data);
    }
    public function Save(ShopRequest $req, $id = "")
    {
        if ($req->input('save_opt') == 'save_new') {
            return $this->tools->onSave($this->table, $req, $id, $this->routeName, 'back');
        }
        return $this->tools->onSave($this->table, $req, $id, $this->routeName);
    }
    public function saveProduct(ShopProductRequest $req, $id = "")
    {
        $shop = Shop::findOrFail($id);
        $validated = $req->validated();

        DB::beginTransaction();
        try {
            foreach ($validated['products'] as $product) {
                ShopProduct::updateOrCreate(
                    [
                        'shop_id' => $shop->id,
                        'product_id' => $product['product_id'],
                    ],
                    [
                        'price' => $product['price'],
                        'point' => $product['point'] ?? null,
                        'max_qty' => $product['max_qty'] ?? null,
                        'commission' => $product['commission'] ?? null,
                        'commission_type' => $product['commission_type'] ?? null,
                        'status' => $product['status'],
                    ]
                );
            }

            DB::commit();
            Session::flash('success', __('shop.message.products_saved'));

            return redirect()->route('admin-shop-product', $shop->id);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('shop.message.products_save_failed'));

            return redirect()->back()->withInput();
        }
    }
    public function deleteProduct($shopId = "", $shopProductId = "")
    {
        DB::beginTransaction();
        try {
            ShopProduct::where('shop_id', $shopId)->where('id', $shopProductId)->firstOrFail()->delete();
            DB::commit();
            Session::flash('success', __('shop.message.delete_success'));

            return response()->json([
                'message' => 'success',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('shop.message.delete_failed'));

            return response()->json([
                'message' => 'unsuccess',
                'status' => 404,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function restoreProduct($shopId = "", $shopProductId = "")
    {
        DB::beginTransaction();
        try {
            ShopProduct::withTrashed()->where('shop_id', $shopId)->where('id', $shopProductId)->firstOrFail()->restore();
            DB::commit();
            Session::flash('success', __('shop.message.restore_success'));

            return response()->json([
                'message' => 'success',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('shop.message.restore_failed'));

            return response()->json([
                'message' => 'unsuccess',
                'status' => 404,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function destroyProduct($shopId = "", $shopProductId = "")
    {
        DB::beginTransaction();
        try {
            ShopProduct::withTrashed()->where('shop_id', $shopId)->where('id', $shopProductId)->firstOrFail()->forceDelete();
            DB::commit();
            Session::flash('success', __('shop.message.destroy_success'));

            return response()->json([
                'message' => 'success',
                'status' => 200,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Session::flash('warning', __('shop.message.destroy_failed'));

            return response()->json([
                'message' => 'unsuccess',
                'status' => 404,
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function updateStatus($id="", $status="")
    {
        return $this->tools->onUpdateStatus($this->table, $id, $status);
    }
    public function restore($id = "")
    {
        return $this->tools->onRestore($this->table, $id);
    }
    public function destroy($id = "")
    {
        return $this->tools->onDestroy($this->table, $id);
    }

    public function delete($id = "")
    {
        return $this->tools->onDelete($this->table, $id);
    }

    public function export(Request $req)
    {
        $fileName = 'shops_export_' . date('Y-m-d_H-i-s') . '.csv';
        $shops = Shop::all(); // You might want to apply filters here if needed

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('ID', 'Name', 'Phone', 'Email', 'Address', 'Created At');

        $callback = function() use($shops, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($shops as $shop) {
                $row['ID']  = $shop->id;
                $row['Name']    = $shop->name;
                $row['Phone']    = $shop->phone;
                $row['Email']  = $shop->email;
                $row['Address']  = $shop->address;
                $row['Created At']  = $shop->created_at;

                fputcsv($file, array($row['ID'], $row['Name'], $row['Phone'], $row['Email'], $row['Address'], $row['Created At']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
