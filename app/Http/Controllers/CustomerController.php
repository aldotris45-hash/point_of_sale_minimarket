<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Models\Customer;
use App\Services\Customer\CustomerServiceInterface;
use App\Services\ActivityLog\ActivityLoggerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerServiceInterface $service,
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
        return view('customers.index');
    }

    public function data()
    {
        $query = Customer::query()->select(['id', 'name', 'address', 'phone', 'email', 'notes', 'created_at']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function (Customer $c) {
                $editUrl = route('pelanggan.edit', $c);
                $deleteUrl = route('pelanggan.destroy', $c);
                $csrf = csrf_token();
                return <<<HTML
                    <div class="d-flex justify-content-end gap-1">
                        <a href="{$editUrl}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <form action="{$deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pelanggan ini?');">
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
        return view('customers.create');
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

        $customer = $this->service->create($validated);
        $this->logger->log('Tambah Pelanggan', "Menambahkan pelanggan '{$customer->name}'", ['customer_id' => $customer->id]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $before = $customer->only(['name', 'address', 'phone', 'email']);
        $this->service->update($customer, $validated);
        $this->logger->log('Ubah Pelanggan', "Mengubah pelanggan '{$before['name']}'", ['before' => $before, 'customer_id' => $customer->id]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $name = $customer->name;
        $id = $customer->id;
        $this->service->delete($customer);
        $this->logger->log('Hapus Pelanggan', "Menghapus pelanggan '{$name}'", ['customer_id' => $id]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
