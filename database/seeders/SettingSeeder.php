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
        $settings->set('store.address', 'Jl. Batu Buil, Kecamatan Belimbing Hulu, Kabupaten Melawi, Kalimantan Barat', 'store', 'Alamat Toko');
        $settings->set('store.phone', '081234567890', 'store', 'No. Telepon Toko');
        $settings->set('store.logo_path', 'assets/images/logo.webp', 'store', 'Path Logo Toko');
        $settings->set('pos.currency', 'IDR', 'pos', 'Mata uang transaksi');
        $settings->set('pos.tax_percent', 11, 'pos', 'PPN dalam persen');
        $settings->set('pos.discount_percent', 0, 'pos', 'Diskon default dalam persen');
        $settings->set('pos.receipt_format', 'INV-{YYYY}{MM}{DD}-{SEQ:6}', 'pos', 'Format Penomoran Struk');
        $settings->set('pos.low_stock_threshold', 5, 'pos', 'Batas stok rendah (notifikasi)');
        $settings->set('pos.expiry_alert_days', 7, 'pos', 'Jumlah hari sebelum kadaluarsa untuk notifikasi');
    }
}
