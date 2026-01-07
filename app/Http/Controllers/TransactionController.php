<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\Settings\SettingsServiceInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function __construct(private readonly SettingsServiceInterface $settings) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $method = $request->query('method');
        $due = $request->query('due');
        $cashier = $request->query('cashier');
        $from = $request->query('from');
        $to = $request->query('to');

        $statuses = array_values(array_filter(TransactionStatus::cases(), fn($s) => $s->value !== 'suspended'));

        return view('transactions.index', [
            'q' => $q,
            'status' => $status,
            'method' => $method,
            'due' => $due,
            'cashier' => $cashier,
            'from' => $from,
            'to' => $to,
            'currency' => $this->settings->currency(),
            'statuses' => $statuses,
            'methods' => PaymentMethod::cases(),
        ]);
    }

    public function data(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $method = $request->input('method');
        $due = $request->input('due');
        $cashier = $request->input('cashier');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Transaction::query()
            ->with(['user'])
            ->where('status', '!=', TransactionStatus::SUSPENDED->value)
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($qq) use ($q) {
                    $qq->where('invoice_number', 'like', "%{$q}%")
                        ->orWhere('note', 'like', "%{$q}%");
                });
            })
            ->when($status && in_array($status, array_column(TransactionStatus::cases(), 'value'), true), function ($w) use ($status) {
                $w->where('status', $status);
            })
            ->when($method && in_array($method, array_column(PaymentMethod::cases(), 'value'), true), function ($w) use ($method) {
                $w->where('payment_method', $method);
            })
            ->when($due === 'utang', function ($w) {
                $w->where('payment_method', PaymentMethod::CASH_TEMPO->value)
                  ->whereColumn('amount_paid', '<', 'total');
            })
            ->when($due === 'lunas', function ($w) {
                $w->where('payment_method', PaymentMethod::CASH_TEMPO->value)
                  ->whereColumn('amount_paid', '>=', 'total');
            })
            ->when($cashier && ctype_digit((string) $cashier), function ($w) use ($cashier) {
                $w->where('user_id', (int) $cashier);
            })
            ->when($from, function ($w) use ($from) {
                $w->whereDate('created_at', '>=', $from);
            })
            ->when($to, function ($w) use ($to) {
                $w->whereDate('created_at', '<=', $to);
            })
            ->orderByDesc('created_at')
            ->select(['id', 'user_id', 'invoice_number', 'payment_method', 'status', 'total', 'created_at']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('invoice', function (Transaction $t) {
                $url = route('transaksi.show', $t);
                return '<a href="' . e($url) . '">' . e($t->invoice_number) . '</a>';
            })
            ->addColumn('date', function (Transaction $t) {
                return $t->created_at?->format('d/m/Y H:i');
            })
            ->addColumn('cashier', function (Transaction $t) {
                return e($t->user->name ?? '-');
            })
            ->addColumn('method', function (Transaction $t) {
                $m = is_string($t->payment_method) ? $t->payment_method : ($t->payment_method?->value ?? '');
                // humanize special code
                if ($m === 'cash_tempo') {
                    return 'TUNAI TEMPO';
                }
                return strtoupper($m);
            })
            ->addColumn('due_badge', function (Transaction $t) {
                $m = is_string($t->payment_method) ? $t->payment_method : ($t->payment_method?->value ?? '');
                if ($m === 'cash_tempo') {
                    if ($t->amount_paid < $t->total) {
                        return '<span class="badge bg-danger">UTANG</span>';
                    }
                    return '<span class="badge bg-success">LUNAS</span>';
                }
                return '';
            })
            ->addColumn('status_badge', function (Transaction $t) {
                $s = is_string($t->status) ? $t->status : ($t->status?->value ?? '');
                $class = $s === 'paid' ? 'bg-success' : ($s === 'pending' ? 'bg-warning text-dark' : 'bg-secondary');
                return '<span class="badge ' . $class . '">' . strtoupper($s) . '</span>';
            })
            ->editColumn('total', function (Transaction $t) {
                return 'Rp ' . number_format((float) $t->total, 0, ',', '.');
            })
            ->addColumn('action', function (Transaction $t) {
                $showUrl = route('transaksi.show', $t);
                $receiptUrl = route('transaksi.struk', $t);
                return '<div class="d-flex justify-content-end gap-1">'
                    . '<a class="btn btn-sm btn-outline-primary" href="' . e($showUrl) . '"><i class="bi bi-eye"></i></a>'
                    . '<a class="btn btn-sm btn-outline-secondary" href="' . e($receiptUrl) . '" target="_blank" rel="noopener noreferrer"><i class="bi bi-receipt-cutoff"></i></a>'
                    . '</div>';
            })
            ->rawColumns(['invoice', 'status_badge', 'action'])
            ->toJson();
    }

    public function show(Transaction $transaction): View
    {
        $transaction->loadMissing(['details.product', 'user', 'latestPayment']);
        return view('transactions.show', [
            'trx' => $transaction,
            'currency' => $this->settings->currency(),
        ]);
    }

    /**
     * Mark a cash_tempo transaction as paid (accepts additional amount).
     */
    public function markAsPaid(Transaction $transaction)
    {
        if (($transaction->payment_method?->value ?? $transaction->payment_method) !== PaymentMethod::CASH_TEMPO->value) {
            abort(400, 'Hanya transaksi tunai tempo yang bisa dilunasi di sini.');
        }

        if ($transaction->amount_paid >= $transaction->total) {
            return back()->with('error', 'Transaksi sudah lunas.');
        }

        $data = request()->validate([
            'paid_amount' => ['required','numeric','min:0'],
        ]);

        $paid = (float) $data['paid_amount'];
        $transaction->amount_paid += $paid;
        $transaction->change = max(0, $transaction->amount_paid - $transaction->total);
        if ($transaction->amount_paid >= $transaction->total) {
            $transaction->status = TransactionStatus::PAID;
        }
        $transaction->save();

        return back()->with('success', 'Pembayaran dicatat.' .
            ($transaction->change > 0 ? ' Kembalian: ' . number_format($transaction->change, 0, ',', '.') : '') );
    }

    public function receipt(Transaction $transaction): View
    {
        $transaction->loadMissing(['details.product', 'user']);

        return view('transactions.receipt', [
            'transaction'    => $transaction,
            'store_name'     => $this->settings->storeName(),
            'store_address'  => $this->settings->storeAddress(),
            'store_phone'    => $this->settings->storePhone(),
            'store_bank_account' => $this->settings->storeBankAccount(),
            'store_logo'     => $this->settings->storeLogoPath(),
            'currency'       => $this->settings->currency(),
            'discount_percent' => $this->settings->discountPercent(),
            'tax_percent'      => $this->settings->taxPercent(),
        ]);
    }
}
