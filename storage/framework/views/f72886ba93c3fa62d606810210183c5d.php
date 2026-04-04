<?php
    /** @var \App\Models\Transaction $t */
    $m = is_string($t->payment_method) ? $t->payment_method : ($t->payment_method?->value ?? '');
?>
<?php if($m === 'cash_tempo'): ?>
    <?php if($t->amount_paid < $t->total): ?>
        <span class="badge bg-danger">UTANG</span>
    <?php else: ?>
        <span class="badge bg-success">LUNAS</span>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/transactions/partials/due-badge.blade.php ENDPATH**/ ?>