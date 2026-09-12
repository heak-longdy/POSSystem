<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StockInRequest;
use App\Models\Product;
use App\Models\Shop;
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

        $query = $req->status === 'trash'
            ? StockIn::onlyTrashed()
            : StockIn::where('status', $req->status);

        $data['data'] = $query->with([
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
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends($req->query());

        return view($this->layout . 'index', $data);
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
                }

                $message = __('stock_in.message.update_success');
            } else {
                $stockIn = StockIn::create($this->payload($req));
                $this->stockTransaction->applyStockIn($stockIn);
                $message = __('stock_in.message.create_success');
            }

            DB::commit();
            Session::flash('success', $message);

            if ($req->input('save_opt') === 'save_new') {
                return redirect()->back();
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
            'id' => $stockIn?->id ?? '',
            'data' => $stockIn,
            'routeName' => $this->routeName,
            'readonly' => $readonly,
            'selectedShop' => $shopId ? Shop::find($shopId) : null,
            'selectedProduct' => $productId ? Product::find($productId) : null,
            'selectedSupplier' => $supplierId ? Supplier::find($supplierId) : null,
            'currentStock' => $this->currentStock($shopId, $productId),
        ];
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
