<?php $__env->startSection('title', 'Supplier'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-building"></i> Daftar Supplier
                </h1>
                <p class="text-muted mb-0">Kelola data supplier / pemasok barang.</p>
            </div>
            <a href="<?php echo e(route('supplier.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Supplier
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
                    <table id="suppliersTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Supplier</th>
                                <th>Alamat</th>
                                <th>Telepon/HP</th>
                                <th>Email</th>
                                <th>Keterangan</th>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#suppliersTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: <?php echo json_encode(route('supplier.data'), 15, 512) ?>,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'address', name: 'address', defaultContent: '-' },
                    { data: 'phone', name: 'phone', defaultContent: '-' },
                    { data: 'email', name: 'email', defaultContent: '-' },
                    { data: 'notes', name: 'notes', defaultContent: '-' },
                    { data: 'action', orderable: false, searchable: false },
                ],
                language: {
                    url: '<?php echo e(asset('assets/vendor/id.json')); ?>'
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/suppliers/index.blade.php ENDPATH**/ ?>