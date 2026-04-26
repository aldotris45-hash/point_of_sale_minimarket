<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?php echo e($transaction->invoice_number); ?></title>
    <link href="<?php echo e(asset('assets/vendor/bootstrap.min.css')); ?>" rel="stylesheet" />
    <style>
        body {
            background: #f8f9fa;
        }

        .receipt {
            width: 320px;
            margin: 12px auto;
            background: #fff;
            border: 1px solid #e9ecef;
            padding: 12px;
        }

        .receipt .hr {
            border-top: 1px dashed #dee2e6;
            margin: .5rem 0;
        }

        .receipt .muted {
            color: #6c757d;
            font-size: 11px;
        }

        .receipt table {
            width: 100%;
            font-size: 12px;
        }

        .badge-promo {
            display: inline-block;
            background: #dc3545;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .4px;
            padding: 1px 4px;
            border-radius: 3px;
            vertical-align: middle;
            margin-left: 3px;
            line-height: 1.4;
        }

        .price-original {
            text-decoration: line-through;
            color: #adb5bd;
            font-size: 10px;
            display: block;
            text-align: right;
        }

        .text-end {
            text-align: right;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .receipt {
                margin: 0;
                border: 0;
                width: auto;
            }
        }
    </style>
    <?php if(request()->boolean('print')): ?>
        <script>
            window.addEventListener('load', function() {
                window.print();
            });
        </script>
    <?php endif; ?>
    <?php
        $fmt = fn($n) => number_format((float) $n, 0, ',', '.');
    ?>
</head>

<body>
    <div class="receipt">
        <div class="text-center">
            <?php if($store_logo): ?>
                <img src="<?php echo e(asset($store_logo)); ?>" alt="Logo" width="48" height="48" class="mb-2" />
            <?php endif; ?>
            <div class="fw-bold"><?php echo e($store_name); ?></div>
            <?php if($store_address): ?>
                <div class="muted"><?php echo e($store_address); ?></div>
            <?php endif; ?>
            <?php if($store_phone): ?>
                <div class="muted"><?php echo e($store_phone); ?></div>
            <?php endif; ?>
            <?php if($store_bank_account): ?>
                <div class="muted">Rek: <?php echo e($store_bank_account); ?></div>
            <?php endif; ?>
        </div>
        <div class="hr"></div>
        <div class="d-flex flex-column" style="font-size:12px;">
            <div class="d-flex justify-content-between"><span
                    class="muted">No</span><span><?php echo e($transaction->invoice_number); ?></span></div>
            <div class="d-flex justify-content-between"><span
                    class="muted">Tanggal</span><span><?php echo e($transaction->created_at->format('d/m/Y H:i')); ?></span></div>
            <div class="d-flex justify-content-between"><span
                    class="muted">Kasir</span><span><?php echo e($transaction->user->name ?? '-'); ?></span></div>
        </div>
        <div class="hr"></div>
        <table class="table table-sm mb-2">
            <thead>
                <tr class="small">
                    <th>Item</th>
                    <th class="text-end" style="width:44px;">Qty</th>
                    <th class="text-end" style="width:74px;">Harga</th>
                    <th class="text-end" style="width:74px;">Total</th>
                </tr>
            </thead>
            <tbody class="small">
                <?php $__currentLoopData = $transaction->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <?php echo e($d->product->name ?? '#' . $d->product_id); ?>

                            <?php if($d->is_promo): ?>
                                <span class="badge-promo">PROMO</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?php echo e((int) $d->quantity); ?></td>
                        <td class="text-end">
                            <?php if($d->is_promo && $d->original_price): ?>
                                <span class="price-original"><?php echo e($fmt($d->original_price)); ?></span>
                            <?php endif; ?>
                            <?php echo e($fmt($d->price)); ?>

                        </td>
                        <td class="text-end"><?php echo e($fmt($d->total)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div class="hr"></div>
        <div class="small">
            <div class="d-flex justify-content-between">
                <span>Subtotal</span><span><?php echo e($fmt($transaction->subtotal)); ?></span></div>
            <div class="d-flex justify-content-between">
                <span>Diskon</span><span><?php echo e($fmt($transaction->discount)); ?></span></div>
            <div class="d-flex justify-content-between"><span>Pajak</span><span><?php echo e($fmt($transaction->tax)); ?></span>
            </div>
            <div class="d-flex justify-content-between fw-bold border-top pt-1">
                <span>Total</span><span><?php echo e($fmt($transaction->total)); ?></span></div>
        </div>
        <div class="hr"></div>
        <div class="small">
            <div class="d-flex justify-content-between">
                <span>Metode</span><span><?php
                    $m = $transaction->payment_method->value ?? (string) $transaction->payment_method;
                    echo $m === 'cash_tempo' ? 'TUNAI TEMPO' : strtoupper($m);
                ?></span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Bayar</span><span><?php echo e($fmt($transaction->amount_paid)); ?></span></div>
            <div class="d-flex justify-content-between">
                <span>Kembali</span><span><?php echo e($fmt($transaction->change)); ?></span></div>
            <?php if(($transaction->payment_method->value ?? (string) $transaction->payment_method) === 'cash_tempo' && $transaction->amount_paid < $transaction->total): ?>
                <div class="d-flex justify-content-between">
                    <span>Piutang</span><span><?php echo e($fmt($transaction->total - $transaction->amount_paid)); ?></span></div>
            <?php endif; ?>
            <?php if(($transaction->payment_method->value ?? (string) $transaction->payment_method) === 'cash_tempo' && $store_bank_account): ?>
                <div class="hr"></div>
                <div class="muted">Silakan transfer ke rekening di atas untuk pelunasan.</div>
            <?php endif; ?>
        </div>
        <div class="hr"></div>
        <div class="text-center muted">Simpan struk sebagai bukti pembelian</div>
        <div class="text-center small">Terima kasih telah berbelanja!</div>

        <div class="no-print mt-3 d-grid gap-2">
            <a class="btn btn-primary btn-sm"
                href="<?php echo e(route('transaksi.struk', ['transaction' => $transaction->id, 'print' => 1])); ?>"
                target="_blank" rel="noopener noreferrer"><i class="bi bi-printer"></i> Cetak</a>
            <?php if (isset($component)) { $__componentOriginale3b09e7757ac2c1ab0efe80ffc34c307 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3b09e7757ac2c1ab0efe80ffc34c307 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.share-wa','data' => ['transaction' => $transaction,'type' => 'struk']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('share-wa'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['transaction' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($transaction),'type' => 'struk']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3b09e7757ac2c1ab0efe80ffc34c307)): ?>
<?php $attributes = $__attributesOriginale3b09e7757ac2c1ab0efe80ffc34c307; ?>
<?php unset($__attributesOriginale3b09e7757ac2c1ab0efe80ffc34c307); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3b09e7757ac2c1ab0efe80ffc34c307)): ?>
<?php $component = $__componentOriginale3b09e7757ac2c1ab0efe80ffc34c307; ?>
<?php unset($__componentOriginale3b09e7757ac2c1ab0efe80ffc34c307); ?>
<?php endif; ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?php echo e(route('kasir')); ?>">Kembali ke Kasir</a>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/transactions/receipt.blade.php ENDPATH**/ ?>