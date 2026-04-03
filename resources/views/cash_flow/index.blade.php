@extends('layouts.app')

@section('title', 'Arus Kas')

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0"><i class="bi bi-arrow-left-right"></i> Arus Kas</h1>
        </div>

        {{-- Filter Tanggal --}}
        <form id="cashFlowFilter" method="GET" action="{{ route('arus-kas') }}" class="card shadow-sm mb-3">
            <div class="card-body row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label for="filterFrom" class="form-label">Dari Tanggal</label>
                    <input type="date" id="filterFrom" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-6 col-md-3">
                    <label for="filterTo" class="form-label">Sampai Tanggal</label>
                    <input type="date" id="filterTo" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <a href="{{ route('arus-kas.export-pdf', ['from' => $from, 'to' => $to]) }}" class="btn btn-outline-danger" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Ekspor PDF
                        </a>
                        <a href="{{ route('arus-kas') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Terapkan</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Summary Cards (5 kartu) --}}
        <div class="row g-3 mb-4">
            {{-- Omset Penjualan --}}
            <div class="col-12 col-md-4 col-xl">
                <div class="card shadow-sm border-start border-success border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-cart-check text-success fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Omset Penjualan</p>
                        </div>
                        <h4 class="mb-0 text-success">@money($totalIncome)</h4>
                        <small class="text-muted">{{ number_format($totalTransactions, 0, ',', '.') }} transaksi</small>
                    </div>
                </div>
            </div>
            {{-- Pembelian Barang --}}
            <div class="col-12 col-md-4 col-xl">
                <div class="card shadow-sm border-start border-warning border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-box-seam text-warning fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Pembelian Barang</p>
                        </div>
                        <h4 class="mb-0 text-warning">@money($totalPurchase)</h4>
                        <small class="text-muted">Modal barang dagangan</small>
                    </div>
                </div>
            </div>
            {{-- Pengeluaran Operasional --}}
            <div class="col-12 col-md-4 col-xl">
                <div class="card shadow-sm border-start border-danger border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-arrow-up-circle text-danger fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Pengeluaran Ops.</p>
                        </div>
                        <h4 class="mb-0 text-danger">@money($totalOperational)</h4>
                        <small class="text-muted">Gaji, listrik, dll</small>
                    </div>
                </div>
            </div>
            {{-- Total Pengeluaran --}}
            <div class="col-12 col-md-6 col-xl">
                <div class="card shadow-sm border-start border-secondary border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-wallet text-secondary fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Total Pengeluaran</p>
                        </div>
                        <h4 class="mb-0 text-secondary">@money($totalExpense)</h4>
                        <small class="text-muted">Barang + Operasional</small>
                    </div>
                </div>
            </div>
            {{-- Laba Bersih --}}
            <div class="col-12 col-md-6 col-xl">
                <div class="card shadow-sm border-start {{ $netBalance >= 0 ? 'border-primary' : 'border-danger' }} border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-graph-up-arrow {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }} fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Laba Bersih</p>
                        </div>
                        <h4 class="mb-0 {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $netBalance < 0 ? '-' : '' }}@money(abs($netBalance))
                        </h4>
                        @if ($netBalance < 0)
                            <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Rugi</small>
                        @else
                            <small class="text-success"><i class="bi bi-check-circle"></i> Margin {{ number_format($marginPercent, 1) }}%</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- Chart Arus Kas --}}
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart"></i> <strong>Grafik Arus Kas Harian</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="cashFlowChart" style="width:100%; height:350px;"></canvas>
                    </div>
                </div>

                {{-- Tabel Rincian Harian --}}
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-table"></i> <strong>Rincian Harian</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0" id="dailyTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th class="text-end">Pemasukan</th>
                                        <th class="text-end">Pembelian Brg</th>
                                        <th class="text-end">Pengeluaran Ops</th>
                                        <th class="text-end">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dailyData as $day)
                                        <tr>
                                            <td>{{ $day['date'] }}</td>
                                            <td class="text-end text-success">@money($day['income'])</td>
                                            <td class="text-end text-warning">@money($day['purchase'])</td>
                                            <td class="text-end text-danger">@money($day['operational'])</td>
                                            <td class="text-end {{ $day['balance'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                                {{ $day['balance'] >= 0 ? '' : '-' }}@money(abs($day['balance']))
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold table-light">
                                        <td>TOTAL</td>
                                        <td class="text-end text-success">@money($totalIncome)</td>
                                        <td class="text-end text-warning">@money($totalPurchase)</td>
                                        <td class="text-end text-danger">@money($totalOperational)</td>
                                        <td class="text-end {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }}">
                                            {{ $netBalance >= 0 ? '' : '-' }}@money(abs($netBalance))
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-12 col-xl-4">
                {{-- Breakdown Pengeluaran per Kategori --}}
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart"></i> <strong>Pengeluaran per Kategori</strong>
                    </div>
                    <div class="card-body">
                        @if ($expenseByCategory->isEmpty())
                            <div class="text-muted text-center py-3">Tidak ada pengeluaran di periode ini.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Kategori</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($expenseByCategory as $cat)
                                            @php
                                                $pct = $totalExpense > 0 ? ($cat['total'] / $totalExpense * 100) : 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    @if ($cat['category'] === 'Pembelian Barang')
                                                        <i class="bi bi-box-seam text-warning"></i>
                                                    @else
                                                        <i class="bi bi-dash-circle text-danger"></i>
                                                    @endif
                                                    {{ $cat['category'] }}
                                                </td>
                                                <td class="text-end">@money($cat['total'])</td>
                                                <td class="text-end">{{ number_format($pct, 1) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold table-light">
                                            <td>Total</td>
                                            <td class="text-end">@money($totalExpense)</td>
                                            <td class="text-end">100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Progress bars --}}
                            <div class="mt-3">
                                @foreach ($expenseByCategory as $cat)
                                    @php
                                        $pct = $totalExpense > 0 ? ($cat['total'] / $totalExpense * 100) : 0;
                                        $color = $cat['category'] === 'Pembelian Barang' ? 'bg-warning' : (['bg-danger', 'bg-info', 'bg-primary', 'bg-secondary'][$loop->index % 4]);
                                    @endphp
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small">
                                            <span>{{ $cat['category'] }}</span>
                                            <span>{{ number_format($pct, 1) }}%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar {{ $color }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Ringkasan --}}
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-calculator"></i> <strong>Ringkasan</strong>
                    </div>
                    <div class="card-body">
                        @php
                            $days = max(1, $dailyData->count());
                            $avgIncome = $totalIncome / $days;
                            $avgExpense = $totalExpense / $days;
                            $avgProfit = $netBalance / $days;
                        @endphp
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rata-rata Omset/Hari</span>
                                <strong>@money($avgIncome)</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rata-rata Pengeluaran/Hari</span>
                                <strong>@money($avgExpense)</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rata-rata Laba/Hari</span>
                                <strong class="{{ $avgProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $avgProfit < 0 ? '-' : '' }}@money(abs($avgProfit))
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Margin Laba</span>
                                <strong class="{{ $marginPercent >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($marginPercent, 1) }}%
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rasio Pembelian Barang</span>
                                <strong>
                                    @if ($totalIncome > 0)
                                        {{ number_format(($totalPurchase / $totalIncome) * 100, 1) }}%
                                    @else
                                        -
                                    @endif
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Hari dgn Pendapatan</span>
                                <strong>{{ $dailyData->where('income', '>', 0)->count() }} / {{ $dailyData->count() }} hari</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('cashFlowChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: @json($chartIncome),
                            backgroundColor: 'rgba(25, 135, 84, 0.7)',
                            borderColor: 'rgba(25, 135, 84, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Pembelian Barang',
                            data: @json($chartPurchase),
                            backgroundColor: 'rgba(255, 193, 7, 0.7)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Pengeluaran Ops',
                            data: @json($chartOperational),
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': Rp ' +
                                        ctx.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                    if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    }
                }
            });

            // DataTable untuk tabel harian
            if ($.fn.DataTable) {
                $('#dailyTable').DataTable({
                    paging: true,
                    pageLength: 15,
                    ordering: false,
                    info: false,
                    language: {
                        url: @json(asset('assets/vendor/id.json'))
                    }
                });
            }
        });
    </script>
@endpush
