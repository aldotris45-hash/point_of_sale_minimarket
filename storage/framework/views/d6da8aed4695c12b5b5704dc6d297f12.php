<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'transaction',
    'type' => 'struk',
    'btnClass' => '',
    'showDownload' => true,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'transaction',
    'type' => 'struk',
    'btnClass' => '',
    'showDownload' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $routeMap = [
        'struk'   => 'transaksi.struk.pdf',
        'invoice' => 'transaksi.invoice.pdf',
        'faktur'  => 'transaksi.faktur.pdf',
    ];
    $labelMap = [
        'struk'   => 'Struk',
        'invoice' => 'Invoice',
        'faktur'  => 'Faktur',
    ];

    $pdfUrl = route($routeMap[$type], $transaction);
    $label = $labelMap[$type] ?? 'Struk';

    $pm = $transaction->payment_method->value ?? (string) $transaction->payment_method;
    $methodLabel = $pm === 'cash_tempo' ? 'Tunai Tempo' : ucfirst($pm);

    $fmt = fn($n) => number_format((float) $n, 0, ',', '.');

    // Build WhatsApp message
    $waText = "*{$label} Pembayaran*\n"
        . "No: {$transaction->invoice_number}\n"
        . "Tanggal: {$transaction->created_at->format('d/m/Y H:i')}\n"
        . "Metode: {$methodLabel}\n"
        . "Total: Rp {$fmt($transaction->total)}\n";

    if ($pm === 'cash_tempo' && $transaction->amount_paid < $transaction->total) {
        $waText .= "Terbayar: Rp {$fmt($transaction->amount_paid)}\n"
            . "Sisa: Rp {$fmt($transaction->total - $transaction->amount_paid)}\n";
    }

    $waText .= "\nDownload PDF:\n{$pdfUrl}";
    $waUrl = 'https://wa.me/?text=' . rawurlencode($waText);

    // Inline styles for standalone pages (invoice/faktur without Bootstrap)
    $waStyle = 'display:inline-block; padding:8px 16px; font-size:12pt; cursor:pointer; background:#25D366; color:#fff; border:none; border-radius:4px; text-decoration:none;';
    $pdfStyle = 'display:inline-block; padding:8px 16px; font-size:12pt; cursor:pointer; background:#fff; color:#0d6efd; border:1px solid #0d6efd; border-radius:4px; text-decoration:none;';
?>

<span class="no-print" style="display:inline-flex; gap:6px;">
    <a href="<?php echo e($waUrl); ?>" target="_blank" rel="noopener noreferrer"
        style="<?php echo e($waStyle); ?>" title="Bagikan via WhatsApp">
        💬 WhatsApp
    </a>
    <?php if($showDownload): ?>
        <a href="<?php echo e($pdfUrl); ?>" style="<?php echo e($pdfStyle); ?>" title="Download PDF">
            📥 PDF
        </a>
    <?php endif; ?>
</span>
<?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/components/share-wa.blade.php ENDPATH**/ ?>