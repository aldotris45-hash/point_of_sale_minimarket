<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        if (!Auth::check() || Auth::user()->role !== \App\Enums\RoleStatus::ADMIN->value) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return view('payments.index', [
            'q' => trim((string) $request->query('q', '')),
            'status' => $request->query('status'),
            'method' => $request->query('method'),
            'provider' => $request->query('provider'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'statuses' => PaymentStatus::cases(),
            'methods' => PaymentMethod::cases(),
        ]);
    }

    public function data(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== \App\Enums\RoleStatus::ADMIN->value) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $method = $request->input('method');
        $provider = $request->input('provider');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Payment::query()
            ->with(['transaction.user'])
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($qq) use ($q) {
                    $qq->where('provider_order_id', 'like', "%{$q}%")
                        ->orWhereHas('transaction', function ($tt) use ($q) {
                            $tt->where('invoice_number', 'like', "%{$q}%");
                        });
                });
            })
            ->when($status && in_array($status, array_column(PaymentStatus::cases(), 'value'), true), function ($w) use ($status) {
                $w->where('status', $status);
            })
            ->when($method && in_array($method, array_column(PaymentMethod::cases(), 'value'), true), function ($w) use ($method) {
                $w->where('method', $method);
            })
            ->when($provider, function ($w) use ($provider) {
                $w->where('provider', $provider);
            })
            ->when($from, function ($w) use ($from) {
                $w->whereDate('created_at', '>=', $from);
            })
            ->when($to, function ($w) use ($to) {
                $w->whereDate('created_at', '<=', $to);
            })
            ->orderByDesc('created_at')
            ->select(['id', 'transaction_id', 'method', 'provider', 'provider_order_id', 'status', 'amount', 'paid_at', 'created_at']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('invoice', function (Payment $p) {
                $inv = $p->transaction?->invoice_number ?? ('#' . $p->transaction_id);
                $url = route('transaksi.show', $p->transaction_id);
                return '<a href="' . e($url) . '">' . e($inv) . '</a>';
            })
            ->addColumn('cashier', function (Payment $p) {
                return e($p->transaction?->user?->name ?? '-');
            })
            ->addColumn('method_text', function (Payment $p) {
                $m = is_string($p->method) ? $p->method : ($p->method?->value ?? '');
                if ($m === 'cash_tempo') {
                    return 'TUNAI TEMPO';
                }
                return strtoupper($m);
            })
            ->addColumn('status_badge', function (Payment $p) {
                return view('partials.status-badge', ['status' => $p->status])->render();
            })
            ->editColumn('amount', function (Payment $p) {
                return 'Rp ' . number_format((float) $p->amount, 0, ',', '.');
            })
            ->addColumn('created', fn(Payment $p) => $p->created_at?->format('d/m/Y H:i'))
            ->addColumn('paid', fn(Payment $p) => $p->paid_at?->format('d/m/Y H:i') ?? '-')
            ->addColumn('action', function (Payment $p) {
                return view('payments.partials.action', ['p' => $p])->render();
            })
            ->rawColumns(['invoice', 'status_badge', 'action'])
            ->toJson();
    }

    public function destroy(Payment $payment): \Illuminate\Http\RedirectResponse
    {
        if (!Auth::check() || Auth::user()->role !== \App\Enums\RoleStatus::ADMIN->value) {
            abort(403, 'Hanya admin yang dapat menghapus pembayaran.');
        }

        $payment->delete();

        return back()->with('success', 'Data pembayaran berhasil dihapus.');
    }
}

