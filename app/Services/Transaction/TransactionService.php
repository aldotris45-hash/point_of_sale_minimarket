<?php

namespace App\Services\Transaction;

use App\Enums\CashTransactionCategory;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService implements TransactionServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function markAsPaid(Transaction $transaction, float $amount, int $userId): array
    {
        $transaction->amount_paid += $amount;
        $transaction->change = max(0, $transaction->amount_paid - $transaction->total);

        if ($transaction->amount_paid >= $transaction->total) {
            $transaction->status = TransactionStatus::PAID;
        }

        $transaction->save();

        // Catat ke tabel payments agar muncul di halaman Pembayaran
        Payment::create([
            'transaction_id'    => $transaction->id,
            'method'            => PaymentMethod::CASH_TEMPO,
            'provider'          => 'manual',
            'provider_order_id' => $transaction->invoice_number,
            'status'            => PaymentStatus::SETTLEMENT,
            'amount'            => $amount,
            'paid_at'           => now(),
        ]);

        // Catat ke Buku Kas
        CashTransaction::create([
            'user_id'     => $userId,
            'type'        => 'in',
            'category'    => CashTransactionCategory::PELUNASAN_TEMPO->value,
            'date'        => now()->toDateString(),
            'amount'      => $amount,
            'description' => 'Pelunasan tempo #' . $transaction->invoice_number,
        ]);

        return [
            'transaction' => $transaction,
            'paid'        => $amount,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function deleteWithStockRestore(Transaction $transaction): void
    {
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
    }
}
