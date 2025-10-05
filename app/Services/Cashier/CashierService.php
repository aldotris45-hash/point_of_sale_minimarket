<?php

namespace App\Services\Cashier;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\Settings\SettingsServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CashierService implements CashierServiceInterface
{
    public function __construct(private readonly SettingsServiceInterface $settings) {}

    public function checkout(array $items, string $paymentMethod, float $paidAmount = 0, ?string $note = null): Transaction
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Keranjang kosong.');
        }

        $method = PaymentMethod::tryFrom($paymentMethod) ?? PaymentMethod::CASH;

        return DB::transaction(function () use ($items, $method, $paidAmount, $note) {
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
                ];
            }

            $discountPercent = (float) $this->settings->discountPercent();
            $taxPercent = (float) $this->settings->taxPercent();
            $discountAmount = $subtotal * ($discountPercent / 100);
            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = $afterDiscount * ($taxPercent / 100);
            $total = $afterDiscount + $taxAmount;

            if ($method === PaymentMethod::CASH && $paidAmount < $total) {
                throw new InvalidArgumentException('Nominal bayar kurang dari total.');
            }

            $trx = Transaction::create([
                'user_id' => Auth::id(),
                'invoice_number' => 'TEMP',
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'tax' => $taxAmount,
                'total' => $total,
                'amount_paid' => $method === PaymentMethod::CASH ? $paidAmount : 0,
                'change' => $method === PaymentMethod::CASH ? max(0, $paidAmount - $total) : 0,
                'payment_method' => $method,
                'status' => TransactionStatus::PAID,
            ]);

            // Nomor invoice berdasarkan format pengaturan
            $format = $this->settings->receiptNumberFormat();
            $invoice = $this->generateInvoiceNumber($trx->id, $format);
            $trx->update(['invoice_number' => $invoice]);

            foreach ($built as $b) {
                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    ...$b,
                ]);
                Product::whereKey($b['product_id'])->decrement('stock', (int) $b['quantity']);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'checkout',
                'description' => 'Transaksi kasir #' . $trx->invoice_number,
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
