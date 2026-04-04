<?php $__env->startSection('title', 'Manajemen Produk'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container py-4">
        <header class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-box-seam"></i> Produk
                </h1>
                <p class="text-muted mb-0">Kelola produk dan stok.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('produk.create')); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Produk
                </a>
            </div>
        </header>

        <?php if(session('success')): ?>
            <div class="alert alert-success" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <section class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="productsTable" class="table align-middle" style="width:100%">
                        <caption>Daftar produk</caption>
                        <thead>
                            <tr>
                                <th scope="col" style="width:60px;">#</th>
                                <th scope="col">SKU</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Kategori</th>
                                <th scope="col" class="text-end">Harga</th>
                                <th scope="col" class="text-end">Stok</th>
                                <th scope="col" class="text-end" style="width:180px;">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </section>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/datatables.min.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('assets/vendor/jquery-3.7.0.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendor/datatables.min.js')); ?>"></script>
    <script>
        (function() {
            const table = $('#productsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?php echo e(route('produk.data')); ?>',
                    type: 'GET'
                },
                language: {
                    url: '<?php echo e(asset('assets/vendor/id.json')); ?>'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'sku',
                        name: 'sku'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'category',
                        name: 'category.name',
                        defaultContent: ''
                    },
                    {
                        data: 'price',
                        name: 'price',
                        className: 'text-end'
                    },
                    {
                        data: 'stock',
                        name: 'stock',
                        className: 'text-end'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    },
                ],
                order: [
                    [2, 'asc']
                ],
                pageLength: 10,
            });
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/products/index.blade.php ENDPATH**/ ?>