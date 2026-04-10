<ul class="list-unstyled mb-0" style="font-size: 0.85em;">
    @foreach ($t->details as $detail)
        <li>
            <span class="fw-semibold">{{ $detail->product->name ?? 'Produk Dihapus' }}</span> 
            <span class="text-muted">x{{ $detail->quantity }}</span>
        </li>
    @endforeach
</ul>
