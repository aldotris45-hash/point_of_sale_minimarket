@php
    /** @var \App\Models\Transaction $t */
@endphp
<div class="d-flex justify-content-end gap-1">
    <a class="btn btn-sm btn-outline-primary" href="{{ route('transaksi.show', $t) }}"><i class="bi bi-eye"></i></a>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('transaksi.struk', $t) }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-receipt-cutoff"></i></a>
    @if (auth()->check() && auth()->user()->role === \App\Enums\RoleStatus::ADMIN->value)
        <form action="{{ route('transaksi.destroy', $t) }}" method="POST" class="d-inline"
            onsubmit="return confirm('Yakin hapus transaksi {{ $t->invoice_number }}? Stok akan dikembalikan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
        </form>
    @endif
</div>
