<?php
    /** @var string $status */
    $s = is_string($status) ? $status : ($status->value ?? '');
    $class = match($s) {
        'paid' => 'bg-success',
        'pending' => 'bg-warning text-dark',
        'settlement' => 'bg-success',
        'expire', 'cancel', 'deny', 'failure' => 'bg-danger',
        default => 'bg-secondary',
    };
?>
<span class="badge <?php echo e($class); ?>"><?php echo e(strtoupper($s)); ?></span>
<?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/partials/status-badge.blade.php ENDPATH**/ ?>