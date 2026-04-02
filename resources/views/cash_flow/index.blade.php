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
                        <a href="{{ route('arus-kas') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Terapkan</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-start border-success border-4 h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-arrow-down-circle text-success fs-1 me-3"></i>
                        <div>
                            <p class="text-muted mb-0">Total Pemasukan</p>
                            <h3 class="mb-0 text-success">@money($totalIncome)</h3>
                            <small class="text-muted">{{ number_format($totalTransactions, 0, ',', '.') }} transaksi</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-start border-danger border-4 h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-arrow-up-circle text-danger fs-1 me-3"></i>
                        <div>
                            <p class="text-muted mb-0">Total Pengeluaran</p>
                            <h3 class="mb-0 text-danger">@money($totalExpense)</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-start {{ $netBalance >= 0 ? 'border-primary' : 'border-warning' }} border-4 h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-wallet2 {{ $netBalance >= 0 ? 'text-primary' : 'text-warning' }} fs-1 me-3"></i>
                        <div>
                            <p class="text-muted mb-0">Saldo Bersih</p>
                            <h3 class="mb-0 {{ $netBalance >= 0 ? 'text-primary' : 'text-warning' }}">@money(abs($netBalance))</h3>
                            @if ($netBalance < 0)
                                <small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Defisit</small>
                            @else
                                <small class="text-success"><i class="bi bi-check-circle"></i> Surplus</small>
                            @endif
                        </div>
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
                                        <th class="text-end">Pengeluaran</th>
                                        <th class="text-end">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dailyData as $day)
                                        <tr>
                                            <td>{{ $day['date'] }}</td>
                                            <td class="text-end text-success">@money($day['income'])</td>
                                            <td class="text-end text-danger">@money($day['expense'])</td>
                                            <td class="text-end {{ $day['balance'] >= 0 ? 'text-primary' : 'text-warning' }}">
                                                {{ $day['balance'] >= 0 ? '' : '-' }}@money(abs($day['balance']))
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold table-light">
                                        <td>TOTAL</td>
                                        <td class="text-end text-success">@money($totalIncome)</td>
                                        <td class="text-end text-danger">@money($totalExpense)</td>
                                        <td class="text-end {{ $netBalance >= 0 ? 'text-primary' : 'text-warning' }}">
                                            {{ $netBalance >= 0 ? '' : '-' }}@money(abs($netBalance))
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: Breakdown Pengeluaran per Kategori --}}
            <div class="col-12 col-xl-4">
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
                                                <td>{{ $cat['category'] }}</td>
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

                            {{-- Progress bars untuk visualisasi --}}
                            <div class="mt-3">
                                @foreach ($expenseByCategory as $cat)
                                    @php
                                        $pct = $totalExpense > 0 ? ($cat['total'] / $totalExpense * 100) : 0;
                                        $colors = ['bg-danger', 'bg-warning', 'bg-info', 'bg-primary', 'bg-secondary'];
                                        $color = $colors[$loop->index % count($colors)];
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

                {{-- Ringkasan Rasio --}}
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-calculator"></i> <strong>Ringkasan</strong>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rata-rata Pemasukan/Hari</span>
                                <strong>
                                    @php
                                        $days = max(1, $dailyData->count());
                                        $avgIncome = $totalIncome / $days;
                                        $avgExpense = $totalExpense / $days;
                                    @endphp
                                    @money($avgIncome)
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rata-rata Pengeluaran/Hari</span>
                                <strong>@money($avgExpense)</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rasio Pengeluaran</span>
                                <strong>
                                    @if ($totalIncome > 0)
                                        {{ number_format(($totalExpense / $totalIncome) * 100, 1) }}%
                                    @else
                                        -
                                    @endif
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Hari dengan Pendapatan</span>
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
                            label: 'Pengeluaran',
                            data: @json($chartExpense),
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
