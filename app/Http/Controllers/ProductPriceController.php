<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductPriceHistory;
use App\Enums\RoleStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProductPriceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== RoleStatus::ADMIN->value) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
            return $next($request);
        });
    }

    public function index(): View
    {
        $products = Product::query()->orderBy('name')->get();
        return view('product_prices_index', compact('products'));
    }

    public function data(Request $request)
    {
        $productId = $request->input('product_id');
        
        $query = ProductPrice::query()
            ->with('product')
            ->when($productId, function ($w) use ($productId) {
                $w->where('product_id', $productId);
            })
            ->orderByDesc('price_date')
            ->orderByDesc('id')
            ->select(['id', 'product_id', 'cost_price', 'selling_price', 'price_date', 'notes']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('product_name', fn(ProductPrice $p) => $p->product?->name)
            ->editColumn('cost_price', fn(ProductPrice $p) => 'Rp ' . number_format((float) $p->cost_price, 0, ',', '.'))
            ->editColumn('selling_price', fn(ProductPrice $p) => 'Rp ' . number_format((float) $p->selling_price, 0, ',', '.'))
            ->addColumn('date', fn(ProductPrice $p) => $p->price_date->format('d/m/Y'))
            ->addColumn('action', function (ProductPrice $p) {
                $editUrl = route('harga-produk.edit', $p);
                $deleteUrl = route('harga-produk.destroy', $p);
                $historyUrl = route('harga-produk.history', $p->product);
                $csrf = csrf_token();
                return <<<HTML
                    <div class="d-flex justify-content-end gap-1">
                        <a href="{$historyUrl}" class="btn btn-sm btn-outline-info" title="Riwayat Harga">
                            <i class="bi bi-clock-history"></i>
                        </a>
                        <a href="{$editUrl}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{$deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data harga ini?');">
                            <input type="hidden" name="_token" value="{$csrf}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                HTML;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(): View
    {
        $products = Product::query()->orderBy('name')->get();
        return view('product_prices_create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'cost_price' => ['required', 'numeric', 'min:0.01'],
            'selling_price' => ['required', 'numeric', 'min:0.01'],
            'price_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::find($validated['product_id']);

        // Simpan harga
        $price = ProductPrice::create($validated);

        // Simpan riwayat harga jual
        ProductPriceHistory::create([
            'product_id' => $product->id,
            'selling_price' => $validated['selling_price'],
            'effective_date' => $validated['price_date'],
            'changed_at' => now(),
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('harga-produk.index')->with('success', 'Harga produk berhasil ditambahkan.');
    }

    public function edit(ProductPrice $productPrice): View
    {
        $products = Product::query()->orderBy('name')->get();
        return view('product_prices_edit', compact('productPrice', 'products'));
    }

    public function update(Request $request, ProductPrice $productPrice): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'cost_price' => ['required', 'numeric', 'min:0.01'],
            'selling_price' => ['required', 'numeric', 'min:0.01'],
            'price_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $oldPrice = $productPrice->selling_price;
        $productPrice->update($validated);

        // Catat perubahan harga jual jika ada perubahan
        if ($oldPrice != $validated['selling_price']) {
            ProductPriceHistory::create([
                'product_id' => $productPrice->product_id,
                'selling_price' => $validated['selling_price'],
                'effective_date' => $validated['price_date'],
                'changed_at' => now(),
                'notes' => 'Perubahan: ' . ($validated['notes'] ?? 'Tanpa keterangan'),
            ]);
        }

        return redirect()->route('harga-produk.index')->with('success', 'Harga produk berhasil diperbarui.');
    }

    public function destroy(ProductPrice $productPrice): RedirectResponse
    {
        $productPrice->delete();
        return redirect()->route('harga-produk.index')->with('success', 'Harga produk berhasil dihapus.');
    }

    public function history(Product $product): View
    {
        return view('product_prices_history', compact('product'));
    }

    public function historyData(Product $product)
    {
        $query = ProductPriceHistory::query()
            ->where('product_id', $product->id)
            ->orderByDesc('changed_at')
            ->orderByDesc('id')
            ->select(['id', 'selling_price', 'effective_date', 'changed_at', 'notes']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('selling_price', fn(ProductPriceHistory $ph) => 'Rp ' . number_format((float) $ph->selling_price, 0, ',', '.'))
            ->addColumn('effective_date', fn(ProductPriceHistory $ph) => $ph->effective_date->format('d/m/Y'))
            ->addColumn('changed_at', fn(ProductPriceHistory $ph) => $ph->changed_at->format('d/m/Y H:i:s'))
            ->addColumn('notes', fn(ProductPriceHistory $ph) => e($ph->notes ?? '-'))
            ->toJson();
    }
}
