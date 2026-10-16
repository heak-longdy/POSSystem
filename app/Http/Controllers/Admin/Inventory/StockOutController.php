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
        $fileName = 'stock_out_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $columns = [
            __('global.table.no'),
            __('stock_out.table.product'),
            __('stock_out.table.category'),
            __('stock_out.table.uom'),
            __('stock_out.table.qty'),
            __('stock_out.table.date'),
            __('stock_out.table.remark'),
            __('stock_out.table.shop'),
            __('stock_out.table.to'),
            __('stock_out.table.requested_by'),
            __('stock_out.table.status'),
        ];

        $callback = function () use ($items, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($items as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->product_title ?? '---',
                    $item->category_title ?? '---',
                    $item->uom_title ?? '---',
                    $item->qty ?? 0,
                    $item->created_date ?? '',
                    $item->remark ?? '',
                    $item->shop_title ?? '---',
                    $item->destination_title ?? '---',
                    $item->request_by_title ?? '---',
                    $item->stock_status_title ?? '---',
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
            ? StockOut::onlyTrashed()
            : StockOut::where('status', $status);

        $getData = $query->with([
            'product.category',
            'product.uom',
            'shop',
            'stockType',
            'customer',
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
            ->orderBy('id', 'desc');

        if ($type === 'report') {
            return $getData->get();
        }

        return $getData->paginate(50)->appends($req->query());
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

                $message = __('stock_out.message.update_success');
            } else {
                $stockOut = StockOut::create($this->payload($req));
                $this->stockTransaction->applyStockOut($stockOut);
                $message = __('stock_out.message.create_success');
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
            Session::flash('warning', $id ? __('stock_out.message.update_failed') : __('stock_out.message.create_failed'));

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
            Session::flash('success', (int) $status === 2 ? __('stock_out.message.disable_success') : __('stock_out.message.enable_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_out.message.status_failed'));

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
            Session::flash('success', __('stock_out.message.delete_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_out.message.delete_failed'));

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
            Session::flash('success', __('stock_out.message.restore_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_out.message.restore_failed'));

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
            Session::flash('success', __('stock_out.message.destroy_success'));

            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', __('stock_out.message.destroy_failed'));

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
