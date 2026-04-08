<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Harga - {{ $storeName }}</title>
    <meta name="description" content="Daftar harga grosir sayur dan buah segar dari {{ $storeName }}. Cek harga terbaru dan pesan sekarang!">
    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/vendor/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendor/bootstrap-icons-1.13.1/bootstrap-icons.min.css') }}" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            background-color: #f1f5f0;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            margin: 0;
        }

        /* ───── HERO ───── */
        .hero {
            background: linear-gradient(135deg, #14532d 0%, #166534 40%, #16a34a 100%);
            color: white;
            padding: 3rem 1rem 2.5rem;
            border-radius: 0 0 2.5rem 2.5rem;
            box-shadow: 0 6px 24px rgba(22,101,52,0.35);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '🥬 🍎 🥦 🍊 🌿 🍇';
            position: absolute;
            top: -10px; left: 0; right: 0;
            font-size: 4rem;
            letter-spacing: 1.5rem;
            opacity: 0.08;
            white-space: nowrap;
            overflow: hidden;
            pointer-events: none;
        }
        .hero h1 { font-weight: 800; letter-spacing: -0.5px; }
        .hero .lead { opacity: 0.9; }
        .hero .form-control, .hero .form-select {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        .hero .btn-warning {
            font-weight: 700;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* ───── KATEGORI SECTION ───── */
        .category-section {
            margin-bottom: 3rem;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
        }

        /* Banner per kategori */
        .category-banner {
            position: relative;
            padding: 1.4rem 1.8rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Emoji floating di belakang banner */
        .category-banner .bg-emojis {
            position: absolute;
            right: -10px; top: 50%;
            transform: translateY(-50%);
            font-size: 5rem;
            line-height: 1;
            opacity: 0.18;
            letter-spacing: 0.5rem;
            pointer-events: none;
            white-space: nowrap;
            user-select: none;
        }

        .category-banner .banner-icon {
            font-size: 2.2rem;
            flex-shrink: 0;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        .category-banner h3 {
            margin: 0;
            font-weight: 800;
            font-size: 1.5rem;
            color: white;
            text-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }
        .category-banner .banner-sub {
            margin: 0;
            font-size: 0.82rem;
            color: rgba(255,255,255,0.8);
            font-weight: 500;
        }

        /* Warna per kategori — Sayur */
        .theme-sayur .category-banner {
            background: linear-gradient(120deg, #14532d 0%, #166534 50%, #15803d 100%);
        }
        .theme-sayur .category-body { background: #f0fdf4; }
        .theme-sayur .product-card { border-left: 4px solid #22c55e; }
        .theme-sayur .product-card:hover { box-shadow: 0 6px 20px rgba(34,197,94,0.2); border-left-color: #16a34a; }
        .theme-sayur .product-price { color: #15803d; }

        /* Warna per kategori — Buah */
        .theme-buah .category-banner {
            background: linear-gradient(120deg, #9a1c07 0%, #c2410c 50%, #ea580c 100%);
        }
        .theme-buah .category-body { background: #fff7ed; }
        .theme-buah .product-card { border-left: 4px solid #f97316; }
        .theme-buah .product-card:hover { box-shadow: 0 6px 20px rgba(249,115,22,0.2); border-left-color: #ea580c; }
        .theme-buah .product-price { color: #c2410c; }

        /* Warna per kategori — Bumbu/Rempah */
        .theme-bumbu .category-banner {
            background: linear-gradient(120deg, #78350f 0%, #92400e 50%, #b45309 100%);
        }
        .theme-bumbu .category-body { background: #fffbeb; }
        .theme-bumbu .product-card { border-left: 4px solid #d97706; }
        .theme-bumbu .product-card:hover { box-shadow: 0 6px 20px rgba(217,119,6,0.2); }
        .theme-bumbu .product-price { color: #b45309; }

        /* Warna per kategori — Daging/Ayam/Ikan */
        .theme-daging .category-banner {
            background: linear-gradient(120deg, #7f1d1d 0%, #991b1b 50%, #dc2626 100%);
        }
        .theme-daging .category-body { background: #fff1f2; }
        .theme-daging .product-card { border-left: 4px solid #ef4444; }
        .theme-daging .product-card:hover { box-shadow: 0 6px 20px rgba(239,68,68,0.2); }
        .theme-daging .product-price { color: #b91c1c; }

        /* Default (kategori lain) */
        .theme-default .category-banner {
            background: linear-gradient(120deg, #1e3a5f 0%, #1d4ed8 50%, #3b82f6 100%);
        }
        .theme-default .category-body { background: #eff6ff; }
        .theme-default .product-card { border-left: 4px solid #3b82f6; }
        .theme-default .product-card:hover { box-shadow: 0 6px 20px rgba(59,130,246,0.2); }
        .theme-default .product-price { color: #1d4ed8; }

        /* ───── BODY PRODUK ───── */
        .category-body {
            padding: 1.25rem 1.25rem 1.5rem;
        }

        /* ───── PRODUCT CARD ───── */
        .product-card {
            background: white;
            border-radius: 10px;
            padding: 0.9rem 1rem;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-left-color 0.18s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-card:hover { transform: translateY(-3px); }
        .product-card .product-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: #1a2e1a;
            line-height: 1.4;
            margin-bottom: 0.5rem;
        }
        .product-price {
            font-size: 1.05rem;
            font-weight: 800;
        }
        .product-price-call {
            font-size: 0.82rem;
            font-weight: 600;
            color: #9ca3af;
            font-style: italic;
        }

        /* ───── ANIMATION ───── */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .category-section {
            animation: fadeSlideUp 0.4s ease both;
        }
        .category-section:nth-child(1) { animation-delay: 0.05s; }
        .category-section:nth-child(2) { animation-delay: 0.12s; }
        .category-section:nth-child(3) { animation-delay: 0.19s; }
        .category-section:nth-child(4) { animation-delay: 0.26s; }
        .category-section:nth-child(5) { animation-delay: 0.33s; }

        /* ───── TOP BAR ───── */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .top-bar .text-muted { font-size: 0.82rem; }

        /* ───── EMPTY STATE ───── */
        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            color: #9ca3af;
        }
        .empty-state .empty-icon { font-size: 4rem; margin-bottom: 1rem; }

        /* ───── WHATSAPP FLOAT ───── */
        .floating-wa {
            position: fixed;
            bottom: 22px; right: 22px;
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
            border-radius: 50px;
            padding: 12px 22px;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 4px 16px rgba(37,211,102,0.45);
            text-decoration: none;
            z-index: 1000;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .floating-wa:hover {
            color: white;
            transform: scale(1.06);
            box-shadow: 0 6px 20px rgba(37,211,102,0.55);
        }

        /* ───── RESPONSIVE ───── */
        @media (max-width: 576px) {
            .hero { padding: 2rem 1rem 2rem; border-radius: 0 0 1.5rem 1.5rem; }
            .category-banner { padding: 1rem 1.2rem; }
            .category-banner .bg-emojis { font-size: 3.5rem; }
            .category-banner h3 { font-size: 1.2rem; }
        }

        /* ───── PROMO POPUP MODAL ───── */
        .promo-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .promo-overlay.visible { opacity: 1; }
        .promo-overlay.hidden  { opacity: 0; pointer-events: none; }

        @keyframes popIn {
            0%   { transform: scale(0.85) translateY(30px); opacity: 0; }
            100% { transform: scale(1)    translateY(0);    opacity: 1; }
        }
        .promo-modal {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            max-width: 580px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            animation: popIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        .promo-modal-header {
            background: linear-gradient(120deg, #7f1d1d 0%, #dc2626 45%, #f97316 100%);
            padding: 1.5rem 1.8rem 1.2rem;
            position: relative;
            overflow: hidden;
        }
        .promo-modal-header .bg-fx {
            position: absolute; inset: 0;
            font-size: 5rem;
            opacity: 0.12;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 1rem;
            pointer-events: none;
            letter-spacing: 0.5rem;
        }
        .promo-modal-header h2 {
            color: white;
            font-size: 1.6rem;
            font-weight: 800;
            margin: 0 0 0.2rem;
            text-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        .promo-modal-header p {
            color: rgba(255,255,255,0.85);
            margin: 0;
            font-size: 0.88rem;
        }
        .promo-modal-close {
            position: absolute;
            top: 14px; right: 16px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 1.4rem;
            width: 36px; height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
            line-height: 1;
        }
        .promo-modal-close:hover { background: rgba(255,255,255,0.35); }
        .promo-modal-body {
            padding: 1.25rem;
            background: #fff5f5;
            max-height: 55vh;
            overflow-y: auto;
        }
        .promo-modal-card {
            background: white;
            border-radius: 10px;
            border: 2px solid #fca5a5;
            padding: 0.85rem 1rem;
            position: relative;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .promo-modal-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(220,38,38,0.18); }
        .promo-modal-card .pill {
            position: absolute; top: -1px; right: -1px;
            background: linear-gradient(135deg, #dc2626, #f97316);
            color: white; font-size: 0.63rem; font-weight: 800;
            padding: 3px 9px; border-radius: 0 8px 0 8px;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .promo-modal-card .pname { font-weight: 700; font-size: 0.88rem; color: #1a1a1a; margin-bottom: 4px; padding-right: 0.5rem; }
        .promo-modal-card .pold  { font-size: 0.75rem; color: #9ca3af; text-decoration: line-through; }
        .promo-modal-card .pnew  { font-size: 1rem; font-weight: 800; color: #dc2626; }
        .promo-modal-card .psave { font-size: 0.7rem; color: #16a34a; font-weight: 700; }
        .promo-modal-footer {
            padding: 1rem 1.25rem;
            background: white;
            display: flex;
            gap: 0.75rem;
            border-top: 1px solid #fee2e2;
        }

        /* ───── PROMO SECTION ───── */
        @keyframes promoPulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(220,38,38,0.4); }
            50%       { box-shadow: 0 4px 32px rgba(249,115,22,0.7); }
        }
        .promo-section {
            border-radius: 1.25rem;
            overflow: hidden;
            margin-bottom: 2.5rem;
            animation: fadeSlideUp 0.3s ease both, promoPulse 2.4s ease-in-out infinite;
        }
        .promo-banner {
            background: linear-gradient(120deg, #7f1d1d 0%, #dc2626 45%, #f97316 100%);
            padding: 1.2rem 1.8rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        .promo-banner .bg-emojis {
            position: absolute;
            right: -10px; top: 50%;
            transform: translateY(-50%);
            font-size: 4.5rem;
            opacity: 0.15;
            letter-spacing: 0.5rem;
            pointer-events: none;
            white-space: nowrap;
        }
        .promo-banner .promo-fire { font-size: 2.2rem; flex-shrink: 0; }
        .promo-banner .promo-title {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 1px 4px rgba(0,0,0,0.25);
        }
        .promo-banner .promo-sub {
            margin: 0;
            font-size: 0.82rem;
            color: rgba(255,255,255,0.85);
        }
        .promo-banner .promo-badge {
            margin-left: auto;
            flex-shrink: 0;
            background: #fbbf24;
            color: #7c2d12;
            font-weight: 800;
            font-size: 0.8rem;
            padding: 6px 14px;
            border-radius: 50px;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .promo-body {
            background: #fff5f5;
            padding: 1.25rem;
        }
        /* Promo product card */
        .promo-card {
            background: white;
            border-radius: 10px;
            border: 2px solid #fca5a5;
            padding: 0.9rem 1rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            transition: transform 0.18s, box-shadow 0.18s;
        }
        .promo-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(220,38,38,0.2); }
        .promo-card .promo-pill {
            position: absolute;
            top: -1px; right: -1px;
            background: linear-gradient(135deg, #dc2626, #f97316);
            color: white;
            font-size: 0.68rem;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 0 8px 0 8px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .promo-card .product-name { font-weight: 700; font-size: 0.88rem; color: #1a1a1a; margin-bottom: 0.6rem; line-height: 1.4; padding-right: 0.5rem; }
        .promo-card .price-original { font-size: 0.78rem; color: #9ca3af; text-decoration: line-through; }
        .promo-card .price-promo { font-size: 1.05rem; font-weight: 800; color: #dc2626; }
        .promo-card .price-hemat { font-size: 0.72rem; color: #16a34a; font-weight: 700; margin-top: 2px; }
        /* Badge promo kecil di card kategori */
        .promo-mini-badge {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626, #f97316);
            color: white;
            font-size: 0.62rem;
            font-weight: 800;
            padding: 1px 7px;
            border-radius: 50px;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

@php
    $waNumber = preg_replace('/[^0-9]/', '', $storePhone);
    if(substr($waNumber, 0, 1) === '0') {
        $waNumber = '62' . substr($waNumber, 1);
    }
    $waLink = "https://wa.me/{$waNumber}?text=".urlencode("Halo Admin {$storeName}, saya melihat katalog harga dan ingin melakukan pesanan.");

    /**
     * Mapping nama kategori → tema visual
     * Cek dengan str_contains case-insensitive terhadap nama kategori
     */
    $categoryThemes = [
        'sayur'  => [
            'class'    => 'theme-sayur',
            'icon'     => '🥬',
            'sub'      => 'Sayuran segar pilihan',
            'bgEmoji'  => '🥦 🌿 🫑 🥬 🌱',
        ],
        'buah'   => [
            'class'    => 'theme-buah',
            'icon'     => '🍎',
            'sub'      => 'Buah-buahan segar pilihan',
            'bgEmoji'  => '🍊 🍋 🍇 🍓 🍎',
        ],
        'jeruk'  => [
            'class'    => 'theme-buah',
            'icon'     => '🍊',
            'sub'      => 'Jeruk dan citrus segar',
            'bgEmoji'  => '🍊 🍋 🍎 🍇 🍓',
        ],
        'bumbu'  => [
            'class'    => 'theme-bumbu',
            'icon'     => '🧄',
            'sub'      => 'Bumbu dan rempah dapur',
            'bgEmoji'  => '🧄 🌶️ 🫚 🧅 🌿',
        ],
        'rempah' => [
            'class'    => 'theme-bumbu',
            'icon'     => '🌶️',
            'sub'      => 'Rempah pilihan',
            'bgEmoji'  => '🌶️ 🧄 🫚 🌿 🧅',
        ],
        'daging' => [
            'class'    => 'theme-daging',
            'icon'     => '🥩',
            'sub'      => 'Daging segar berkualitas',
            'bgEmoji'  => '🥩 🍗 🐟 🥚',
        ],
        'ayam'   => [
            'class'    => 'theme-daging',
            'icon'     => '🍗',
            'sub'      => 'Ayam segar pilihan',
            'bgEmoji'  => '🍗 🥩 🐓 🥚',
        ],
        'ikan'   => [
            'class'    => 'theme-daging',
            'icon'     => '🐟',
            'sub'      => 'Ikan dan seafood segar',
            'bgEmoji'  => '🐟 🦐 🦑 🐠',
        ],
    ];

    $defaultTheme = [
        'class'   => 'theme-default',
        'icon'    => '🏪',
        'sub'     => 'Produk pilihan toko',
        'bgEmoji' => '🛒 📦 🏪',
    ];

    function getCategoryTheme(string $categoryName, array $themes, array $default): array {
        $lower = strtolower($categoryName);
        foreach ($themes as $keyword => $theme) {
            if (str_contains($lower, $keyword)) {
                return $theme;
            }
        }
        return $default;
    }
@endphp

<!-- HERO -->
<div class="hero text-center">
    <h1 class="display-6 fw-bold mb-2"><i class="bi bi-shop-window"></i> {{ $storeName }}</h1>
    <p class="lead mb-4">Daftar Harga Grosir Sayur &amp; Buah Segar</p>

    <div class="container" style="max-width: 620px;">
        <form action="{{ route('catalog.index') }}" method="GET" class="d-flex gap-2">
            <select name="category_id" class="form-select form-select-lg shadow-sm" style="max-width: 210px;">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <input type="text" name="search" class="form-control form-control-lg shadow-sm" placeholder="Cari produk..." value="{{ $search }}">
            <button type="submit" class="btn btn-warning btn-lg shadow-sm px-3"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="container mb-5 pb-5">

    <!-- Top bar -->
    <div class="top-bar">
        <p class="text-muted mb-0">
            <i class="bi bi-clock text-success"></i>
            Diperbarui: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMM YYYY') }}
        </p>
        <a href="{{ route('catalog.export-pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-earmark-pdf"></i> Cetak PDF
        </a>
    </div>

    {{-- ══════════ PROMO SECTION ══════════ --}}
    @if($promoProducts->isNotEmpty() && !$categoryId && !$search)
    <div class="promo-section">
        <div class="promo-banner">
            <span class="bg-emojis" aria-hidden="true">🔥 ⚡ 🎉 💥 🔥 ⚡ 🎉 💥</span>
            <span class="promo-fire">🔥</span>
            <div>
                <h2 class="promo-title">PROMO HARI INI!</h2>
                <p class="promo-sub">Dapatkan harga spesial sebelum kehabisan &bull; {{ $promoProducts->count() }} produk promo</p>
            </div>
            <div class="promo-badge">HEMAT SEKARANG!</div>
        </div>
        <div class="promo-body">
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                @foreach($promoProducts as $p)
                @php
                    $hemat = (float)$p->price - (float)$p->promo_price;
                    $pct   = $p->price > 0 ? round($hemat / $p->price * 100) : 0;
                    $label = $p->promo_label ?: "HEMAT {$pct}%";
                @endphp
                <div class="col">
                    <div class="promo-card">
                        <span class="promo-pill">{{ $label }}</span>
                        <p class="product-name">{{ $p->name }}</p>
                        <div>
                            <div class="price-original">Rp {{ number_format((float)$p->price, 0, ',', '.') }}</div>
                            <div class="price-promo">Rp {{ number_format((float)$p->promo_price, 0, ',', '.') }}</div>
                            <div class="price-hemat">💚 Hemat Rp {{ number_format($hemat, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @forelse($groupedProducts as $categoryName => $products)
        @php
            $theme = getCategoryTheme($categoryName, $categoryThemes, $defaultTheme);
        @endphp

        <div class="category-section {{ $theme['class'] }}">

            <!-- Banner Kategori -->
            <div class="category-banner">
                <span class="bg-emojis" aria-hidden="true">{{ $theme['bgEmoji'] }} {{ $theme['bgEmoji'] }}</span>
                <span class="banner-icon">{{ $theme['icon'] }}</span>
                <div>
                    <h3>{{ $categoryName }}</h3>
                    <p class="banner-sub">{{ $theme['sub'] }} &bull; {{ $products->count() }} produk</p>
                </div>
            </div>

            <!-- Grid Produk -->
            <div class="category-body">
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                    @foreach($products as $product)
                    <div class="col">
                        <div class="product-card">
                            @if($product->isOnPromo())
                                <span class="promo-mini-badge">🔥 Promo</span>
                            @endif
                            <p class="product-name">{{ $product->name }}</p>
                            <div>
                                @if($product->isOnPromo())
                                    <div style="font-size:0.78rem;color:#9ca3af;text-decoration:line-through;">Rp {{ number_format((float)$product->price, 0, ',', '.') }}</div>
                                    <div class="product-price" style="color:#dc2626;">Rp {{ number_format((float)$product->promo_price, 0, ',', '.') }}</div>
                                @elseif($product->price > 0)
                                    <div class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                @else
                                    <div class="product-price-call">Hubungi Admin</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    @empty
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h4>Produk tidak ditemukan</h4>
            <p>Coba kata kunci lain atau pilih kategori yang berbeda.</p>
            <a href="{{ route('catalog.index') }}" class="btn btn-outline-success mt-2">Tampilkan Semua</a>
        </div>
    @endforelse

</div>

<!-- WhatsApp Float -->
<a href="{{ $waLink }}" target="_blank" rel="noopener" class="floating-wa">
    <i class="bi bi-whatsapp fs-5"></i> Pesan Sekarang
</a>

{{-- ══════════ PROMO POPUP MODAL ══════════ --}}
@if($promoProducts->isNotEmpty() && !$categoryId && !$search)
<div class="promo-overlay" id="promoOverlay" role="dialog" aria-modal="true" aria-labelledby="promoModalTitle">
    <div class="promo-modal">

        <!-- Header -->
        <div class="promo-modal-header">
            <div class="bg-fx" aria-hidden="true">🔥 ⚡ 💥 🎉</div>
            <button class="promo-modal-close" id="promoClose" aria-label="Tutup">&times;</button>
            <h2 id="promoModalTitle">🔥 PROMO HARI INI!</h2>
            <p>Harga spesial untuk {{ $promoProducts->count() }} produk — jangan sampai kehabisan!</p>
        </div>

        <!-- Body: promo cards -->
        <div class="promo-modal-body">
            <div class="row row-cols-2 g-3">
                @foreach($promoProducts as $p)
                @php
                    $hemat = (float)$p->price - (float)$p->promo_price;
                    $pct   = $p->price > 0 ? round($hemat / $p->price * 100) : 0;
                    $label = $p->promo_label ?: "HEMAT {$pct}%";
                @endphp
                <div class="col">
                    <div class="promo-modal-card">
                        <span class="pill">{{ $label }}</span>
                        <p class="pname">{{ $p->name }}</p>
                        <div class="pold">Rp {{ number_format((float)$p->price, 0, ',', '.') }}</div>
                        <div class="pnew">Rp {{ number_format((float)$p->promo_price, 0, ',', '.') }}</div>
                        <div class="psave">💚 Hemat Rp {{ number_format($hemat, 0, ',', '.') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="promo-modal-footer">
            <a href="{{ $waLink }}" target="_blank" rel="noopener"
               class="btn btn-success flex-grow-1 fw-bold">
                <i class="bi bi-whatsapp"></i> Pesan Sekarang!
            </a>
            <button id="promoCloseBtnBottom" class="btn btn-outline-secondary">
                Lihat Katalog
            </button>
        </div>

    </div>
</div>

<script>
(function () {
    const STORAGE_KEY = 'promo_popup_seen_{{ md5($promoProducts->pluck("id")->sort()->implode(",")) }}';
    const overlay  = document.getElementById('promoOverlay');
    const closeBtn = document.getElementById('promoClose');
    const closeBottom = document.getElementById('promoCloseBtnBottom');

    function openModal() {
        overlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.remove('visible');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
        // Simpan ke sessionStorage — popup tidak muncul lagi dalam satu sesi
        sessionStorage.setItem(STORAGE_KEY, '1');
    }

    // Auto-open setelah 600ms jika belum pernah lihat di sesi ini
    if (!sessionStorage.getItem(STORAGE_KEY)) {
        setTimeout(openModal, 600);
    } else {
        overlay.classList.add('hidden');
    }

    closeBtn.addEventListener('click', closeModal);
    closeBottom.addEventListener('click', closeModal);

    // Klik di luar modal untuk menutup
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    // ESC untuk menutup
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });
})();
</script>
@endif

<script src="{{ asset('assets/vendor/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
