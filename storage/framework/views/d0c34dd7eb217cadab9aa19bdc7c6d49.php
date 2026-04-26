<?php $__env->startSection('title', 'Tambah Kategori'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container py-4">
        <header class="mb-3">
            <h1 class="h3 d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Tambah Kategori
            </h1>
        </header>

        <section class="card shadow-sm">
            <div class="card-body">
                <form action="<?php echo e(route('kategori.store')); ?>" method="POST" novalidate>
                    <?php echo $__env->make('categories._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <div class="d-flex gap-2">
                        <a href="<?php echo e(route('kategori.index')); ?>" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/categories/create.blade.php ENDPATH**/ ?>