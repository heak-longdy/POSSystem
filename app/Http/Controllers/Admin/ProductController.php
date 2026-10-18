<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\Tools;
use App\Http\Requests\Admin\ProductRequest;

class ProductController extends Controller
{
    protected $layout = 'admin::pages.product.';
    private $tools;
    private $table = Product::class;
    private $routeName = "product";
    public function __construct(Tools $serTool)
    {
        $this->tools = $serTool;
    }
    private function queryProducts(Request $req)
    {
        $status = $req->status ?? 1;
        if ($status !== 'trash') {
            $query = Product::with(['category', 'uom'])->where('status', $status);
        } else {
            $query = Product::with(['category', 'uom'])->onlyTrashed();
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('code', 'LIKE', '%' . $search . '%');
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        if (!$req->status) {
            return redirect()->route('admin-'.$this->routeName.'-list', 1);
        }
        $query = $this->queryProducts($req);
        $data['data'] = $query->paginate(50);
        $data['exportUrl'] = route('admin-product-export', request()->query());
        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $products = $this->queryProducts($req)->get();

        return response()->json([
            'status' => $req->status ?? 1,
            'rows'   => $products,
        ]);
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
        $data['data'] = Product::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(ProductRequest $req, $id = "")
    {
        if ($req->input('save_opt') == 'save_new') {
            return $this->tools->onSave($this->table, $req, $id, $this->routeName, 'back');
        }
        return $this->tools->onSave($this->table, $req, $id, $this->routeName);
    }
    public function updateStatus($id, $status)
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
        $fileName = 'products_export_' . date('Y-m-d_H-i-s') . '.csv';
        $products = $this->queryProducts($req)->get();

        $headers = array(
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            __('product.export.id'),
            __('product.export.code'),
            __('product.export.name'),
            __('global.table.category'),
            __('global.table.uom'),
            __('product.export.cost'),
            __('product.export.price'),
            __('product.export.commission'),
            __('product.export.created_at'),
        );

        $callback = function() use($products, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($products as $index => $product) {
                fputcsv($file, array(
                    $index + 1,
                    $product->code ?? '',
                    $product->name ?? '',
                    $product->category_title ?? ($product->category->name ?? ''),
                    $product->uom_title ?? ($product->uom->name ?? ''),
                    $product->cost ?? 0,
                    $product->price ?? 0,
                    $product->commission ?? 0,
                    $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : '',
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
