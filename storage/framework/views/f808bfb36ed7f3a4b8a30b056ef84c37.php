<?php
    /** @var \App\Models\Transaction $t */
?>
<div class="d-flex justify-content-end gap-1">
    <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('transaksi.show', $t)); ?>"><i class="bi bi-eye"></i></a>
    <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(route('transaksi.struk', $t)); ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-receipt-cutoff"></i></a>
    <?php if(auth()->check() && auth()->user()->role === \App\Enums\RoleStatus::ADMIN->value): ?>
        <button type="button" class="btn btn-sm btn-outline-info btn-edit-trx-date"
            data-url="<?php echo e(route('transaksi.update-date', $t)); ?>"
            data-date="<?php echo e($t->created_at->format('Y-m-d')); ?>"
            data-invoice="<?php echo e($t->invoice_number); ?>"
            title="Ubah tanggal">
            <i class="bi bi-calendar-event"></i>
        </button>
        <form action="<?php echo e(route('transaksi.destroy', $t)); ?>" method="POST" class="d-inline"
            onsubmit="return confirm('Yakin hapus transaksi <?php echo e($t->invoice_number); ?>? Stok akan dikembalikan.')">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
        </form>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/transactions/partials/action.blade.php ENDPATH**/ ?>