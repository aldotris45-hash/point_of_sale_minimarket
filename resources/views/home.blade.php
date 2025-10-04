@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="container py-4">
    <header class="mb-4">
        <h1 class="h3 d-flex align-items-center gap-2">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
        <p class="text-muted mb-0">Ringkasan aktivitas kasir dan penjualan.</p>
    </header>

    <section class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
        <article class="col">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-cash-coin text-success fs-2 me-3"></i>
                    <div>
                        <p class="text-muted mb-0">Penjualan Hari Ini</p>
                        <h2 class="h4 mb-0">Rp 0</h2>
                    </div>
                </div>
            </div>
        </article>
        <article class="col">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-receipt text-primary fs-2 me-3"></i>
                    <div>
                        <p class="text-muted mb-0">Transaksi</p>
                        <h2 class="h4 mb-0">0</h2>
                    </div>
                </div>
            </div>
        </article>
        <article class="col">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-box-seam text-warning fs-2 me-3"></i>
                    <div>
                        <p class="text-muted mb-0">Produk Habis</p>
                        <h2 class="h4 mb-0">0</h2>
                    </div>
                </div>
            </div>
        </article>
        <article class="col">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-people text-info fs-2 me-3"></i>
                    <div>
                        <p class="text-muted mb-0">Pengguna Aktif</p>
                        <h2 class="h4 mb-0">0</h2>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="card shadow-sm mt-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-graph-up"></i>
            <strong>Grafik Penjualan</strong>
        </div>
        <div class="card-body">
            <div class="ratio ratio-16x9 bg-light rounded d-flex align-items-center justify-content-center">
                <img src="{{ asset('assets/images/logo.webp') }}" alt="Placeholder" width="56" height="56" />
            </div>
        </div>
    </section>
</section>
@endsection