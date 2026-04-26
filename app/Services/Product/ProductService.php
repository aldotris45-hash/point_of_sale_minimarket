<?php

namespace App\Services\Product;

use App\Models\Product;

class ProductService implements ProductServiceInterface
{
    public function create(array $data): Product
    {
        return Product::create([
            'category_id'      => $data['category_id'],
            'name'             => $data['name'],
            'sku'              => $data['sku'],
            'has_retail'       => $data['has_retail'] ?? true,
            'price'            => $data['price'],
            // Multi-unit
            'product_type'     => !($data['has_retail'] ?? true) ? 'count' : ($data['product_type'] ?? 'weight'),
            'unit'             => !($data['has_retail'] ?? true) ? ($data['bulk_unit'] ?? 'krat') : ($data['unit'] ?? 'kg'),
            'bulk_unit'        => $data['bulk_unit'] ?: null,
            'bulk_conversion'  => $data['bulk_conversion'] ?: null,
            'price_per_unit'   => $data['price_per_unit'] ?: $data['price'],
            'price_per_bulk'   => $data['price_per_bulk'] ?: null,
            'krat_weight_kg'   => $data['krat_weight_kg'] ?: null,
            // Promo
            'promo_price'      => $data['promo_price'] ?: null,
            'promo_label'      => $data['promo_label'] ?: null,
            'stock'            => $data['stock'] ?? 0,
            'min_stock'        => $data['min_stock'] ?? 0,
            'expiry_date'      => $data['expiry_date'] ?? null,
        ]);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update([
            'category_id'      => $data['category_id'],
            'name'             => $data['name'],
            'sku'              => $data['sku'],
            'has_retail'       => $data['has_retail'] ?? true,
            'price'            => $data['price'],
            // Multi-unit
            'product_type'     => !($data['has_retail'] ?? true) ? 'count' : ($data['product_type'] ?? $product->product_type),
            'unit'             => !($data['has_retail'] ?? true) ? ($data['bulk_unit'] ?? 'krat') : ($data['unit'] ?? $product->unit),
            'bulk_unit'        => $data['bulk_unit'] ?: null,
            'bulk_conversion'  => $data['bulk_conversion'] ?: null,
            'price_per_unit'   => $data['price_per_unit'] ?: $data['price'],
            'price_per_bulk'   => $data['price_per_bulk'] ?: null,
            'krat_weight_kg'   => $data['krat_weight_kg'] ?: null,
            // Promo
            'promo_price'      => $data['promo_price'] ?: null,
            'promo_label'      => $data['promo_label'] ?: null,
            'stock'            => $data['stock'] ?? 0,
            'min_stock'        => $data['min_stock'] ?? 0,
            'expiry_date'      => $data['expiry_date'] ?? null,
        ]);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function findOrFail(int $id): Product
    {
        return Product::findOrFail($id);
    }
}
