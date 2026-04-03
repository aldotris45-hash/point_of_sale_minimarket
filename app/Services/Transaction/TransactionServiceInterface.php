<?php

namespace App\Services\Transaction;

use App\Models\Transaction;

interface TransactionServiceInterface
{
    /**
     * Process a tempo payment (mark as partially or fully paid).
     *
     * Updates amount_paid, creates Payment record & CashTransaction entry.
     *
     * @param  Transaction  $transaction
     * @param  float  $amount  The amount being paid
     * @param  int  $userId  The authenticated user performing the action
     * @return array{transaction: Transaction, paid: float}
     */
    public function markAsPaid(Transaction $transaction, float $amount, int $userId): array;

    /**
     * Delete a transaction and restore product stock.
     *
     * Performs soft-delete within a DB transaction. Restores stock only
     * for paid/pending transactions (not suspended).
     *
     * @param  Transaction  $transaction
     * @return void
     */
    public function deleteWithStockRestore(Transaction $transaction): void;
}
