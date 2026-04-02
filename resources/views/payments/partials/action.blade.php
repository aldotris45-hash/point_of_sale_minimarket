@php
    /** @var \App\Models\Payment $p */
@endphp
<div class="d-flex justify-content-end gap-1">
    <a class="btn btn-sm btn-outline-primary" href="{{ route('transaksi.show', $p->transaction_id) }}"><i class="bi bi-eye"></i></a>
    <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"
        href="{{ route('transaksi.struk', $p->transaction_id) }}"><i class="bi bi-receipt-cutoff"></i></a>
</div>
