<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Settings\SettingsServiceInterface;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(private readonly SettingsServiceInterface $settings) {}

    public function receipt(Transaction $transaction): View
    {
        $transaction->loadMissing(['details.product', 'user']);

        return view('transactions.receipt', [
            'transaction'    => $transaction,
            'store_name'     => $this->settings->storeName(),
            'store_address'  => $this->settings->storeAddress(),
            'store_phone'    => $this->settings->storePhone(),
            'store_logo'     => $this->settings->storeLogoPath(),
            'currency'       => $this->settings->currency(),
            'discount_percent' => $this->settings->discountPercent(),
            'tax_percent'      => $this->settings->taxPercent(),
        ]);
    }
}
