<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StockTransferRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockOnHand;
use App\Models\StockTransfer;
use App\Services\StockTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class StockTransferController extends Controller
{
    protected $layout = 'admin::pages.inventoryManagement.stockTransfer.';
    private $routeName = 'stock-transfer';
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
            ? StockTransfer::onlyTrashed()
            : StockTransfer::where('status', $req->status);

        $data['data'] = $query->with([
            'product.category',
            'product.uom',
            'shop',
            'shopTo',
            'user',
            'barber',
        ])
            ->when($req->search, function ($query) use ($req) {
                $query->where(function ($q) use ($req) {
                    $q->whereHas('product', function ($product) use ($req) {
                        $product->where('name', 'like', '%' . $req->search . '%');
                    })->orWhereHas('shop', function ($shop) use ($req) {
                        $shop->where('name', 'like', '%' . $req->search . '%');
                    })->orWhereHas('shopTo', function ($shop) use ($req) {
                        $shop->where('name', 'like', '%' . $req->search . '%');
                    });
                });
            })
            ->when($req->shop_id, function ($query) use ($req) {
                $query->where(function ($q) use ($req) {
                    $q->where('from_shop_id', $req->shop_id)
                        ->orWhere('to_shop_id', $req->shop_id);
                });
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
        $stockTransfer = StockTransfer::find($req->id);
        if (!$stockTransfer) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        return view($this->layout . 'create', $this->formData($stockTransfer));
    }

    public function onView(Request $req)
    {
        $stockTransfer = StockTransfer::withTrashed()->find($req->id);
        if (!$stockTransfer) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        return view($this->layout . 'create', $this->formData($stockTransfer, true));
    }

    public function onUpdate(StockTransferRequest $req, $id = '')
    {
        return $this->onSave($req, $id);
    }

    public function onSave(StockTransferRequest $req, $id = '')
    {
        DB::beginTransaction();
        try {
            if ($id) {
                $stockTransfer = StockTransfer::findOrFail($id);
                if ((int) $stockTransfer->status === 1) {
                    $this->stockTransaction->reverseStockTransfer($stockTransfer);
                }

                $stockTransfer->update($this->payload($req, $stockTransfer->status));
                $stockTransfer->refresh();

                if ((int) $stockTransfer->status === 1) {
                    $this->stockTransaction->applyStockTransfer($stockTransfer);
                }

                $message = __('stock_transfer.message.update_success');
            } else {
                $stockTransfer = StockTransfer::create($this->payload($req));
                $this->stockTransaction->applyStockTransfer($stockTransfer);
                $message = __('stock_transfer.message.create_success');
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
            Session::flash('warning', $id ? __('stock_transfer.message.update_failed') : __('stock_transfer.message.create_failed'));

            return redirect()->back()->withInput();
        }
    }

    public function updateStatus($id, $status)
    {
        DB::beginTransaction();
        try {
            $stockTransfer = StockTransfer::findOrFail($id);
            if ((int) $stockTransfer->status !== (int) $status) {
                if ((int) $status === 2 && (int) $stockTransfer->status === 1) {
                    $this->stockTransaction->reverseStockTransfer($stockTransfer);
                }

                if ((int) $status === 1 && (int) $stockTransfer->status === 2) {
                    $this->stockTransaction->applyStockTransfer($stockTransfer);
                }

                $stockTransfer->update(['status' => $status]);
            }

            DB::commit();
            Session::flash('success', (int) $status === 2 ? __('stock_transfer.message.disable_success') : __('stock_transfer.message.enable_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_transfer.message.status_failed'));

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function delete($id = '')
    {
        DB::beginTransaction();
        try {
            $stockTransfer = StockTransfer::findOrFail($id);
            if ((int) $stockTransfer->status === 1) {
                $this->stockTransaction->reverseStockTransfer($stockTransfer);
            }

            $stockTransfer->delete();

            DB::commit();
            Session::flash('success', __('stock_transfer.message.delete_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_transfer.message.delete_failed'));

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function restore($id = '')
    {
        DB::beginTransaction();
        try {
            $stockTransfer = StockTransfer::withTrashed()->findOrFail($id);
            $stockTransfer->restore();

            if ((int) $stockTransfer->status === 1) {
                $this->stockTransaction->applyStockTransfer($stockTransfer);
            }

            DB::commit();
            Session::flash('success', __('stock_transfer.message.restore_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_transfer.message.restore_failed'));

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function destroy($id = '')
    {
        DB::beginTransaction();
        try {
            $stockTransfer = StockTransfer::withTrashed()->findOrFail($id);
            if (!$stockTransfer->trashed() && (int) $stockTransfer->status === 1) {
                $this->stockTransaction->reverseStockTransfer($stockTransfer);
            }

            $this->stockTransaction->deleteHistory($stockTransfer->id, 'stock_transfer');
            $stockTransfer->forceDelete();

            DB::commit();
            Session::flash('success', __('stock_transfer.message.destroy_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_transfer.message.destroy_failed'));

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    private function payload(StockTransferRequest $req, $status = 1)
    {
        return [
            'product_id' => $req->product_id,
            'from_shop_id' => $req->shop_id,
            'to_shop_id' => $req->to_shop_id,
            'qty' => $req->qty,
            'remark' => $req->remark,
            'status' => $status,
            'request_by' => Auth::id(),
            'request_by_type' => 'admin',
        ];
    }

    private function formData(StockTransfer $stockTransfer = null, $readonly = false)
    {
        $shopId = old('shop_id', $stockTransfer?->from_shop_id);
        $toShopId = old('to_shop_id', $stockTransfer?->to_shop_id);
        $productId = old('product_id', $stockTransfer?->product_id);

        return [
            'id' => $stockTransfer?->id ?? '',
            'data' => $stockTransfer,
            'routeName' => $this->routeName,
            'readonly' => $readonly,
            'selectedShop' => $shopId ? Shop::find($shopId) : null,
            'selectedToShop' => $toShopId ? Shop::find($toShopId) : null,
            'selectedProduct' => $productId ? Product::find($productId) : null,
            'currentStock' => $this->currentStock($shopId, $productId, $stockTransfer),
        ];
    }

    private function currentStock($shopId, $productId, StockTransfer $stockTransfer = null)
    {
        if (!$shopId || !$productId) {
            return 0;
        }

        $currentStock = (int) (StockOnHand::where('shop_id', $shopId)
            ->where('product_id', $productId)
            ->value('current_stock') ?? 0);

        if ($stockTransfer
            && (int) $stockTransfer->status === 1
            && (int) $stockTransfer->from_shop_id === (int) $shopId
            && (int) $stockTransfer->product_id === (int) $productId) {
            $currentStock += (int) $stockTransfer->qty;
        }

        return $currentStock;
    }
}
