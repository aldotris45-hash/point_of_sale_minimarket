<?php

namespace App\Services\Cashier;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\Settings\SettingsServiceInterface;
use App\Services\Product\ProductAlertService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CashierService implements CashierServiceInterface
{
    public function __construct(
        private readonly SettingsServiceInterface $settings,
    ) {}

    public function checkout(array $items, string $paymentMethod, float $paidAmount = 0, ?string $note = null, ?int $suspendedFromId = null, ?int $customerId = null): Transaction
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Keranjang kosong.');
        }

        $method = PaymentMethod::tryFrom($paymentMethod) ?? PaymentMethod::CASH;

        return DB::transaction(function () use ($items, $method, $paidAmount, $note, $suspendedFromId, $customerId) {
            $subtotal = 0.0;
            $built = [];

            foreach ($items as $row) {
                $pid = (int) ($row['product_id'] ?? 0);
                $qty = (int) ($row['qty'] ?? 0);
                if ($pid <= 0 || $qty <= 0) {
                    throw new InvalidArgumentException('Item keranjang tidak valid.');
                }

                $product = Product::lockForUpdate()->findOrFail($pid);
                if ($product->stock < $qty) {
                    throw new InvalidArgumentException("Stok tidak mencukupi untuk {$product->name}.");
                }

                $line = (float) $product->price * $qty;
                $subtotal += $line;
                $built[] = [
                    'product_id' => $product->id,
                    'price' => (float) $product->price,
                    'quantity' => $qty,
                    'total' => $line,
                    '_product' => $product, // simpan instance untuk dipakai nanti
                ];
            }

            $discountPercent = $this->settings->discountPercent();
            $taxPercent = $this->settings->taxPercent();

            $discountAmount = $subtotal * ($discountPercent / 100);
            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = $afterDiscount * ($taxPercent / 100);
            $total = $afterDiscount + $taxAmount;

            // for normal cash we must have paid >= total; tempo can be less
        if ($method === PaymentMethod::CASH && $paidAmount < $total) {
            throw new InvalidArgumentException('Nominal bayar kurang dari total.');
        }

        // determine fields that differ depending on method
        $amountPaid = 0.0;
        $change = 0.0;
        $status = TransactionStatus::PENDING;

        if ($method === PaymentMethod::CASH) {
            $amountPaid = $paidAmount;
            $change = max(0, $paidAmount - $total);
            $status = TransactionStatus::PAID;
        } elseif ($method === PaymentMethod::CASH_TEMPO) {
            // customer will settle later; record whatever was paid (could be 0 or a partial amount)
            $amountPaid = $paidAmount;
            $change = max(0, $paidAmount - $total);
            $status = $paidAmount >= $total ? TransactionStatus::PAID : TransactionStatus::PENDING;
        }

        $trx = Transaction::create([
            'user_id' => Auth::id(),
            'customer_id' => $customerId,
            'invoice_number' => 'TEMP',
            'note' => $note,
            'suspended_from_id' => $suspendedFromId,
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'tax' => $taxAmount,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'change' => $change,
            'payment_method' => $method,
            'status' => $status,
        ]);

            $format = $this->settings->receiptNumberFormat();
            $invoice = $this->generateInvoiceNumber($trx->id, $format);
            $trx->update(['invoice_number' => $invoice]);

            foreach ($built as $b) {
                $product = $b['_product'];
                unset($b['_product']); // jangan simpan ke DB

                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    ...$b,
                ]);
                Product::whereKey($b['product_id'])->decrement('stock', (int) $b['quantity']);

                // Gunakan instance yang sudah ada, refresh untuk stok terbaru
                $product->refresh();
                app(ProductAlertService::class)->checkAndNotifyForProduct($product, $this->settings->expiryAlertDays());
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'checkout',
                'description' => 'Transaksi kasir #' . $trx->invoice_number,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);



            if ($suspendedFromId && in_array($method, [PaymentMethod::CASH, PaymentMethod::CASH_TEMPO], true)) {
                $original = Transaction::where('id', $suspendedFromId)
                    ->where('status', TransactionStatus::SUSPENDED)
                    ->first();
                if ($original) {
                    $original->delete();
                }
            }

            return $trx;
        });
    }

    public function hold(array $items, ?string $note = null, ?int $suspendedId = null, ?int $customerId = null): Transaction
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Keranjang kosong.');
        }

        return DB::transaction(function () use ($items, $note, $suspendedId, $customerId) {
            $subtotal = 0.0;
            $built = [];

            foreach ($items as $row) {
                $pid = (int) ($row['product_id'] ?? 0);
                $qty = (int) ($row['qty'] ?? 0);
                if ($pid <= 0 || $qty <= 0) {
                    throw new InvalidArgumentException('Item keranjang tidak valid.');
                }

                $product = Product::findOrFail($pid); // No lock/stock decrement for hold
                $line = (float) $product->price * $qty;
                $subtotal += $line;
                $built[] = [
                    'product_id' => $product->id,
                    'price' => (float) $product->price,
                    'quantity' => $qty,
                    'total' => $line,
                ];
            }

            $discountPercent = $this->settings->discountPercent();
            $taxPercent = $this->settings->taxPercent();

            $discountAmount = $subtotal * ($discountPercent / 100);
            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = $afterDiscount * ($taxPercent / 100);
            $total = $afterDiscount + $taxAmount;

            // If suspendedId provided and belongs to current user and is suspended, update it; else create new
            if ($suspendedId) {
                $trx = Transaction::where('id', $suspendedId)
                    ->where('user_id', Auth::id())
                    ->where('status', TransactionStatus::SUSPENDED)
                    ->first();
            } else {
                $trx = null;
            }

            if ($trx) {
                // Update header
                $trx->update([
                    'note' => $note,
                    'customer_id' => $customerId,
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'total' => $total,
                ]);
                // Replace details
                $trx->details()->delete();
                foreach ($built as $b) {
                    TransactionDetail::create([
                        'transaction_id' => $trx->id,
                        ...$b,
                    ]);
                }
            } else {
                $trx = Transaction::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $customerId,
                    'invoice_number' => 'TEMP',
                    'note' => $note,
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'total' => $total,
                    'amount_paid' => 0,
                    'change' => 0,
                    'payment_method' => PaymentMethod::CASH,
                    'status' => TransactionStatus::SUSPENDED,
                ]);

                // Generate a different pattern for hold to make it recognizable
                $format = $this->settings->receiptNumberFormat();
                $invoice = 'HOLD-' . $this->generateInvoiceNumber($trx->id, $format);
                $trx->update(['invoice_number' => $invoice]);

                foreach ($built as $b) {
                    TransactionDetail::create([
                        'transaction_id' => $trx->id,
                        ...$b,
                    ]);
                }
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'hold',
                'description' => 'Tunda Transaksi #' . $trx->invoice_number,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $trx;
        });
    }

    public function generateInvoiceNumber(int $transactionId, string $format): string
    {
        $now = now();
        $map = [
            '{YYYY}' => $now->format('Y'),
            '{YY}' => $now->format('y'),
            '{MM}' => $now->format('m'),
            '{DD}' => $now->format('d'),
        ];
        $result = strtr($format, $map);
        $seqWidth = $this->extractSeqWidth($format) ?? 6;
        $seqPad = str_pad((string) $transactionId, $seqWidth, '0', STR_PAD_LEFT);
        return (string) preg_replace('/\{SEQ:\d{1,9}\}/', $seqPad, $result) ?: $result;
    }

    private function extractSeqWidth(string $format): ?int
    {
        if (preg_match('/\{SEQ:(\d{1,9})\}/', $format, $m)) {
            return (int) $m[1];
        }
        return null;
    }
}
