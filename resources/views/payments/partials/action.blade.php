@php
    /** @var \App\Models\Payment $p */
@endphp
<div class="d-flex justify-content-end gap-1">
    <a class="btn btn-sm btn-outline-primary" href="{{ route('transaksi.show', $p->transaction_id) }}"><i class="bi bi-eye"></i></a>
    <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer"
        href="{{ route('transaksi.struk', $p->transaction_id) }}"><i class="bi bi-receipt-cutoff"></i></a>
    <form action="{{ route('pembayaran.destroy', $p->id) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Yakin hapus data pembayaran ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
    </form>
</div>
