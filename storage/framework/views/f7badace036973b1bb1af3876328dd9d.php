<?php $__env->startSection('title', 'Laporan Pajak UMKM — ' . $year); ?>

<?php $__env->startSection('content'); ?>
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0"><i class="bi bi-calculator"></i> Laporan Pajak UMKM</h1>
        </div>

        
        <form id="taxFilter" method="GET" action="<?php echo e(route('pajak')); ?>" class="card shadow-sm mb-3">
            <div class="card-body row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label for="filterYear" class="form-label">Tahun Pajak</label>
                    <select id="filterYear" name="year" class="form-select" onchange="this.form.submit()">
                        <?php $__currentLoopData = $available_years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($y); ?>" <?php echo e($y == $year ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-12 col-md-9">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <a href="<?php echo e(route('pajak.export-csv', ['year' => $year])); ?>" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
                        </a>
                        <a href="<?php echo e(route('pajak.export-pdf', ['year' => $year])); ?>" class="btn btn-outline-danger" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </form>

        
        <div class="row g-3 mb-4">
            
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-start border-primary border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-cash-coin text-primary fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Omset Tahunan <?php echo e($year); ?></p>
                        </div>
                        <h4 class="mb-0 text-primary"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['total_revenue'], 0, ',', '.');
            ?></h4>
                        <small class="text-muted">Seluruh penjualan (status lunas)</small>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-start border-danger border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-receipt-cutoff text-danger fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Total PPh Final (0.5%)</p>
                        </div>
                        <h4 class="mb-0 text-danger"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['total_tax'], 0, ',', '.');
            ?></h4>
                        <small class="text-muted">Yang harus disetor ke negara</small>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-start <?php echo e($summary['is_below_ptkp'] ? 'border-success' : 'border-warning'); ?> border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-shield-check <?php echo e($summary['is_below_ptkp'] ? 'text-success' : 'text-warning'); ?> fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Status PTKP</p>
                        </div>
                        <?php if($summary['is_below_ptkp']): ?>
                            <h4 class="mb-0 text-success">Bebas Pajak</h4>
                            <small class="text-muted">Omset di bawah Rp 500 juta</small>
                        <?php else: ?>
                            <h4 class="mb-0 text-warning">Kena Pajak</h4>
                            <small class="text-muted">Omset melewati batas PTKP</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm border-start border-info border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-speedometer text-info fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Sisa Kuota PTKP</p>
                        </div>
                        <h4 class="mb-0 text-info"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['remaining_ptkp'], 0, ',', '.');
            ?></h4>
                        <?php
                            $usedPercent = $summary['ptkp_limit'] > 0
                                ? min(100, ($summary['total_revenue'] / $summary['ptkp_limit']) * 100)
                                : 0;
                        ?>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar <?php echo e($usedPercent >= 100 ? 'bg-danger' : ($usedPercent >= 75 ? 'bg-warning' : 'bg-info')); ?>"
                                 style="width: <?php echo e($usedPercent); ?>%"></div>
                        </div>
                        <small class="text-muted"><?php echo e(number_format($usedPercent, 1)); ?>% terpakai</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-table"></i> <strong>Rekap Omset & PPh per Bulan — <?php echo e($year); ?></strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0" id="taxTable">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th class="text-end">Transaksi</th>
                                        <th class="text-end">Omset Bruto</th>
                                        <th class="text-end">Kumulatif</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end">Kena Pajak</th>
                                        <th class="text-end">PPh 0.5%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="<?php echo e($m['gross_revenue'] == 0 ? 'text-muted' : ''); ?>">
                                            <td>
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?php echo e($m['month']); ?>

                                            </td>
                                            <td class="text-end"><?php echo e(number_format($m['trx_count'], 0, ',', '.')); ?></td>
                                            <td class="text-end"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($m['gross_revenue'], 0, ',', '.');
            ?></td>
                                            <td class="text-end"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($m['cumulative'], 0, ',', '.');
            ?></td>
                                            <td class="text-center">
                                                <?php if($m['gross_revenue'] == 0): ?>
                                                    <span class="badge bg-secondary">—</span>
                                                <?php elseif($m['is_below_ptkp']): ?>
                                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Bebas</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Kena Pajak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end <?php echo e($m['taxable_revenue'] > 0 ? 'text-warning fw-semibold' : ''); ?>">
                                                <?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($m['taxable_revenue'], 0, ',', '.');
            ?>
                                            </td>
                                            <td class="text-end <?php echo e($m['tax'] > 0 ? 'text-danger fw-semibold' : ''); ?>">
                                                <?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($m['tax'], 0, ',', '.');
            ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold table-light">
                                        <td>TOTAL</td>
                                        <td class="text-end"><?php echo e(number_format(collect($months)->sum('trx_count'), 0, ',', '.')); ?></td>
                                        <td class="text-end text-primary"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['total_revenue'], 0, ',', '.');
            ?></td>
                                        <td class="text-end">—</td>
                                        <td></td>
                                        <td class="text-end text-warning"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['total_taxable_revenue'], 0, ',', '.');
            ?></td>
                                        <td class="text-end text-danger"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['total_tax'], 0, ',', '.');
            ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart"></i> <strong>Grafik Omset Bulanan <?php echo e($year); ?></strong>
                    </div>
                    <div class="card-body">
                        <canvas id="taxChart" style="width:100%; height:300px;"></canvas>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-xl-4">
                
                <div class="card shadow-sm border-start border-info border-3">
                    <div class="card-header d-flex align-items-center gap-2 bg-info bg-opacity-10">
                        <i class="bi bi-info-circle text-info"></i> <strong>Panduan Bayar Pajak</strong>
                    </div>
                    <div class="card-body small">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <strong class="d-block"><i class="bi bi-clock"></i> Kapan bayar?</strong>
                                Paling lambat <strong>tanggal 15</strong> bulan berikutnya.<br>
                                <span class="text-muted">Contoh: Omset Januari → setor sebelum 15 Februari</span>
                            </li>
                            <li class="mb-3">
                                <strong class="d-block"><i class="bi bi-globe"></i> Dimana bayar?</strong>
                                Buat kode billing di <a href="https://djponline.pajak.go.id" target="_blank" class="text-decoration-none">djponline.pajak.go.id</a>
                                atau lewat ATM / mobile banking.
                            </li>
                            <li class="mb-3">
                                <strong class="d-block"><i class="bi bi-key"></i> Kode Billing</strong>
                                <div class="d-flex gap-3 mt-1">
                                    <div>
                                        <span class="text-muted">KAP:</span><br>
                                        <code class="fs-6">411128</code>
                                    </div>
                                    <div>
                                        <span class="text-muted">KJS:</span><br>
                                        <code class="fs-6">420</code>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">PPh Final Pasal 4(2) UMKM</small>
                            </li>
                            <li class="mb-3">
                                <strong class="d-block"><i class="bi bi-calendar-check"></i> SPT Tahunan</strong>
                                Dilaporkan paling lambat <strong>31 Maret</strong> tahun berikutnya.
                                Gunakan data dari tabel ini untuk mengisi formulir 1770.
                            </li>
                            <li>
                                <strong class="d-block"><i class="bi bi-shield-check"></i> PTKP (PP 55/2022)</strong>
                                Omset kumulatif ≤ <strong>Rp 500 juta/tahun</strong> = <span class="text-success fw-semibold">bebas pajak</span>.
                                Pajak hanya dihitung dari bagian omset yang melebihi Rp 500 juta.
                            </li>
                        </ul>
                    </div>
                </div>

                
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-journal-text"></i> <strong>Ringkasan <?php echo e($year); ?></strong>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Tarif PPh Final</span>
                                <strong><?php echo e($summary['tax_rate_percent']); ?>%</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Batas PTKP</span>
                                <strong><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['ptkp_limit'], 0, ',', '.');
            ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Omset Tahun Ini</span>
                                <strong class="text-primary"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['total_revenue'], 0, ',', '.');
            ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Omset Kena Pajak</span>
                                <strong class="text-warning"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['total_taxable_revenue'], 0, ',', '.');
            ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total PPh Terutang</span>
                                <strong class="text-danger"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($summary['total_tax'], 0, ',', '.');
            ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const months = <?php echo json_encode(collect($months)->pluck('month'), 15, 512) ?>;
            const revenues = <?php echo json_encode(collect($months)->pluck('gross_revenue'), 15, 512) ?>;
            const taxes = <?php echo json_encode(collect($months)->pluck('tax'), 15, 512) ?>;
            const ptkpLine = <?php echo json_encode(array_fill(0, 12, $summary['ptkp_limit'] / 12)) ?>;

            const ctx = document.getElementById('taxChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Omset Bruto',
                            data: revenues,
                            backgroundColor: 'rgba(13, 110, 253, 0.7)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            order: 2,
                        },
                        {
                            label: 'PPh Final 0.5%',
                            data: taxes,
                            backgroundColor: 'rgba(220, 53, 69, 0.8)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            order: 3,
                        },
                        {
                            label: 'Rata-rata PTKP/bulan',
                            data: ptkpLine,
                            type: 'line',
                            borderColor: 'rgba(25, 135, 84, 0.6)',
                            borderDash: [5, 5],
                            borderWidth: 2,
                            pointRadius: 0,
                            fill: false,
                            order: 1,
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
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/tax/index.blade.php ENDPATH**/ ?>