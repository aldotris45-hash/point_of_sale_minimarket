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
                        <h2 class="h4 mb-0">@money($salesToday)</h2>
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
                        <h2 class="h4 mb-0">{{ number_format($trxToday, 0, ',', '.') }}</h2>
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
                        <h2 class="h4 mb-0">{{ number_format($outOfStock, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
        </article>
        <article class="col">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-people text-info fs-2 me-3"></i>
                    <div>
                        <p class="text-muted mb-0">Produk Stok Rendah (&le; 5)</p>
                        <h2 class="h4 mb-0">{{ number_format($lowStock, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="row g-3 mt-1 mt-md-3">
        <div class="col-12 col-xl-7">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-graph-up"></i>
                    <strong>Penjualan 7 Hari Terakhir</strong>
                </div>
                <div class="card-body">
                    <div class="chart-bars">
                        @foreach ($chartValues as $i => $val)
                            @php
                                $pct = $chartMax > 0 ? round(($val / $chartMax) * 100) : 0;
                                $label = $chartLabels[$i] ?? '';
                            @endphp
                            <div class="bar">
                                <div class="bar-inner" style="height: {{ $pct }}%" title="{{ $label }} - @money($val)"></div>
                                <div class="bar-label">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-star"></i>
                    <strong>Top Produk Hari Ini</strong>
                </div>
                <div class="card-body">
                    @if ($topToday->isEmpty())
                        <div class="text-muted">Belum ada penjualan hari ini.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topToday as $p)
                                        <tr>
                                            <td>{{ $p->name }}</td>
                                            <td class="text-end">{{ number_format($p->qty, 0, ',', '.') }}</td>
                                            <td class="text-end">@money($p->total)</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    
</section>
@endsection

@push('css')
<style>
    .chart-bars {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: .75rem;
        align-items: end;
        height: 220px;
    }
    .chart-bars .bar { display: flex; flex-direction: column; align-items: center; }
    .chart-bars .bar-inner {
        width: 100%;
        background: linear-gradient(180deg, #0d6efd 0%, #6ea8fe 100%);
        border-radius: .375rem .375rem 0 0;
        min-height: 4px;
    }
    .chart-bars .bar-label { font-size: .8rem; color: #6c757d; margin-top: .25rem; }
</style>
@endpush