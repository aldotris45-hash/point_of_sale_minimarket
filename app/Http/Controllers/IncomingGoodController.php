<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Models\IncomingGood;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\IncomingGood\IncomingGoodServiceInterface;
use App\Services\ActivityLog\ActivityLoggerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class IncomingGoodController extends Controller
{
    public function __construct(
        private readonly IncomingGoodServiceInterface $service,
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
        return view('incoming_goods.index');
    }

    public function data(Request $request)
    {
        $query = IncomingGood::query()
            ->with(['supplier', 'product', 'product.category', 'user'])
            ->select(['id', 'date', 'supplier_id', 'product_id', 'purchase_price', 'quantity', 'total', 'user_id', 'notes', 'created_at'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date_formatted', fn(IncomingGood $ig) => $ig->date->format('d/m/Y'))
            ->addColumn('supplier_name', fn(IncomingGood $ig) => e($ig->supplier?->name ?? '-'))
            ->addColumn('product_name', fn(IncomingGood $ig) => e($ig->product?->name ?? '-'))
            ->addColumn('category_name', fn(IncomingGood $ig) => e($ig->product?->category?->name ?? '-'))
            ->editColumn('purchase_price', fn(IncomingGood $ig) => 'Rp ' . number_format((float) $ig->purchase_price, 0, ',', '.'))
            ->editColumn('total', fn(IncomingGood $ig) => 'Rp ' . number_format((float) $ig->total, 0, ',', '.'))
            ->addColumn('user_name', fn(IncomingGood $ig) => e($ig->user?->name ?? '-'))
            ->addColumn('action', function (IncomingGood $ig) {
                $deleteUrl = route('barang-masuk.destroy', $ig);
                $updateDateUrl = route('barang-masuk.update-date', $ig);
                $csrf = csrf_token();
                $dateValue = $ig->date->format('Y-m-d');
                return <<<HTML
                    <div class="d-flex gap-1 justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-date"
                            data-url="{$updateDateUrl}" data-date="{$dateValue}" data-id="{$ig->id}"
                            title="Ubah tanggal">
                            <i class="bi bi-calendar-event"></i>
                        </button>
                        <form action="{$deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data barang masuk ini? Stok produk akan dikurangi kembali.');">
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
        $suppliers = Supplier::query()->orderBy('name')->pluck('name', 'id');
        $products = Product::query()->with('category')->orderBy('name')->get();

        return view('incoming_goods.create', compact('suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['user_id'] = Auth::id();

        $incomingGood = $this->service->create($validated);
        $incomingGood->loadMissing('product');
        $product = $incomingGood->product;

        $this->logger->log(
            'Barang Masuk',
            "Mencatat barang masuk '{$product->name}' sebanyak {$incomingGood->quantity}",
            ['incoming_good_id' => $incomingGood->id, 'product_id' => $product->id, 'quantity' => $incomingGood->quantity]
        );

        return redirect()->route('barang-masuk.index')->with('success', "Barang masuk berhasil dicatat! Stok {$product->name} bertambah {$incomingGood->quantity}.");
    }

    /**
     * Update tanggal barang masuk (cascade ke product_prices & history).
     */
    public function updateDate(Request $request, IncomingGood $incomingGood): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
        ]);

        $oldDate = $incomingGood->date->format('d/m/Y');
        $incomingGood = $this->service->updateDate($incomingGood, $validated['date']);
        $newDate = $incomingGood->date->format('d/m/Y');

        $this->logger->log(
            'Edit Tanggal Barang Masuk',
            "Mengubah tanggal barang masuk '{$incomingGood->product?->name}' dari {$oldDate} menjadi {$newDate}",
            ['incoming_good_id' => $incomingGood->id, 'old_date' => $oldDate, 'new_date' => $newDate]
        );

        return redirect()->route('barang-masuk.index')->with('success', "Tanggal berhasil diubah dari {$oldDate} → {$newDate}. Data harga terkait juga sudah diperbarui.");
    }

    public function destroy(IncomingGood $incomingGood): RedirectResponse
    {
        $productName = $incomingGood->product?->name ?? '-';
        $qty = $incomingGood->quantity;
        $id = $incomingGood->id;

        $this->service->delete($incomingGood);
        $this->logger->log('Hapus Barang Masuk', "Menghapus data barang masuk '{$productName}' ({$qty})", ['incoming_good_id' => $id]);

        return redirect()->route('barang-masuk.index')->with('success', "Data barang masuk dihapus. Stok {$productName} dikurangi {$qty}.");
    }
}
