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
            ->select([
                'id', 'date', 'supplier_id', 'product_id',
                'incoming_unit', 'incoming_qty', 'stock_added',
                'purchase_price_per_bulk', 'purchase_price',
                'spoilage_qty', 'total', 'user_id', 'notes', 'created_at',
            ])
            ->orderByDesc('date')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date_formatted', fn(IncomingGood $ig) => $ig->date->format('d/m/Y'))
            ->addColumn('supplier_name', fn(IncomingGood $ig) => e($ig->supplier?->name ?? '-'))
            ->addColumn('product_name', fn(IncomingGood $ig) => e($ig->product?->name ?? '-'))
            ->addColumn('category_name', fn(IncomingGood $ig) => e($ig->product?->category?->name ?? '-'))
            ->addColumn('incoming_display', function (IncomingGood $ig) {
                // Tampilkan: "5 krat → 117 botol (3 rusak)" atau "3 krat → 37.5 kg (1.5 kg busuk)"
                $bulk  = number_format((float) $ig->incoming_qty, 0) . ' ' . ($ig->incoming_unit ?? '');
                $added = number_format((float) $ig->stock_added, 2) . ' ' . ($ig->product?->unit ?? '');
                $spoil = (float) $ig->spoilage_qty > 0
                    ? ' <span class="text-danger">(-' . number_format((float) $ig->spoilage_qty, 2) . ' busuk)</span>'
                    : '';
                return "{$bulk} → {$added}{$spoil}";
            })
            ->editColumn('purchase_price_per_bulk', fn(IncomingGood $ig) =>
                'Rp ' . number_format((float) ($ig->purchase_price_per_bulk ?: $ig->purchase_price), 0, ',', '.') .
                '/' . ($ig->incoming_unit ?? 'unit')
            )
            ->editColumn('total', fn(IncomingGood $ig) =>
                'Rp ' . number_format((float) $ig->total, 0, ',', '.')
            )
            ->addColumn('user_name', fn(IncomingGood $ig) => e($ig->user?->name ?? '-'))
            ->addColumn('action', function (IncomingGood $ig) {
                $deleteUrl     = route('barang-masuk.destroy', $ig);
                $updateDateUrl = route('barang-masuk.update-date', $ig);
                $csrf      = csrf_token();
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
            ->rawColumns(['action', 'incoming_display'])
            ->toJson();
    }

    public function create(): View
    {
        $suppliers = Supplier::query()->orderBy('name')->pluck('name', 'id');
        $products  = Product::query()->with('category')->orderBy('name')->get();
        $enableBulk = app(\App\Services\Settings\SettingsServiceInterface::class)->enableBulkUnit();

        return view('incoming_goods.create', compact('suppliers', 'products', 'enableBulk'));
    }

    public function store(Request $request): RedirectResponse
    {
        $product = Product::findOrFail($request->product_id);

        // Validasi dasar (field form tetap konsisten dengan layout lama)
        $rules = [
            'date'              => ['required', 'date'],
            'supplier_id'       => ['nullable', 'exists:suppliers,id'],
            'product_id'        => ['required', 'exists:products,id'],
            'purchase_price'       => ['required', 'numeric', 'min:0'],
            'selling_price_bulk'   => ['nullable', 'numeric', 'min:0'],
            'selling_price_retail' => ['nullable', 'numeric', 'min:0'],
            'quantity'             => ['required', 'numeric', 'min:0.01'],
            'spoilage_qty'      => ['nullable', 'numeric', 'min:0'],
            'spoilage_notes'    => ['nullable', 'string', 'max:500'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ];

        // Tambah field spesifik per tipe produk (jika fitur bulk aktif)
        $enableBulk = app(\App\Services\Settings\SettingsServiceInterface::class)->enableBulkUnit();
        
        if ($enableBulk) {
            if ($product->isWeightBased()) {
                $rules['gross_weight_kg'] = ['required', 'numeric', 'min:0.001'];
                $rules['krat_weight_kg']  = ['nullable', 'numeric', 'min:0'];
            } else {
                $rules['conversion_factor'] = ['nullable', 'numeric', 'min:0.001'];
            }
        }

        $validated = $request->validate($rules);

        // Map field form ke field service layer
        $serviceData = [
            'date'                   => $validated['date'],
            'supplier_id'            => $validated['supplier_id'] ?? null,
            'product_id'             => $validated['product_id'],
            'incoming_qty'           => $validated['quantity'],
            'purchase_price_per_bulk' => $validated['purchase_price'],
            'selling_price_bulk'     => $validated['selling_price_bulk'] ?? null,
            'selling_price_retail'   => $validated['selling_price_retail'] ?? null,
            'spoilage_qty'           => $validated['spoilage_qty'] ?? null,
            'spoilage_notes'         => $validated['spoilage_notes'] ?? null,
            'notes'                  => $validated['notes'] ?? null,
            'user_id'                => Auth::id(),
        ];

        // Tentukan incoming_unit dari tipe produk
        if ($enableBulk) {
            if ($product->isWeightBased()) {
                $serviceData['incoming_unit']   = $product->bulk_unit ?: 'krat';
                $serviceData['gross_weight_kg'] = $validated['gross_weight_kg'];
                $serviceData['krat_weight_kg']  = $validated['krat_weight_kg'];
            } else {
                $serviceData['incoming_unit']     = $product->bulk_unit ?: 'krat';
                $serviceData['conversion_factor'] = $validated['conversion_factor'] ?? $product->bulk_conversion;
            }
        } else {
            // Jika bulk mati, quantity adalah total stok langsung.
            $serviceData['incoming_unit'] = $product->unit;
            if ($product->isWeightBased()) {
                $serviceData['incoming_qty'] = 1; // Paksa 1 krat
                $serviceData['gross_weight_kg'] = $validated['quantity'];
                $serviceData['krat_weight_kg']  = 0;
            } else {
                $serviceData['conversion_factor'] = 1;
            }
        }

        $incomingGood = $this->service->create($serviceData);
        $incomingGood->loadMissing('product');
        $product = $incomingGood->product;

        $stockAdded  = number_format((float) $incomingGood->stock_added, 2);
        $unit        = $product->unit;
        $bulkQty     = number_format((float) $incomingGood->incoming_qty, 0);
        $bulkUnit    = $incomingGood->incoming_unit;
        $spoilageMsg = (float) $incomingGood->spoilage_qty > 0
            ? " (susut/busuk: {$incomingGood->spoilage_qty} {$unit})"
            : '';

        $this->logger->log(
            'Barang Masuk',
            "Mencatat barang masuk '{$product->name}' {$bulkQty} {$bulkUnit} → stok +{$stockAdded} {$unit}",
            [
                'incoming_good_id' => $incomingGood->id,
                'product_id'       => $product->id,
                'incoming_qty'     => $incomingGood->incoming_qty,
                'stock_added'      => $incomingGood->stock_added,
            ]
        );

        return redirect()->route('barang-masuk.index')->with(
            'success',
            "Barang masuk berhasil! {$product->name}: {$bulkQty} {$bulkUnit} → stok bertambah {$stockAdded} {$unit}{$spoilageMsg}."
        );
    }

    /**
     * Update tanggal barang masuk (cascade ke product_prices & history).
     */
    public function updateDate(Request $request, IncomingGood $incomingGood): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'date.required'        => 'Tanggal wajib diisi.',
            'date.date'            => 'Format tanggal tidak valid.',
            'date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
        ]);

        $oldDate      = $incomingGood->date->format('d/m/Y');
        $incomingGood = $this->service->updateDate($incomingGood, $validated['date']);
        $newDate      = $incomingGood->date->format('d/m/Y');

        $this->logger->log(
            'Edit Tanggal Barang Masuk',
            "Mengubah tanggal barang masuk '{$incomingGood->product?->name}' dari {$oldDate} menjadi {$newDate}",
            ['incoming_good_id' => $incomingGood->id, 'old_date' => $oldDate, 'new_date' => $newDate]
        );

        return redirect()->route('barang-masuk.index')
            ->with('success', "Tanggal berhasil diubah dari {$oldDate} → {$newDate}. Data harga terkait juga sudah diperbarui.");
    }

    public function destroy(IncomingGood $incomingGood): RedirectResponse
    {
        $productName = $incomingGood->product?->name ?? '-';
        $stockAdded  = number_format((float) $incomingGood->stock_added, 2);
        $unit        = $incomingGood->product?->unit ?? '';
        $id          = $incomingGood->id;

        $this->service->delete($incomingGood);

        $this->logger->log(
            'Hapus Barang Masuk',
            "Menghapus data barang masuk '{$productName}' (stok dikurangi {$stockAdded} {$unit})",
            ['incoming_good_id' => $id]
        );

        return redirect()->route('barang-masuk.index')
            ->with('success', "Data barang masuk dihapus. Stok {$productName} dikurangi {$stockAdded} {$unit}.");
    }
}
