<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo e($appStoreName ?? config('app.name', 'POS')); ?> - <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    <meta name="description" content="" />
    <meta name="author" content="Mariani Krismonika" />
    <link rel="icon" type="image/png" href="<?php echo e(asset('assets/images/logo.png')); ?>" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Lato:wght@300;400;700&display=swap"
        rel="stylesheet">

    
    <link href="<?php echo e(asset('assets/vendor/bootstrap.min.css')); ?>" rel="stylesheet" />
    <link href="<?php echo e(asset('assets/vendor/datatables.min.css')); ?>" rel="stylesheet" />

    
    <link href="<?php echo e(asset('assets/vendor/bootstrap-icons-1.13.1/bootstrap-icons.min.css')); ?>" rel="stylesheet" />

    
    <link href="<?php echo e(asset('assets/css/custom-css.css')); ?>" rel="stylesheet" />

    <?php echo $__env->yieldPushContent('css'); ?>
</head>

<body>
    <?php echo $__env->make('layouts.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>
    <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <script src="<?php echo e(asset('assets/vendor/jquery-3.7.0.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendor/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/vendor/datatables.min.js')); ?>"></script>

    
    <script src="<?php echo e(asset('assets/js/custom-js.js')); ?>"></script>

    <?php echo $__env->yieldPushContent('script'); ?>
</body>

</html>
<?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/layouts/app.blade.php ENDPATH**/ ?>