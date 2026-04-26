<ul class="list-unstyled mb-0" style="font-size: 0.85em;">
    <?php $__currentLoopData = $t->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li>
            <span class="fw-semibold"><?php echo e($detail->product->name ?? 'Produk Dihapus'); ?></span>
            <span class="text-muted">x<?php echo e(rtrim(rtrim(number_format((float) $detail->quantity, 3, '.', ''), '0'), '.')); ?></span>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/transactions/partials/items.blade.php ENDPATH**/ ?>