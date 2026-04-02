@php
    /** @var \App\Models\Transaction $t */
    $m = is_string($t->payment_method) ? $t->payment_method : ($t->payment_method?->value ?? '');
@endphp
@if ($m === 'cash_tempo')
    @if ($t->amount_paid < $t->total)
        <span class="badge bg-danger">UTANG</span>
    @else
        <span class="badge bg-success">LUNAS</span>
    @endif
@endif
