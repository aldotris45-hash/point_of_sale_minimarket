<?php $__env->startSection('title', 'Arus Kas'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0"><i class="bi bi-arrow-left-right"></i> Arus Kas</h1>
        </div>

        
        <form id="cashFlowFilter" method="GET" action="<?php echo e(route('arus-kas')); ?>" class="card shadow-sm mb-3">
            <div class="card-body row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label for="filterFrom" class="form-label">Dari Tanggal</label>
                    <input type="date" id="filterFrom" name="from" class="form-control" value="<?php echo e($from); ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label for="filterTo" class="form-label">Sampai Tanggal</label>
                    <input type="date" id="filterTo" name="to" class="form-control" value="<?php echo e($to); ?>">
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <a href="<?php echo e(route('arus-kas.export-pdf', ['from' => $from, 'to' => $to])); ?>" class="btn btn-outline-danger" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Ekspor PDF
                        </a>
                        <a href="<?php echo e(route('arus-kas')); ?>" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Reset</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Terapkan</button>
                    </div>
                </div>
            </div>
        </form>

        
        <div class="row g-3 mb-4">
            
            <div class="col-12 col-md-4 col-xl">
                <div class="card shadow-sm border-start border-success border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-cart-check text-success fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Omset Penjualan</p>
                        </div>
                        <h4 class="mb-0 text-success"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($totalIncome, 0, ',', '.');
            ?></h4>
                        <small class="text-muted"><?php echo e(number_format($totalTransactions, 0, ',', '.')); ?> transaksi</small>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 col-xl">
                <div class="card shadow-sm border-start border-warning border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-box-seam text-warning fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Pembelian Barang</p>
                        </div>
                        <h4 class="mb-0 text-warning"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($totalPurchase, 0, ',', '.');
            ?></h4>
                        <small class="text-muted">Modal barang dagangan</small>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 col-xl">
                <div class="card shadow-sm border-start border-danger border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-arrow-up-circle text-danger fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Pengeluaran Ops.</p>
                        </div>
                        <h4 class="mb-0 text-danger"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($totalOperational, 0, ',', '.');
            ?></h4>
                        <small class="text-muted">Gaji, listrik, dll</small>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-xl">
                <div class="card shadow-sm border-start border-secondary border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-wallet text-secondary fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Total Pengeluaran</p>
                        </div>
                        <h4 class="mb-0 text-secondary"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($totalExpense, 0, ',', '.');
            ?></h4>
                        <small class="text-muted">Barang + Operasional</small>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-xl">
                <div class="card shadow-sm border-start <?php echo e($netBalance >= 0 ? 'border-primary' : 'border-danger'); ?> border-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-graph-up-arrow <?php echo e($netBalance >= 0 ? 'text-primary' : 'text-danger'); ?> fs-3 me-2"></i>
                            <p class="text-muted mb-0 small">Laba Bersih</p>
                        </div>
                        <h4 class="mb-0 <?php echo e($netBalance >= 0 ? 'text-primary' : 'text-danger'); ?>">
                            <?php echo e($netBalance < 0 ? '-' : ''); ?><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format(abs($netBalance), 0, ',', '.');
            ?>
                        </h4>
                        <?php if($netBalance < 0): ?>
                            <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Rugi</small>
                        <?php else: ?>
                            <small class="text-success"><i class="bi bi-check-circle"></i> Margin <?php echo e(number_format($marginPercent, 1)); ?>%</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            
            <div class="col-12 col-xl-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart"></i> <strong>Grafik Arus Kas Harian</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="cashFlowChart" style="width:100%; height:350px;"></canvas>
                    </div>
                </div>

                
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
                                    <?php $__currentLoopData = $dailyData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($day['date']); ?></td>
                                            <td class="text-end text-success"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($day['income'], 0, ',', '.');
            ?></td>
                                            <td class="text-end text-warning"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($day['purchase'], 0, ',', '.');
            ?></td>
                                            <td class="text-end text-danger"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($day['operational'], 0, ',', '.');
            ?></td>
                                            <td class="text-end <?php echo e($day['balance'] >= 0 ? 'text-primary' : 'text-danger'); ?>">
                                                <?php echo e($day['balance'] >= 0 ? '' : '-'); ?><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format(abs($day['balance']), 0, ',', '.');
            ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold table-light">
                                        <td>TOTAL</td>
                                        <td class="text-end text-success"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($totalIncome, 0, ',', '.');
            ?></td>
                                        <td class="text-end text-warning"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($totalPurchase, 0, ',', '.');
            ?></td>
                                        <td class="text-end text-danger"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($totalOperational, 0, ',', '.');
            ?></td>
                                        <td class="text-end <?php echo e($netBalance >= 0 ? 'text-primary' : 'text-danger'); ?>">
                                            <?php echo e($netBalance >= 0 ? '' : '-'); ?><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format(abs($netBalance), 0, ',', '.');
            ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-xl-4">
                
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart"></i> <strong>Pengeluaran per Kategori</strong>
                    </div>
                    <div class="card-body">
                        <?php if($expenseByCategory->isEmpty()): ?>
                            <div class="text-muted text-center py-3">Tidak ada pengeluaran di periode ini.</div>
                        <?php else: ?>
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
                                        <?php $__currentLoopData = $expenseByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $pct = $totalExpense > 0 ? ($cat['total'] / $totalExpense * 100) : 0;
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php if($cat['category'] === 'Pembelian Barang'): ?>
                                                        <i class="bi bi-box-seam text-warning"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-dash-circle text-danger"></i>
                                                    <?php endif; ?>
                                                    <?php echo e($cat['category']); ?>

                                                </td>
                                                <td class="text-end"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($cat['total'], 0, ',', '.');
            ?></td>
                                                <td class="text-end"><?php echo e(number_format($pct, 1)); ?>%</td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold table-light">
                                            <td>Total</td>
                                            <td class="text-end"><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($totalExpense, 0, ',', '.');
            ?></td>
                                            <td class="text-end">100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            
                            <div class="mt-3">
                                <?php $__currentLoopData = $expenseByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $pct = $totalExpense > 0 ? ($cat['total'] / $totalExpense * 100) : 0;
                                        $color = $cat['category'] === 'Pembelian Barang' ? 'bg-warning' : (['bg-danger', 'bg-info', 'bg-primary', 'bg-secondary'][$loop->index % 4]);
                                    ?>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small">
                                            <span><?php echo e($cat['category']); ?></span>
                                            <span><?php echo e(number_format($pct, 1)); ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar <?php echo e($color); ?>" style="width: <?php echo e($pct); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="card shadow-sm mt-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-calculator"></i> <strong>Ringkasan</strong>
                    </div>
                    <div class="card-body">
                        <?php
                            $days = max(1, $dailyData->count());
                            $avgIncome = $totalIncome / $days;
                            $avgExpense = $totalExpense / $days;
                            $avgProfit = $netBalance / $days;
                        ?>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rata-rata Omset/Hari</span>
                                <strong><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($avgIncome, 0, ',', '.');
            ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rata-rata Pengeluaran/Hari</span>
                                <strong><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format($avgExpense, 0, ',', '.');
            ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rata-rata Laba/Hari</span>
                                <strong class="<?php echo e($avgProfit >= 0 ? 'text-success' : 'text-danger'); ?>">
                                    <?php echo e($avgProfit < 0 ? '-' : ''); ?><?php
                $__cur = app(\App\Services\Settings\SettingsServiceInterface::class)->currency();
                $__code = is_string($__cur) ? strtoupper($__cur) : 'IDR';
                $__prefix = $__code === 'IDR' ? 'Rp ' : ($__code . ' ');
                echo $__prefix . number_format(abs($avgProfit), 0, ',', '.');
            ?>
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Margin Laba</span>
                                <strong class="<?php echo e($marginPercent >= 0 ? 'text-success' : 'text-danger'); ?>">
                                    <?php echo e(number_format($marginPercent, 1)); ?>%
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rasio Pembelian Barang</span>
                                <strong>
                                    <?php if($totalIncome > 0): ?>
                                        <?php echo e(number_format(($totalPurchase / $totalIncome) * 100, 1)); ?>%
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Hari dgn Pendapatan</span>
                                <strong><?php echo e($dailyData->where('income', '>', 0)->count()); ?> / <?php echo e($dailyData->count()); ?> hari</strong>
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
            const ctx = document.getElementById('cashFlowChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chartLabels, 15, 512) ?>,
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: <?php echo json_encode($chartIncome, 15, 512) ?>,
                            backgroundColor: 'rgba(25, 135, 84, 0.7)',
                            borderColor: 'rgba(25, 135, 84, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Pembelian Barang',
                            data: <?php echo json_encode($chartPurchase, 15, 512) ?>,
                            backgroundColor: 'rgba(255, 193, 7, 0.7)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Pengeluaran Ops',
                            data: <?php echo json_encode($chartOperational, 15, 512) ?>,
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
                        url: <?php echo json_encode(asset('assets/vendor/id.json'), 15, 512) ?>
                    }
                });
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/cash_flow/index.blade.php ENDPATH**/ ?>