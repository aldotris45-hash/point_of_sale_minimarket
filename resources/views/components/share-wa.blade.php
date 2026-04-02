@props([
    'transaction',
    'type' => 'struk',
    'btnClass' => '',
    'showDownload' => true,
])

@php
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
@endphp

<span class="no-print" style="display:inline-flex; gap:6px;">
    <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
        style="{{ $waStyle }}" title="Bagikan via WhatsApp">
        💬 WhatsApp
    </a>
    @if($showDownload)
        <a href="{{ $pdfUrl }}" style="{{ $pdfStyle }}" title="Download PDF">
            📥 PDF
        </a>
    @endif
</span>
