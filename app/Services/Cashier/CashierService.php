<?php

namespace App\Services\Cashier;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Enums\CashTransactionCategory;
use App\Models\ActivityLog;
use App\Models\CashTransaction;
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

    public function checkout(array $items, string $paymentMethod, float $paidAmount = 0, ?string $note = null, ?int $suspendedFromId = null, ?int $customerId = null, ?string $transactionDate = null): Transaction
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Keranjang kosong.');
        }

        $method = PaymentMethod::tryFrom($paymentMethod) ?? PaymentMethod::CASH;

        return DB::transaction(function () use ($items, $method, $paidAmount, $note, $suspendedFromId, $customerId, $transactionDate) {
            $subtotal = 0.0;
            $built = [];

            foreach ($items as $row) {
                $pid      = (int) ($row['product_id'] ?? 0);
                $saleUnit = $row['sale_unit'] ?? null;           // 'kg','botol' atau 'krat','karton'
                $saleQty  = (float) ($row['qty'] ?? 0);          // bisa desimal untuk kg
                $isBulk   = (bool) ($row['is_bulk'] ?? false);  // penjualan grosir?

                if ($pid <= 0 || $saleQty <= 0) {
                    throw new InvalidArgumentException('Item keranjang tidak valid.');
                }

                $product = Product::lockForUpdate()->findOrFail($pid);

                // Tentukan satuan jual default jika tidak dikirim
                if (! $saleUnit) {
                    $saleUnit = $isBulk ? ($product->bulk_unit ?? $product->unit) : $product->unit;
                }

                // Konversi ke base unit untuk validasi & deduct stok
                $qtyInBaseUnit = $product->toBaseUnit($saleQty, $saleUnit);

                if ((float) $product->stock < $qtyInBaseUnit) {
                    throw new InvalidArgumentException(
                        "Stok tidak mencukupi untuk {$product->name}. " .
                        "Stok: {$product->stockDisplay()}"
                    );
                }

                // Tentukan harga
                $isPromo         = $product->isOnPromo() && ! $isBulk;
                $unitPrice       = $product->priceFor($saleUnit);
                $itemDiscount    = (float) ($row['item_discount'] ?? 0);
                $effectivePrice  = max(0, $unitPrice - $itemDiscount);
                $line            = $effectivePrice * $saleQty;
                $subtotal       += $line;

                // Promo grosir (bulk promo label)
                $promoBulkLabel  = $row['promo_bulk_label'] ?? null;

                $built[] = [
                    'product_id'       => $product->id,
                    'price'            => $unitPrice,
                    'item_discount'    => $itemDiscount,
                    'is_promo'         => $isPromo || ($isBulk && $promoBulkLabel),
                    'original_price'   => $isPromo ? (float) $product->price : null,
                    'quantity'         => $saleQty,  // qty dalam satuan jual (bisa decimal)
                    'sale_unit'        => $saleUnit,
                    'sale_qty'         => $saleQty,
                    'qty_in_base_unit' => $qtyInBaseUnit,
                    'is_bulk_sale'     => $isBulk,
                    'promo_bulk_label' => $promoBulkLabel,
                    'total'            => $line,
                    '_product'         => $product,
                    '_qty_deduct'      => $qtyInBaseUnit, // untuk deduct stok
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
            'created_at' => $transactionDate ?? now(),
            'updated_at' => $transactionDate ?? now(),
        ]);

            $format = $this->settings->receiptNumberFormat();
            $invoice = $this->generateInvoiceNumber($trx->id, $format, $transactionDate ? \Carbon\Carbon::parse($transactionDate) : null);
            $trx->update(['invoice_number' => $invoice]);

            foreach ($built as $b) {
                $product = $b['_product'];

                $qtyDeduct = $b['_qty_deduct'] ?? $b['qty_in_base_unit'] ?? $b['quantity'];
                unset($b['_product'], $b['_qty_deduct']); // jangan simpan ke DB

                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    ...$b,
                ]);

                // Deduct stok dalam base unit (presisi untuk kg & pcs)
                $newStock = max(0, (float) $product->stock - (float) $qtyDeduct);
                $product->update(['stock' => $newStock]);

                // Refresh & cek alert stok minimum
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

            // Catat ke Buku Kas jika transaksi langsung lunas
            if ($status === TransactionStatus::PAID && $amountPaid > 0) {
                CashTransaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'category' => CashTransactionCategory::PENJUALAN->value,
                    'date' => $transactionDate ?? now()->toDateString(),
                    'amount' => $total,
                    'description' => 'Penjualan #' . $trx->invoice_number,
                ]);
            }

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
                $pid      = (int) ($row['product_id'] ?? 0);
                $saleQty  = (float) ($row['qty'] ?? 0);  // float untuk support kg desimal
                $saleUnit = $row['sale_unit'] ?? null;
                $isBulk   = (bool) ($row['is_bulk'] ?? false);

                if ($pid <= 0 || $saleQty <= 0) {
                    throw new InvalidArgumentException('Item keranjang tidak valid.');
                }

                $product = Product::findOrFail($pid);

                // Tentukan satuan jual
                if (! $saleUnit) {
                    $saleUnit = $isBulk ? ($product->bulk_unit ?? $product->unit) : $product->unit;
                }

                $isPromo   = $product->isOnPromo() && ! $isBulk;
                $unitPrice = $product->priceFor($saleUnit);
                $itemDiscount = (float) ($row['item_discount'] ?? 0);
                $effectivePrice = max(0, $unitPrice - $itemDiscount);
                $line      = $effectivePrice * $saleQty;
                $subtotal += $line;

                // Konversi ke base unit
                $qtyInBaseUnit = $product->toBaseUnit($saleQty, $saleUnit);

                $built[] = [
                    'product_id'       => $product->id,
                    'price'            => $unitPrice,
                    'item_discount'    => $itemDiscount,
                    'is_promo'         => $isPromo,
                    'original_price'   => $isPromo ? (float) $product->price : null,
                    'quantity'         => $saleQty,
                    'sale_unit'        => $saleUnit,
                    'sale_qty'         => $saleQty,
                    'qty_in_base_unit' => $qtyInBaseUnit,
                    'is_bulk_sale'     => $isBulk,
                    'total'            => $line,
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

    public function generateInvoiceNumber(int $transactionId, string $format, ?\Carbon\Carbon $date = null): string
    {
        $dt = $date ?? now();
        $map = [
            '{YYYY}' => $dt->format('Y'),
            '{YY}' => $dt->format('y'),
            '{MM}' => $dt->format('m'),
            '{DD}' => $dt->format('d'),
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
