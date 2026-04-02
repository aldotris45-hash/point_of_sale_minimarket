@php
    /** @var string $status */
    $s = is_string($status) ? $status : ($status->value ?? '');
    $class = match($s) {
        'paid' => 'bg-success',
        'pending' => 'bg-warning text-dark',
        'settlement' => 'bg-success',
        'expire', 'cancel', 'deny', 'failure' => 'bg-danger',
        default => 'bg-secondary',
    };
@endphp
<span class="badge {{ $class }}">{{ strtoupper($s) }}</span>
