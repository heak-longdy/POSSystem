<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StockInRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\StockIn;
use App\Models\StockOnHand;
use App\Models\Supplier;
use App\Services\StockTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class StockInController extends Controller
{
    protected $layout = 'admin::pages.inventoryManagement.stockIn.';
    private $routeName = 'stock-in';
    private $stockTransaction;

    public function __construct(StockTransactionService $stockTransaction)
    {
        $this->stockTransaction = $stockTransaction;
    }

    public function index(Request $req)
    {
        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        $data['shop'] = $req->shop_id ? Shop::find($req->shop_id) : null;
        $exportRoute = 'admin-' . $this->routeName . '-export';
        $data['exportUrl'] = \Illuminate\Support\Facades\Route::has($exportRoute)
            ? route($exportRoute, $req->query())
            : url('admin/' . $this->routeName . '/export' . ($req->query() ? '?' . http_build_query($req->query()) : ''));
        $data['data'] = $this->queryData('list', $req);

        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $data = $this->queryData('report', $req);
        return response()->json($data);
    }

    public function export(Request $req)
    {
        $items = $this->queryData('report', $req);
        $fileName = 'stock_in_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $columns = [
            __('stock_in.table.no'),
            __('stock_in.table.product'),
            __('stock_in.table.shop'),
            __('stock_in.table.supplier'),
            __('stock_in.table.category'),
            __('stock_in.table.uom'),
            __('stock_in.table.qty'),
            __('stock_in.table.date'),
            __('stock_in.table.requested_by'),
            __('stock_in.table.status'),
            __('stock_in.table.remark'),
        ];

        $callback = function () use ($items, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($items as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->product_title ?? '---',
                    $item->shop_title ?? '---',
                    $item->supplier_title ?? '---',
                    $item->category_title ?? '---',
                    $item->uom_title ?? '---',
                    $item->qty ?? 0,
                    $item->created_date ?? '',
                    $item->request_by_title ?? '---',
                    $item->stock_status_title ?? '---',
                    $item->remark ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function queryData($type = 'list', Request $req = null)
    {
        $req = $req ?: request();
        $status = $req->status ?: 1;

        $query = $status === 'trash'
            ? StockIn::onlyTrashed()
            : StockIn::where('status', $status);

        $getData = $query->with([
            'product.category',
            'product.uom',
            'shop',
            'supplier',
            'user',
            'barber',
        ])
            ->when($req->search, function ($query) use ($req) {
                $query->where(function ($q) use ($req) {
                    $q->whereHas('product', function ($product) use ($req) {
                        $product->where('name', 'like', '%' . $req->search . '%');
                    })->orWhereHas('shop', function ($shop) use ($req) {
                        $shop->where('name', 'like', '%' . $req->search . '%');
                    })->orWhereHas('supplier', function ($supplier) use ($req) {
                        $supplier->where('name', 'like', '%' . $req->search . '%');
                    });
                });
            })
            ->when($req->shop_id, function ($query) use ($req) {
                $query->where('shop_id', $req->shop_id);
            })
            ->when($req->date, function ($query) use ($req) {
                $query->whereDate('created_at', $req->date);
            })
            ->orderBy('id', 'desc');

        return $type === 'report'
            ? $getData->get()
            : $getData->paginate(50)->appends($req->query());
    }

    public function onCreate()
    {
        return view($this->layout . 'create', $this->formData());
    }

    public function onEdit(Request $req)
    {
        $stockIn = StockIn::find($req->id);
        if (!$stockIn) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        return view($this->layout . 'create', $this->formData($stockIn));
    }

    public function onView(Request $req)
    {
        $stockIn = StockIn::withTrashed()->find($req->id);
        if (!$stockIn) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        return view($this->layout . 'create', $this->formData($stockIn, true));
    }

    public function onSave(StockInRequest $req, $id = '')
    {
        DB::beginTransaction();
        try {
            if ($id) {
                $stockIn = StockIn::findOrFail($id);
                if ((int) $stockIn->status === 1) {
                    $this->stockTransaction->reverseStockIn($stockIn);
                }

                $stockIn->update($this->payload($req, $stockIn->status));
                $stockIn->refresh();

                if ((int) $stockIn->status === 1) {
                    $this->stockTransaction->applyStockIn($stockIn);
                    $this->stockTransaction->syncShopProduct($stockIn->shop_id, $stockIn->product_id);
                }

                $message = __('stock_in.message.update_success');
            } else {
                if ($req->has('items') && is_array($req->input('items'))) {
                    $rawItems = $req->input('items');
                    $validItems = array_values(array_filter($rawItems, function ($item) {
                        return !empty($item['product_id']) && isset($item['qty']) && (int) $item['qty'] > 0;
                    }));

                    if (empty($validItems)) {
                        throw ValidationException::withMessages([
                            'items' => __('stock_in.validation.items_required'),
                        ]);
                    }

                    foreach ($validItems as $item) {
                        $itemRemark = !empty($item['remark']) ? $item['remark'] : $req->input('remark');

                        $stockIn = StockIn::create([
                            'product_id'      => $item['product_id'],
                            'shop_id'         => $req->input('shop_id'),
                            'supplier_id'     => $req->input('supplier_id'),
                            'supplier_type'   => 'supplier',
                            'qty'             => $item['qty'],
                            'remark'          => $itemRemark,
                            'status'          => 1,
                            'request_by'      => Auth::id(),
                            'request_by_type' => 'admin',
                        ]);

                        $this->stockTransaction->applyStockIn($stockIn);
                        $this->stockTransaction->syncShopProduct($stockIn->shop_id, $stockIn->product_id);
                    }

                    $message = __('stock_in.message.create_success');
                } else {
                    $stockIn = StockIn::create($this->payload($req));
                    $this->stockTransaction->applyStockIn($stockIn);
                    $this->stockTransaction->syncShopProduct($stockIn->shop_id, $stockIn->product_id);
                    $message = __('stock_in.message.create_success');
                }
            }

            DB::commit();
            Session::flash('success', $message);

            if ($req->input('save_opt') === 'save_new') {
                return redirect()->route('admin-' . $this->routeName . '-create');
            }

            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', $id ? __('stock_in.message.update_failed') : __('stock_in.message.create_failed'));

            return redirect()->back()->withInput();
        }
    }

    public function updateStatus($id, $status)
    {
        DB::beginTransaction();
        try {
            $stockIn = StockIn::findOrFail($id);
            if ((int) $stockIn->status !== (int) $status) {
                if ((int) $status === 2 && (int) $stockIn->status === 1) {
                    $this->stockTransaction->reverseStockIn($stockIn);
                }

                if ((int) $status === 1 && (int) $stockIn->status === 2) {
                    $this->stockTransaction->applyStockIn($stockIn);
                }

                $stockIn->update(['status' => $status]);
            }

            DB::commit();
            Session::flash('success', (int) $status === 2 ? __('stock_in.message.disable_success') : __('stock_in.message.enable_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_in.message.status_failed'));

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function delete($id = '')
    {
        DB::beginTransaction();
        try {
            $stockIn = StockIn::findOrFail($id);
            if ((int) $stockIn->status === 1) {
                $this->stockTransaction->reverseStockIn($stockIn);
            }

            $stockIn->delete();

            DB::commit();
            Session::flash('success', __('stock_in.message.delete_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_in.message.delete_failed'));

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function restore($id = '')
    {
        DB::beginTransaction();
        try {
            $stockIn = StockIn::withTrashed()->findOrFail($id);
            $stockIn->restore();

            if ((int) $stockIn->status === 1) {
                $this->stockTransaction->applyStockIn($stockIn);
            }

            DB::commit();
            Session::flash('success', __('stock_in.message.restore_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_in.message.restore_failed'));

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function destroy($id = '')
    {
        DB::beginTransaction();
        try {
            $stockIn = StockIn::withTrashed()->findOrFail($id);
            if (!$stockIn->trashed() && (int) $stockIn->status === 1) {
                $this->stockTransaction->reverseStockIn($stockIn);
            }

            $this->stockTransaction->deleteHistory($stockIn->id, 'stock_in');
            $stockIn->forceDelete();

            DB::commit();
            Session::flash('success', __('stock_in.message.destroy_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_in.message.destroy_failed'));

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    private function payload(StockInRequest $req, $status = 1)
    {
        return [
            'product_id' => $req->product_id,
            'shop_id' => $req->shop_id,
            'supplier_id' => $req->supplier_id,
            'supplier_type' => 'supplier',
            'qty' => $req->qty,
            'remark' => $req->remark,
            'status' => $status,
            'request_by' => Auth::id(),
            'request_by_type' => 'admin',
        ];
    }

    private function formData(StockIn $stockIn = null, $readonly = false)
    {
        $shopId = old('shop_id', $stockIn?->shop_id);
        $productId = old('product_id', $stockIn?->product_id);
        $supplierId = old('supplier_id', $stockIn?->supplier_id);

        return [
            'id'                => $stockIn?->id ?? '',
            'data'              => $stockIn,
            'routeName'         => $this->routeName,
            'readonly'          => $readonly,
            'selectedShop'      => $shopId ? Shop::find($shopId) : null,
            'selectedProduct'   => $productId ? Product::find($productId) : null,
            'selectedSupplier'  => $supplierId ? Supplier::find($supplierId) : null,
            'currentStock'      => $this->currentStock($shopId, $productId),
            'preloadedProducts' => $shopId ? $this->getShopProductsData($shopId) : [],
        ];
    }

    public function getShopProducts($shopId)
    {
        return response()->json([
            'status' => 200,
            'data'   => $this->getShopProductsData($shopId),
        ]);
    }

    public function getShopProductsData($shopId)
    {
        if (!$shopId) {
            return [];
        }

        $shopProducts = ShopProduct::where('shop_id', $shopId)
            ->where('status', 1)
            ->whereHas('product', function ($query) {
                $query->where('status', 1);
            })
            ->with(['product.category', 'product.uom'])
            ->get()
            ->unique('product_id');

        $stockOnHands = StockOnHand::where('shop_id', $shopId)
            ->pluck('current_stock', 'product_id');

        $list = [];

        foreach ($shopProducts as $sp) {
            if ($sp->product && (int) $sp->product->status === 1) {
                $list[] = [
                    'id'            => (string) $sp->product->id,
                    'name'          => $sp->product->name,
                    'category'      => $sp->product->category?->name ?? '---',
                    'uom'           => $sp->product->uom?->name ?? '---',
                    'image'         => $sp->product->image_url ?? asset('images/logo/default.png'),
                    'current_stock' => (int) ($stockOnHands[$sp->product->id] ?? 0),
                    'is_assigned'   => true,
                ];
            }
        }

        usort($list, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $list;
    }

    private function currentStock($shopId, $productId)
    {
        if (!$shopId || !$productId) {
            return 0;
        }

        return (int) (StockOnHand::where('shop_id', $shopId)
            ->where('product_id', $productId)
            ->value('current_stock') ?? 0);
    }
}
