<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Harga - {{ $storeName }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; padding: 3rem 1rem; border-radius: 0 0 2rem 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;}
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .category-title { border-bottom: 2px solid #0d6efd; display: inline-block; padding-bottom: 5px; margin-bottom: 1.5rem; font-weight: 600;}
        .transition-hover { transition: all 0.2s ease; }
        .transition-hover:hover { transform: translateX(5px); box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important; border-color: #0d6efd !important; }
        .price { font-size: 1.1rem; font-weight: 700; color: #198754; }
        .floating-wa { position: fixed; bottom: 20px; right: 20px; background-color: #25d366; color: white; border-radius: 50px; padding: 12px 24px; font-weight: bold; box-shadow: 0 4px 10px rgba(37,211,102,0.4); text-decoration: none; z-index: 1000; transition: transform 0.2s;}
        .floating-wa:hover { color: white; transform: scale(1.05); }
    </style>
</head>
<body>

@php
    $waNumber = preg_replace('/[^0-9]/', '', $storePhone);
    if(substr($waNumber, 0, 1) === '0') {
        $waNumber = '62' . substr($waNumber, 1);
    }
    $waLink = "https://wa.me/{$waNumber}?text=".urlencode("Halo Admin {$storeName}, saya melihat katalog harga sayur Anda dan ingin melakukan pesanan.");
@endphp

<div class="hero text-center">
    <h1 class="display-5 fw-bold mb-3"><i class="bi bi-shop"></i> {{ $storeName }}</h1>
    <p class="lead mb-4">Daftar Harga Grosir Sayur & Buah Segar</p>
    
    <div class="container max-w-md mx-auto" style="max-width: 600px;">
        <form action="{{ route('catalog.index') }}" method="GET" class="d-flex gap-2">
            <select name="category_id" class="form-select form-select-lg shadow-sm" style="max-width: 200px;">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <input type="text" name="search" class="form-control form-control-lg shadow-sm" placeholder="Cari sayur..." value="{{ $search }}">
            <button type="submit" class="btn btn-warning btn-lg shadow-sm"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-muted mb-0">Diperbarui pada: {{ \Carbon\Carbon::now()->format('d M Y') }} | <span class="badge bg-secondary">System DB Check: {{ \App\Models\Product::count() }} Master Products</span></p>
        <a href="{{ route('catalog.export-pdf', request()->query()) }}" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf"></i> Cetak Katalog PDF
        </a>
    </div>

    @forelse($groupedProducts as $categoryName => $products)
        <div class="mb-5">
            <h3 class="category-title text-primary"><i class="bi bi-tags"></i> {{ $categoryName }}</h3>
            
            <div class="d-flex flex-column gap-3">
                @foreach($products as $product)
                <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-4 shadow-sm transition-hover" style="border: 1px solid #f0f0f0;">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.15rem;">{{ $product->name }}</h5>
                    </div>
                    <div class="text-end">
                        @if($product->price > 0)
                            <h5 class="fw-bolder mb-1" style="color: #198754; font-size: 1.25rem;">Rp {{ number_format($product->price, 0, ',', '.') }}</h5>
                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-2 py-1" style="font-weight: 500; font-size: 0.75rem;"><i class="bi bi-circle-fill small" style="font-size: 0.5rem; margin-right: 4px;"></i> Tersedia</span>
                        @else
                            <h6 class="fw-semibold mb-1 text-secondary fst-italic">Hubungi Admin</h6>
                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-2 py-1" style="font-weight: 500; font-size: 0.75rem;"><i class="bi bi-circle-half small" style="font-size: 0.5rem; margin-right: 4px;"></i> Menyesuaikan</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-search display-1 text-light mb-3"></i>
            <h4>Produk tidak ditemukan</h4>
            <p>Cobalah kata kunci lain atau pilih kategori yang berbeda.</p>
        </div>
    @endforelse

</div>

<!-- WhatsApp Float -->
<a href="{{ $waLink }}" target="_blank" class="floating-wa">
    <i class="bi bi-whatsapp"></i> Pesan Sekarang
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
