<?php

namespace App\Services\Pdf;

use App\Models\Transaction;
use Barryvdh\DomPDF\PDF;

interface InvoicePdfServiceInterface
{
    /**
     * Build view data array for receipt/invoice/faktur (both HTML preview and PDF).
     *
     * @param  Transaction  $transaction
     * @param  bool  $withStamp      Include rotated stamp image
     * @param  bool  $withSignature  Include signature image
     * @param  bool  $withTerbilang  Include amount-in-words
     * @return array<string, mixed>
     */
    public function buildViewData(
        Transaction $transaction,
        bool $withStamp = false,
        bool $withSignature = false,
        bool $withTerbilang = false,
    ): array;

    /**
     * Generate a receipt PDF (80mm thermal receipt).
     */
    public function receiptPdf(Transaction $transaction, bool $withStamp = false, bool $withSignature = false): PDF;

    /**
     * Generate an invoice PDF (A4 landscape).
     */
    public function invoicePdf(Transaction $transaction, bool $withStamp = false, bool $withSignature = false): PDF;

    /**
     * Generate a faktur PDF (A4 landscape).
     */
    public function fakturPdf(Transaction $transaction, bool $withStamp = false, bool $withSignature = false): PDF;
}
