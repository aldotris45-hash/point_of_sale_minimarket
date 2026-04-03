<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RoleStatus;
use App\Enums\TransactionStatus;
use App\Enums\CashTransactionCategory;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\ActivityLog\ActivityLoggerInterface;
use App\Services\Settings\SettingsServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Helpers\Terbilang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function __construct(
        private readonly SettingsServiceInterface $settings,
        private readonly ActivityLoggerInterface $logger,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $method = $request->query('method');
        $due = $request->query('due');
        $cashier = $request->query('cashier');
        $customerId = $request->query('customer_id');
        $from = $request->query('from');
        $to = $request->query('to');

        $statuses = array_values(array_filter(TransactionStatus::cases(), fn($s) => $s->value !== 'suspended'));

        return view('transactions.index', [
            'q' => $q,
            'status' => $status,
            'method' => $method,
            'due' => $due,
            'cashier' => $cashier,
            'customer_id' => $customerId,
            'from' => $from,
            'to' => $to,
            'currency' => $this->settings->currency(),
            'statuses' => $statuses,
            'methods' => PaymentMethod::cases(),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function data(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $method = $request->input('method');
        $due = $request->input('due');
        $cashier = $request->input('cashier');
        $customerId = $request->input('customer_id');
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
            ->when($customerId && ctype_digit((string) $customerId), function ($w) use ($customerId) {
                $w->where('customer_id', (int) $customerId);
            })
            ->when($from, function ($w) use ($from) {
                $w->whereDate('created_at', '>=', $from);
            })
            ->when($to, function ($w) use ($to) {
                $w->whereDate('created_at', '<=', $to);
            })
            ->orderByDesc('created_at')
            ->select(['id', 'user_id', 'customer_id', 'invoice_number', 'payment_method', 'status', 'total', 'created_at']);

        // Hitung total dari SEMUA data yang terfilter (bukan hanya halaman saat ini)
        $grandTotal = (clone $query)->sum('total');

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
                if ($m === 'cash_tempo') {
                    return 'TUNAI TEMPO';
                }
                return strtoupper($m);
            })
            ->addColumn('due_badge', function (Transaction $t) {
                return view('transactions.partials.due-badge', ['t' => $t])->render();
            })
            ->addColumn('status_badge', function (Transaction $t) {
                return view('partials.status-badge', ['status' => $t->status])->render();
            })
            ->editColumn('total', function (Transaction $t) {
                return 'Rp ' . number_format((float) $t->total, 0, ',', '.');
            })
            ->addColumn('action', function (Transaction $t) {
                return view('transactions.partials.action', ['t' => $t])->render();
            })
            ->rawColumns(['invoice', 'status_badge', 'due_badge', 'action'])
            ->with('grand_total', (float) $grandTotal)
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

        // Catat ke tabel payments agar muncul di halaman Pembayaran
        Payment::create([
            'transaction_id' => $transaction->id,
            'method'         => PaymentMethod::CASH_TEMPO,
            'provider'       => 'manual',
            'provider_order_id' => $transaction->invoice_number,
            'status'         => PaymentStatus::SETTLEMENT,
            'amount'         => $paid,
            'paid_at'        => now(),
        ]);

        // Catat ke Buku Kas
        CashTransaction::create([
            'user_id' => auth()->id(),
            'type' => 'in',
            'category' => CashTransactionCategory::PELUNASAN_TEMPO->value,
            'date' => now()->toDateString(),
            'amount' => $paid,
            'description' => 'Pelunasan tempo #' . $transaction->invoice_number,
        ]);

        $this->logger->log('Pelunasan Tempo', 'Pembayaran tempo dicatat', [
            'transaction_id' => $transaction->id,
            'invoice' => $transaction->invoice_number,
            'amount' => $paid,
        ]);

        return back()->with('success', 'Pembayaran dicatat.' .
            ($transaction->change > 0 ? ' Kembalian: ' . number_format($transaction->change, 0, ',', '.') : '') );
    }

    /**
     * Delete a transaction (admin only). Restores product stock.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        // Guard: only admin
        if (!Auth::check() || Auth::user()->role !== RoleStatus::ADMIN->value) {
            abort(403, 'Hanya admin yang dapat menghapus transaksi.');
        }

        $invoiceNumber = $transaction->invoice_number;

        DB::transaction(function () use ($transaction) {
            // Restore stock for each detail (only if transaction was paid/pending, not suspended)
            $statusVal = $transaction->status->value ?? $transaction->status;
            if (in_array($statusVal, ['paid', 'pending'])) {
                foreach ($transaction->details as $detail) {
                    Product::whereKey($detail->product_id)
                        ->increment('stock', (int) $detail->quantity);
                }
            }

            // Soft delete — data tetap tersimpan untuk audit trail
            $transaction->delete();
        });

        $this->logger->log('Hapus Transaksi', 'Transaksi dihapus oleh admin (soft delete)', [
            'transaction_id' => $transaction->id,
            'invoice' => $invoiceNumber,
        ]);

        return redirect()->route('transaksi')
            ->with('success', "Transaksi {$invoiceNumber} berhasil dihapus dan stok dikembalikan.");
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

    /**
     * Print Invoice (formal invoice document).
     */
    public function printInvoice(Transaction $transaction, Request $request): View
    {
        $transaction->loadMissing(['details.product', 'user', 'customer']);

        return view('transactions.print-invoice', [
            'transaction'       => $transaction,
            'store_name'        => $this->settings->storeName(),
            'store_address'     => $this->settings->storeAddress(),
            'store_phone'       => $this->settings->storePhone(),
            'store_bank_account' => $this->settings->storeBankAccount(),
            'store_logo'        => $this->settings->storeLogoPath(),
            'currency'          => $this->settings->currency(),
            'discount_percent'  => $this->settings->discountPercent(),
            'tax_percent'       => $this->settings->taxPercent(),
            'terbilang'         => Terbilang::rupiah((float) $transaction->total),
            'with_signature'    => $request->query('signature') == 1,
            'with_stamp'        => $request->query('stamp') == 1,
            'store_signature'   => $this->settings->storeSignaturePath(),
            'store_stamp'       => $this->settings->storeStampPath(),
        ]);
    }

    /**
     * Print Faktur Penjualan (sales receipt document).
     */
    public function printFaktur(Transaction $transaction, Request $request): View
    {
        $transaction->loadMissing(['details.product', 'user', 'customer']);

        return view('transactions.print-faktur', [
            'transaction'       => $transaction,
            'store_name'        => $this->settings->storeName(),
            'store_address'     => $this->settings->storeAddress(),
            'store_phone'       => $this->settings->storePhone(),
            'store_bank_account' => $this->settings->storeBankAccount(),
            'store_logo'        => $this->settings->storeLogoPath(),
            'currency'          => $this->settings->currency(),
            'discount_percent'  => $this->settings->discountPercent(),
            'tax_percent'       => $this->settings->taxPercent(),
            'terbilang'         => Terbilang::rupiah((float) $transaction->total),
            'with_signature'    => $request->query('signature') == 1,
            'with_stamp'        => $request->query('stamp') == 1,
            'store_signature'   => $this->settings->storeSignaturePath(),
            'store_stamp'       => $this->settings->storeStampPath(),
        ]);
    }

    // ── PDF Downloads ──────────────────────────────────────────────

    /**
     * Shared helper: gather common receipt/invoice data.
     */
    private function pdfData(Transaction $transaction, Request $request, bool $withTerbilang = false): array
    {
        $transaction->loadMissing(['details.product', 'user', 'customer']);

        // Resolve absolute file paths for DomPDF (bypasses symlinks)
        $resolvePath = function (?string $relativePath): ?string {
            if (empty($relativePath)) return null;
            // Path stored as 'storage/assets/images/xxx.png' → actual file at 'storage/app/public/assets/images/xxx.png'
            $stripped = str_replace('storage/', '', $relativePath);
            $absolute = storage_path('app/public/' . $stripped);
            return file_exists($absolute) ? $absolute : null;
        };

        $data = [
            'transaction'        => $transaction,
            'store_name'         => $this->settings->storeName(),
            'store_address'      => $this->settings->storeAddress(),
            'store_phone'        => $this->settings->storePhone(),
            'store_bank_account' => $this->settings->storeBankAccount(),
            'store_logo'         => $this->settings->storeLogoPath(),
            'currency'           => $this->settings->currency(),
            'discount_percent'   => $this->settings->discountPercent(),
            'tax_percent'        => $this->settings->taxPercent(),
            'with_signature'    => true,
            'with_stamp'        => true,
            'store_signature'   => $this->settings->storeSignaturePath(),
            'store_stamp'       => $this->settings->storeStampPath(),
            'is_pdf'            => true,
            // Resolved absolute paths for DomPDF
            'pdf_logo_path'      => $resolvePath($this->settings->storeLogoPath()),
            'pdf_signature_path' => $resolvePath($this->settings->storeSignaturePath()),
            'pdf_stamp_path'     => $resolvePath($this->settings->storeStampPath()),
        ];

        if ($withTerbilang) {
            $data['terbilang'] = Terbilang::rupiah((float) $transaction->total);
        }

        return $data;
    }

    /**
     * Download struk as PDF (80mm thermal receipt width).
     */
    public function receiptPdf(Transaction $transaction, Request $request)
    {
        $data = $this->pdfData($transaction, $request);

        $pdf = Pdf::loadView('transactions.receipt', $data)
            ->setPaper([0, 0, 226.77, 841.89], 'portrait') // ~80mm x ~297mm
            ->setOption(['isRemoteEnabled' => true]);

        return $pdf->download("struk-{$transaction->invoice_number}.pdf");
    }

    /**
     * Download invoice as PDF (A4).
     */
    public function invoicePdf(Transaction $transaction, Request $request)
    {
        $data = $this->pdfData($transaction, $request, true);

        // Debug log for image paths
        \Log::info('Invoice PDF paths', [
            'with_stamp' => $data['with_stamp'],
            'with_signature' => $data['with_signature'],
            'store_stamp' => $data['store_stamp'],
            'store_signature' => $data['store_signature'],
            'pdf_stamp_path' => $data['pdf_stamp_path'],
            'pdf_signature_path' => $data['pdf_signature_path'],
            'pdf_logo_path' => $data['pdf_logo_path'],
        ]);

        $pdf = Pdf::loadView('transactions.print-invoice', $data)
            ->setPaper('a4', 'landscape')
            ->setOption(['isRemoteEnabled' => true]);

        return $pdf->download("invoice-{$transaction->invoice_number}.pdf");
    }

    /**
     * Download faktur as PDF (A4).
     */
    public function fakturPdf(Transaction $transaction, Request $request)
    {
        $data = $this->pdfData($transaction, $request, true);

        $pdf = Pdf::loadView('transactions.print-faktur', $data)
            ->setPaper('a4', 'landscape')
            ->setOption(['isRemoteEnabled' => true]);

        return $pdf->download("faktur-{$transaction->invoice_number}.pdf");
    }
}

