<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Minuman', 'description' => 'Minuman berbagai macam untuk menemani Anda'],
            ['name' => 'Makanan Ringan', 'description' => 'Camilan dan makanan ringan yang lezat'],
            ['name' => 'Mie & Instan', 'description' => 'Mie instan dan produk siap saji'],
            ['name' => 'Roti & Kue', 'description' => 'Roti, kue, dan produk bakery'],
            ['name' => 'Susu & Olahan', 'description' => 'Susu dan produk olahan susu'],
            ['name' => 'Bahan Pokok', 'description' => 'Bahan makanan pokok sehari-hari'],
            ['name' => 'Bumbu & Saus', 'description' => 'Bumbu dapur dan saus masakan'],
            ['name' => 'Kopi & Teh', 'description' => 'Kopi dan teh premium'],
            ['name' => 'Perawatan Pribadi', 'description' => 'Produk perawatan dan kebersihan pribadi'],
            ['name' => 'Kebersihan Rumah', 'description' => 'Produk kebersihan rumah tangga'],
            ['name' => 'Bayi & Anak', 'description' => 'Produk bayi dan anak-anak'],
            ['name' => 'Beku', 'description' => 'Produk beku siap pakai'],
            ['name' => 'Sayur', 'description' => 'Sayuran segar dan berkualitas untuk konsumsi sehari-hari.'],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );
        }
    }
}
