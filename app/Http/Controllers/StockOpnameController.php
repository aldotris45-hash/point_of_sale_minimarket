<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Models\Product;
use App\Models\StockOpname;
use App\Services\StockOpname\StockOpnameServiceInterface;
use App\Services\ActivityLog\ActivityLoggerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StockOpnameController extends Controller
{
    public function __construct(
        private readonly StockOpnameServiceInterface $service,
        private readonly ActivityLoggerInterface $logger,
    ) {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== RoleStatus::ADMIN->value) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
            return $next($request);
        });
    }

    public function index(): View
    {
        return view('stock_opnames.index');
    }

    public function data(Request $request)
    {
        $query = StockOpname::query()
            ->with(['product', 'product.category', 'user'])
            ->select(['id', 'date', 'product_id', 'system_stock', 'physical_stock', 'difference', 'notes', 'user_id', 'created_at'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date_formatted', fn(StockOpname $so) => $so->date->format('d/m/Y'))
            ->addColumn('product_name', fn(StockOpname $so) => e($so->product?->name ?? '-'))
            ->addColumn('category_name', fn(StockOpname $so) => e($so->product?->category?->name ?? '-'))
            ->addColumn('difference_display', function (StockOpname $so) {
                $diff = $so->difference;
                $color = $diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-success' : 'text-muted');
                $prefix = $diff > 0 ? '+' : '';
                return '<span class="fw-semibold ' . $color . '">' . $prefix . $diff . '</span>';
            })
            ->addColumn('user_name', fn(StockOpname $so) => e($so->user?->name ?? '-'))
            ->addColumn('action', function (StockOpname $so) {
                $deleteUrl = route('stok-opname.destroy', $so);
                $csrf = csrf_token();
                return <<<HTML
                    <form action="{$deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data stok opname ini?');">
                        <input type="hidden" name="_token" value="{$csrf}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                HTML;
            })
            ->rawColumns(['difference_display', 'action'])
            ->toJson();
    }

    public function create(): View
    {
        $products = Product::query()->with('category')->orderBy('name')->get();

        return view('stock_opnames.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'product_id' => ['required', 'exists:products,id'],
            'physical_stock' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['user_id'] = Auth::id();

        $stockOpname = $this->service->create($validated);
        $product = $stockOpname->product;

        $this->logger->log(
            'Stok Opname',
            "Stok opname '{$product->name}': sistem={$stockOpname->system_stock}, fisik={$stockOpname->physical_stock}, selisih={$stockOpname->difference}",
            ['stock_opname_id' => $stockOpname->id, 'product_id' => $product->id, 'difference' => $stockOpname->difference]
        );

        return redirect()->route('stok-opname.index')->with('success', "Stok opname berhasil disimpan! Stok {$product->name} disesuaikan menjadi {$stockOpname->physical_stock}.");
    }

    public function destroy(StockOpname $stockOpname): RedirectResponse
    {
        $productName = $stockOpname->product?->name ?? '-';
        $id = $stockOpname->id;

        $this->service->delete($stockOpname);
        $this->logger->log('Hapus Stok Opname', "Menghapus data stok opname '{$productName}'", ['stock_opname_id' => $id]);

        return redirect()->route('stok-opname.index')->with('success', 'Data stok opname berhasil dihapus.');
    }

    /**
     * API endpoint: ambil stok sistem produk (untuk AJAX di form create).
     */
    public function getProductStock(Product $product)
    {
        return response()->json([
            'stock' => (int) $product->stock,
        ]);
    }
}
