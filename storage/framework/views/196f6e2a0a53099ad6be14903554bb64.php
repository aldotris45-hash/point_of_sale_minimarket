<?php
    /** @var \App\Models\Payment $p */
?>
<div class="d-flex justify-content-end gap-1">
    <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('transaksi.show', $p->transaction_id)); ?>"><i class="bi bi-eye"></i></a>
    <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"
        href="<?php echo e(route('transaksi.struk', $p->transaction_id)); ?>"><i class="bi bi-receipt-cutoff"></i></a>
    <form action="<?php echo e(route('pembayaran.destroy', $p->id)); ?>" method="POST" class="d-inline"
          onsubmit="return confirm('Yakin hapus data pembayaran ini?')">
        <?php echo csrf_field(); ?>
        <?php echo method_field('DELETE'); ?>
        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
    </form>
</div>
<?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/payments/partials/action.blade.php ENDPATH**/ ?>