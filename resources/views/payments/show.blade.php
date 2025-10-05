@extends('layouts.app')

@section('title', 'Pembayaran QRIS')

@section('content')
    <section class="container py-4">
        <h1 class="h4 mb-3">Pembayaran QRIS • No: {{ $transaction->invoice_number }}</h1>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <p class="mb-2">Total: <strong>Rp {{ number_format($transaction->total, 0, ',', '.') }}</strong>
                        </p>
                        @if ($payment->qr_url)
                            <img src="{{ $payment->qr_url }}" alt="QRIS" class="img-fluid" style="max-width: 320px;">
                        @elseif ($payment->qr_string)
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=320x320&data={{ urlencode($payment->qr_string) }}"
                                alt="QRIS" class="img-fluid" style="max-width: 320px;">
                            <p class="small text-muted mt-2">Tampilkan QR ini ke pelanggan untuk dipindai.</p>
                        @else
                            <div class="alert alert-warning">QR belum tersedia.</div>
                        @endif
                        <div id="payStatus" class="mt-3 text-muted">Menunggu pembayaran…</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('script')
        <script>
            (function() {
                const url = @json(route('pembayaran.status', $transaction));
                const el = document.getElementById('payStatus');

                function check() {
                    fetch(url, {
                            cache: 'no-store'
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.status === 'settlement') {
                                el.textContent = 'Pembayaran berhasil.';
                                setTimeout(() => location.href = @json(route('kasir')), 1200);
                            } else if (['expire', 'cancel', 'deny', 'failure'].includes(d.status)) {
                                el.textContent = 'Pembayaran tidak berhasil (' + d.status + ').';
                            } else {
                                el.textContent = 'Menunggu pembayaran…';
                            }
                        })
                        .catch(() => {})
                        .finally(() => setTimeout(check, 3000));
                }
                check();
            })();
        </script>
    @endpush
@endsection
