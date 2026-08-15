<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StockOutRequest;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockOnHand;
use App\Models\StockOut;
use App\Models\StockType;
use App\Services\StockTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class StockOutController extends Controller
{
    protected $layout = 'admin::pages.inventoryManagement.stockOut.';
    private $routeName = 'stock-out';
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
            ? StockOut::onlyTrashed()
            : StockOut::where('status', $req->status);

        $data['data'] = $query->with([
            'product.category',
            'product.uom',
            'shop',
            'stockType',
            'user',
            'barber',
        ])
            ->when($req->search, function ($query) use ($req) {
                $query->where(function ($q) use ($req) {
                    $q->whereHas('product', function ($product) use ($req) {
                        $product->where('name', 'like', '%' . $req->search . '%');
                    })->orWhereHas('shop', function ($shop) use ($req) {
                        $shop->where('name', 'like', '%' . $req->search . '%');
                    })->orWhereHas('stockType', function ($stockType) use ($req) {
                        $stockType->where('name', 'like', '%' . $req->search . '%');
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
        $stockOut = StockOut::find($req->id);
        if (!$stockOut) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        return view($this->layout . 'create', $this->formData($stockOut));
    }

    public function onView(Request $req)
    {
        $stockOut = StockOut::withTrashed()->find($req->id);
        if (!$stockOut) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        return view($this->layout . 'create', $this->formData($stockOut, true));
    }

    public function onSave(StockOutRequest $req, $id = '')
    {
        DB::beginTransaction();
        try {
            if ($id) {
                $stockOut = StockOut::findOrFail($id);
                if ((int) $stockOut->status === 1) {
                    $this->stockTransaction->reverseStockOut($stockOut);
                }

                $stockOut->update($this->payload($req, $stockOut->status));
                $stockOut->refresh();

                if ((int) $stockOut->status === 1) {
                    $this->stockTransaction->applyStockOut($stockOut);
                }

                $message = 'Update success.';
            } else {
                $stockOut = StockOut::create($this->payload($req));
                $this->stockTransaction->applyStockOut($stockOut);
                $message = 'Create success.';
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
            Session::flash('warning', $id ? 'Update unsuccess!' : 'Create unsuccess!');

            return redirect()->back()->withInput();
        }
    }

    public function updateStatus($id, $status)
    {
        DB::beginTransaction();
        try {
            $stockOut = StockOut::findOrFail($id);
            if ((int) $stockOut->status !== (int) $status) {
                if ((int) $status === 2 && (int) $stockOut->status === 1) {
                    $this->stockTransaction->reverseStockOut($stockOut);
                }

                if ((int) $status === 1 && (int) $stockOut->status === 2) {
                    $this->stockTransaction->applyStockOut($stockOut);
                }

                $stockOut->update(['status' => $status]);
            }

            DB::commit();
            Session::flash('success', (int) $status === 2 ? 'Disable successful!' : 'Enable successful!');

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', 'Status unsuccess!');

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function delete($id = '')
    {
        DB::beginTransaction();
        try {
            $stockOut = StockOut::findOrFail($id);
            if ((int) $stockOut->status === 1) {
                $this->stockTransaction->reverseStockOut($stockOut);
            }

            $stockOut->delete();

            DB::commit();
            Session::flash('success', 'Delete success!');

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', 'Delete unsuccess!');

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function restore($id = '')
    {
        DB::beginTransaction();
        try {
            $stockOut = StockOut::withTrashed()->findOrFail($id);
            $stockOut->restore();

            if ((int) $stockOut->status === 1) {
                $this->stockTransaction->applyStockOut($stockOut);
            }

            DB::commit();
            Session::flash('success', 'Restore success!');

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', 'Restore unsuccess!');

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function destroy($id = '')
    {
        DB::beginTransaction();
        try {
            $stockOut = StockOut::withTrashed()->findOrFail($id);
            if (!$stockOut->trashed() && (int) $stockOut->status === 1) {
                $this->stockTransaction->reverseStockOut($stockOut);
            }

            $this->stockTransaction->deleteHistory($stockOut->id, 'stock_out');
            $stockOut->forceDelete();

            DB::commit();
            Session::flash('success', 'Delete success!');

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', 'Delete unsuccess!');

            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    private function payload(StockOutRequest $req, $status = 1)
    {
        return [
            'product_id' => $req->product_id,
            'shop_id' => $req->shop_id,
            'to_id' => $req->to_id,
            'qty' => $req->qty,
            'remark' => $req->remark,
            'type' => 'stock_type',
            'status' => $status,
            'request_by' => Auth::id(),
            'request_by_type' => 'admin',
        ];
    }

    private function formData(StockOut $stockOut = null, $readonly = false)
    {
        $shopId = old('shop_id', $stockOut?->shop_id);
        $productId = old('product_id', $stockOut?->product_id);

        return [
            'id' => $stockOut?->id ?? '',
            'data' => $stockOut,
            'routeName' => $this->routeName,
            'readonly' => $readonly,
            'stockTypes' => StockType::where('status', 1)->orderBy('ordering', 'asc')->get(),
            'selectedShop' => $shopId ? Shop::find($shopId) : null,
            'selectedProduct' => $productId ? Product::find($productId) : null,
            'currentStock' => $this->currentStock($shopId, $productId, $stockOut),
        ];
    }

    private function currentStock($shopId, $productId, StockOut $stockOut = null)
    {
        if (!$shopId || !$productId) {
            return 0;
        }

        $currentStock = (int) (StockOnHand::where('shop_id', $shopId)
            ->where('product_id', $productId)
            ->value('current_stock') ?? 0);

        if ($stockOut
            && (int) $stockOut->status === 1
            && (int) $stockOut->shop_id === (int) $shopId
            && (int) $stockOut->product_id === (int) $productId) {
            $currentStock += (int) $stockOut->qty;
        }

        return $currentStock;
    }
}
