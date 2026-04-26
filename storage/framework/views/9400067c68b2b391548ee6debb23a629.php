<?php $__env->startSection('title', 'Stok Opname'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-clipboard-check"></i> Stok Opname
                </h1>
                <p class="text-muted mb-0">Catat hasil audit stok fisik. Stok produk otomatis disesuaikan ke stok fisik.</p>
            </div>
            <a href="<?php echo e(route('stok-opname.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Catat Stok Opname
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success" role="alert" aria-live="polite">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="stockOpnamesTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="text-end">Stok Sistem</th>
                                <th class="text-end">Stok Fisik</th>
                                <th class="text-end">Selisih</th>
                                <th>Keterangan</th>
                                <th>Dicatat Oleh</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('assets/vendor/jquery-3.7.0.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendor/datatables.min.js')); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#stockOpnamesTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: <?php echo json_encode(route('stok-opname.data'), 15, 512) ?>,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'date_formatted', name: 'date' },
                    { data: 'product_name', name: 'product_id' },
                    { data: 'category_name', orderable: false, searchable: false },
                    { data: 'system_stock', name: 'system_stock', className: 'text-end' },
                    { data: 'physical_stock', name: 'physical_stock', className: 'text-end' },
                    { data: 'difference_display', name: 'difference', className: 'text-end' },
                    { data: 'notes', name: 'notes', defaultContent: '-' },
                    { data: 'user_name', name: 'user_id' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[1, 'desc']],
                language: {
                    url: '<?php echo e(asset('assets/vendor/id.json')); ?>'
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/stock_opnames/index.blade.php ENDPATH**/ ?>