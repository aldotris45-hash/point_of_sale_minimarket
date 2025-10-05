<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Transaction $transaction): View
    {
        $payment = Payment::where('transaction_id', $transaction->id)->latest()->firstOrFail();
        return view('payments.show', compact('transaction', 'payment'));
    }

    public function status(Transaction $transaction): JsonResponse
    {
        $transaction->loadMissing('latestPayment');
        $pay = $transaction->latestPayment;

        $status = $pay?->status?->value
            ?? $transaction->status?->value
            ?? 'pending';

        return response()->json([
            'transaction_id' => $transaction->id,
            'invoice' => $transaction->invoice_number,
            'status' => $status,
            'paid' => in_array($status, [
                PaymentStatus::SETTLEMENT->value,
                TransactionStatus::PAID->value,
            ], true),
        ]);
    }

    public function complete(Transaction $transaction)
    {
        // Flash data untuk modal cetak di halaman kasir
        $method = strtolower((string) ($transaction->payment_method?->value ?? $transaction->payment_method ?? ''));
        return redirect()->route('kasir')
            ->with('success', 'Transaksi berhasil. Nomor: ' . $transaction->invoice_number)
            ->with('printed_transaction_id', $transaction->id)
            ->with('printed_invoice', $transaction->invoice_number)
            ->with('printed_payment_method', $method);
    }
}
