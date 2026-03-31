<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Models\Supplier;
use App\Services\Supplier\SupplierServiceInterface;
use App\Services\ActivityLog\ActivityLoggerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function __construct(
        private readonly SupplierServiceInterface $service,
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
        return view('suppliers.index');
    }

    public function data()
    {
        $query = Supplier::query()->select(['id', 'name', 'address', 'phone', 'email', 'notes', 'created_at']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function (Supplier $s) {
                $editUrl = route('supplier.edit', $s);
                $deleteUrl = route('supplier.destroy', $s);
                $csrf = csrf_token();
                return <<<HTML
                    <div class="d-flex justify-content-end gap-1">
                        <a href="{$editUrl}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <form action="{$deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Hapus supplier ini?');">
                            <input type="hidden" name="_token" value="{$csrf}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Hapus
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
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $supplier = $this->service->create($validated);
        $this->logger->log('Tambah Supplier', "Menambahkan supplier '{$supplier->name}'", ['supplier_id' => $supplier->id]);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $before = $supplier->only(['name', 'address', 'phone', 'email']);
        $this->service->update($supplier, $validated);
        $this->logger->log('Ubah Supplier', "Mengubah supplier '{$before['name']}'", ['before' => $before, 'supplier_id' => $supplier->id]);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $name = $supplier->name;
        $id = $supplier->id;
        $this->service->delete($supplier);
        $this->logger->log('Hapus Supplier', "Menghapus supplier '{$name}'", ['supplier_id' => $id]);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
