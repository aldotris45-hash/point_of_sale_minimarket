<?php

namespace Database\Seeders;

use App\Services\Settings\SettingsServiceInterface;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var SettingsServiceInterface $settings */
        $settings = app(SettingsServiceInterface::class);

        $settings->set('store.name', 'POS Mutiara Kasih', 'store', 'Nama toko yang tampil di aplikasi');
        $settings->set('pos.currency', 'IDR', 'pos', 'Mata uang transaksi');
        $settings->set('pos.tax_percent', 11, 'pos', 'PPN dalam persen');
        $settings->set('pos.discount_percent', 0, 'pos', 'Diskon default dalam persen');
    }
}
