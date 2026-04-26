<?php $__env->startSection('title', 'Edit Produk'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container py-4">
        <header class="mb-3">
            <h1 class="h3 d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i> Edit Produk
            </h1>
        </header>

        <section class="card shadow-sm">
            <div class="card-body">
                <form action="<?php echo e(route('produk.update', $product)); ?>" method="POST" novalidate>
                    <?php echo method_field('PUT'); ?>
                    <?php echo $__env->make('products._form', ['product' => $product, 'categories' => $categories], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <div class="d-flex gap-2 mt-3">
                        <a href="<?php echo e(route('produk.index')); ?>" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i> Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/products/edit.blade.php ENDPATH**/ ?>