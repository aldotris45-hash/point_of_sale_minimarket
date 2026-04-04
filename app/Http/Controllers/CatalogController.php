<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Settings\SettingsServiceInterface;
use Carbon\Carbon;

class CatalogController extends Controller
{
    public function __construct(private readonly SettingsServiceInterface $settings)
    {
        // Public guest route - no middleware
    }

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');

        // Only show products with stock > 0 AND price > 0 (harga kosong = tidak dipasarkan)
        $query = Product::with('category')
            ->where('stock', '>', 0)
            ->where('price', '>', 0)
            ->orderBy('name');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Group by category for beautiful display
        $products = $query->get();
        $groupedProducts = $products->groupBy(function($product) {
            return $product->category ? $product->category->name : 'Lain-lain';
        });

        // For the filter dropdown: only categories that have marketable products
        $categories = Category::whereHas('products', function($q) {
            $q->where('stock', '>', 0)->where('price', '>', 0);
        })->orderBy('name')->get();
        
        $storeName = $this->settings->storeName() ?: 'TRIJAYA FRESH';
        $storePhone = $this->settings->storePhone();

        return view('catalog.index', compact('groupedProducts', 'categories', 'search', 'categoryId', 'storeName', 'storePhone'));
    }

    public function exportPdf(Request $request)
    {
        $categoryId = $request->query('category_id');
        $search = $request->query('search');

        $query = Product::with('category')
            ->where('stock', '>', 0)
            ->where('price', '>', 0)
            ->orderBy('name');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get();
        $groupedProducts = $products->groupBy(function($product) {
            return $product->category ? $product->category->name : 'Lain-lain';
        });

        $data = [
            'storeName' => $this->settings->storeName() ?: 'TRIJAYA FRESH',
            'storeAddress' => $this->settings->storeAddress(),
            'storePhone' => $this->settings->storePhone(),
            'groupedProducts' => $groupedProducts,
            'date' => Carbon::now()->format('d F Y (H:i)'),
        ];

        $pdf = Pdf::loadView('catalog.pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Katalog_Harga_{$data['date']}.pdf");
    }
}
