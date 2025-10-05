<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Http\Requests\Cashier\CheckoutRequest;
use App\Models\Product;
use App\Services\Cashier\CashierServiceInterface;
use App\Services\Settings\SettingsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CashierController extends Controller
{
    public function __construct(
        private readonly CashierServiceInterface $cashier,
        private readonly SettingsServiceInterface $settings
    ) {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !in_array(Auth::user()->role, [RoleStatus::ADMIN->value, RoleStatus::CASHIER->value], true)) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
            return $next($request);
        });
    }

    public function index(): View
    {
        return view('cashier.index', [
            'currency' => $this->settings->currency(),
            'discount_percent' => $this->settings->discountPercent(),
            'tax_percent' => $this->settings->taxPercent(),
            'receipt_format' => $this->settings->receiptNumberFormat(),
        ]);
    }

    public function products(): JsonResponse
    {
        $q = trim((string) request('q', ''));
        $limit = max(1, min(20, (int) request('limit', 10)));

        $query = Product::query()->select(['id', 'sku', 'name', 'price', 'stock']);
        if ($q !== '') {
            if (ctype_digit($q)) {
                $query->where('id', (int) $q);
            } else {
                $query->where(function ($w) use ($q) {
                    $w->where('sku', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            }
        }
        $products = $query->orderBy('name')->limit($limit)->get();

        return response()->json($products);
    }

    public function checkout(CheckoutRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        try {
            $order = $this->cashier->checkout(
                $data['items'],
                $data['payment_method'],
                (float) ($data['paid_amount'] ?? 0),
                $data['note'] ?? null
            );
            // Jika QRIS dan permintaan JSON/AJAX -> kembalikan snap token untuk Snap Embedded
            if (
                ($data['payment_method'] === 'qris') &&
                ($request->ajax() || $request->wantsJson() || $request->expectsJson())
            ) {
                $order->loadMissing('latestPayment');
                $payment = $order->latestPayment;
                $token = $payment->metadata['snap_token'] ?? null;
                $redir = $payment->metadata['redirect_url'] ?? null;
                return response()->json([
                    'transaction_id' => $order->id,
                    'invoice' => $order->invoice_number,
                    'snap_token' => $token,
                    'redirect_url' => $redir,
                ]);
            }
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()->route('kasir')
            ->with('success', 'Transaksi berhasil. Nomor: ' . $order->invoice_number);
    }
}
