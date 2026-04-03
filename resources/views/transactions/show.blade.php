@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0"><i class="bi bi-receipt"></i> Transaksi {{ $trx->invoice_number }}</h1>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('transaksi') }}"><i class="bi bi-arrow-left"></i>
                    Kembali</a>
                <a class="btn btn-primary" href="{{ route('transaksi.struk', $trx) }}" target="_blank"
                    rel="noopener noreferrer"><i class="bi bi-printer"></i> Cetak Struk</a>
                <a class="btn btn-info text-white print-btn" data-base-url="{{ route('transaksi.invoice', $trx) }}" href="{{ route('transaksi.invoice', $trx) }}" target="_blank"
                    rel="noopener noreferrer"><i class="bi bi-file-earmark-text"></i> Cetak Invoice</a>
                <a class="btn btn-success print-btn" data-base-url="{{ route('transaksi.faktur', $trx) }}" href="{{ route('transaksi.faktur', $trx) }}" target="_blank"
                    rel="noopener noreferrer"><i class="bi bi-receipt"></i> Cetak Faktur</a>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-whatsapp"></i> Bagikan
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ 'https://wa.me/?text=' . rawurlencode("*Struk Pembayaran*\nNo: {$trx->invoice_number}\nTotal: Rp " . number_format($trx->total, 0, ',', '.') . "\n\nDownload PDF:\n" . route('transaksi.struk.pdf', $trx)) }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-receipt-cutoff"></i> Struk via WA
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item wa-print-btn" data-wa-base-text="{{ rawurlencode("*Invoice*\nNo: {$trx->invoice_number}\nTotal: Rp " . number_format($trx->total, 0, ',', '.') . "\n\nDownload PDF:\n") }}" data-base-url="{{ route('transaksi.invoice.pdf', $trx) }}" href="{{ 'https://wa.me/?text=' . rawurlencode("*Invoice*\nNo: {$trx->invoice_number}\nTotal: Rp " . number_format($trx->total, 0, ',', '.') . "\n\nDownload PDF:\n" . route('transaksi.invoice.pdf', $trx)) }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-file-earmark-text"></i> Invoice via WA
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item wa-print-btn" data-wa-base-text="{{ rawurlencode("*Faktur Penjualan*\nNo: {$trx->invoice_number}\nTotal: Rp " . number_format($trx->total, 0, ',', '.') . "\n\nDownload PDF:\n") }}" data-base-url="{{ route('transaksi.faktur.pdf', $trx) }}" href="{{ 'https://wa.me/?text=' . rawurlencode("*Faktur Penjualan*\nNo: {$trx->invoice_number}\nTotal: Rp " . number_format($trx->total, 0, ',', '.') . "\n\nDownload PDF:\n" . route('transaksi.faktur.pdf', $trx)) }}" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-receipt"></i> Faktur via WA
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('transaksi.struk.pdf', $trx) }}">
                                <i class="bi bi-file-earmark-pdf"></i> Download Struk PDF
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item print-btn" data-base-url="{{ route('transaksi.invoice.pdf', $trx) }}" href="{{ route('transaksi.invoice.pdf', $trx) }}">
                                <i class="bi bi-file-earmark-pdf"></i> Download Invoice PDF
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item print-btn" data-base-url="{{ route('transaksi.faktur.pdf', $trx) }}" href="{{ route('transaksi.faktur.pdf', $trx) }}">
                                <i class="bi bi-file-earmark-pdf"></i> Download Faktur PDF
                            </a>
                        </li>
                    </ul>
                </div>
                @if(auth()->user()->role === \App\Enums\RoleStatus::ADMIN->value)
                    <form action="{{ route('transaksi.destroy', $trx) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Yakin hapus transaksi {{ $trx->invoice_number }}? Stok produk akan dikembalikan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus Transaksi</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row small">
                            <div class="col-md-6">
                                <div><span class="text-muted">Tanggal</span>
                                    <div>{{ $trx->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="mt-2"><span class="text-muted">Kasir</span>
                                    <div>{{ $trx->user->name ?? '-' }}</div>
                                </div>
                                <div class="mt-2"><span class="text-muted">Metode</span>
                                    <div class="text-uppercase">
                                        @php
                                            $pm = $trx->payment_method->value ?? $trx->payment_method;
                                            if ($pm === 'cash_tempo') {
                                                echo 'TUNAI TEMPO';
                                            } else {
                                                echo strtoupper($pm);
                                            }
                                        @endphp
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @php $s = $trx->status->value ?? $trx->status; @endphp
                                <div><span class="text-muted">Status</span>
                                    <div><span
                                            class="badge {{ $s === 'paid' ? 'bg-success' : ($s === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ strtoupper($s) }}</span>
                                    </div>
                                </div>
                                <div class="mt-2"><span class="text-muted">Total</span>
                                    <div class="fw-semibold">@money($trx->total)</div>
                                </div>
                                <div class="mt-2"><span class="text-muted">Bayar</span>
                                    <div>@money($trx->amount_paid)</div>
                                </div>
                                <div class="mt-2"><span class="text-muted">Kembali</span>
                                    <div>@money($trx->change)</div>
                                </div>
                                @if((($trx->payment_method->value ?? $trx->payment_method) === 'cash_tempo') && $trx->amount_paid < $trx->total)
                                    <div class="mt-2"><span class="text-muted">Piutang</span>
                                        <div>@money($trx->total - $trx->amount_paid)</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-end" style="width:60px;">Qty</th>
                                        <th class="text-end" style="width:120px;">Harga</th>
                                        <th class="text-end" style="width:120px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($trx->details as $d)
                                        <tr>
                                            <td>{{ $d->product->name ?? '#' . $d->product_id }}</td>
                                            <td class="text-end">{{ (int) $d->quantity }}</td>
                                            <td class="text-end">Rp {{ number_format($d->price, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 text-muted">Ringkasan</h2>
                        <div class="small">
                            <div class="d-flex justify-content-between"><span>Subtotal</span><span>@money($trx->subtotal)</span>
                            </div>
                            <div class="d-flex justify-content-between"><span>Diskon</span><span>@money($trx->discount)</span>
                            </div>
                            <div class="d-flex justify-content-between"><span>Pajak</span><span>@money($trx->tax)</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold border-top pt-1">
                                <span>Total</span><span>@money($trx->total)</span>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <a class="btn btn-outline-secondary" href="{{ route('transaksi.struk', $trx) }}"
                                target="_blank" rel="noopener noreferrer"><i class="bi bi-receipt-cutoff"></i> Lihat
                                Struk</a>
                            
                            <div class="border rounded p-2 bg-light">
                                <div class="d-flex align-items-center mb-2 gap-3 small">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="add_signature" value="1">
                                        <label class="form-check-label user-select-none" for="add_signature">
                                            + Tanda Tangan
                                        </label>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="add_stamp" value="1">
                                        <label class="form-check-label user-select-none" for="add_stamp">
                                            + Stempel
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-outline-info flex-fill print-btn" data-base-url="{{ route('transaksi.invoice', $trx) }}"
                                        href="{{ route('transaksi.invoice', $trx) }}" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-printer"></i> Cetak Html
                                    </a>
                                    <a class="btn btn-outline-success flex-fill print-btn" data-base-url="{{ route('transaksi.faktur', $trx) }}"
                                        href="{{ route('transaksi.faktur', $trx) }}" target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-printer"></i> Faktur Html
                                    </a>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <a class="btn btn-info text-white flex-fill print-btn" data-base-url="{{ route('transaksi.invoice.pdf', $trx) }}"
                                        href="{{ route('transaksi.invoice.pdf', $trx) }}">
                                        <i class="bi bi-file-earmark-pdf"></i> Download Invoice PDF
                                    </a>
                                    <a class="btn btn-success flex-fill print-btn" data-base-url="{{ route('transaksi.faktur.pdf', $trx) }}"
                                        href="{{ route('transaksi.faktur.pdf', $trx) }}">
                                        <i class="bi bi-file-earmark-pdf"></i> Download Faktur PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                        @php
                            $pm = $trx->payment_method->value ?? $trx->payment_method;
                        @endphp
                        @if($pm === 'cash_tempo' && $trx->amount_paid < $trx->total)
                            <section class="card shadow-sm mt-3">
                                <div class="card-body">
                                    <h5 class="h6">Bayar Sisa (Piutang)</h5>
                                    <form action="{{ route('transaksi.lunas', $trx) }}" method="POST" class="row g-2">
                                        @csrf
                                        <div class="col-12 col-md-6">
                                            <label for="paid_amount" class="form-label">Jumlah Bayar</label>
                                            <input type="text" name="paid_amount" id="paid_amount" class="form-control" placeholder="Rp 0" required>
                                        </div>
                                        <div class="col-12 col-md-6 d-grid">
                                            <button type="submit" class="btn btn-success">Tandai Lunas</button>
                                        </div>
                                    </form>
                                </div>
                            </section>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sigCheck = document.getElementById('add_signature');
        const stampCheck = document.getElementById('add_stamp');
        const printBtns = document.querySelectorAll('.print-btn');

        function updatePrintUrls() {
            if (!sigCheck || !stampCheck) return;
            const params = new URLSearchParams();
            if (sigCheck.checked) params.append('signature', '1');
            if (stampCheck.checked) params.append('stamp', '1');
            const qs = params.toString() ? '?' + params.toString() : '';
            
            printBtns.forEach(btn => {
                const baseUrl = btn.getAttribute('data-base-url');
                if (baseUrl) {
                    btn.setAttribute('href', baseUrl + qs);
                }
            });

            // Update WA share links
            document.querySelectorAll('.wa-print-btn').forEach(btn => {
                const baseText = btn.getAttribute('data-wa-base-text');
                const baseUrl = btn.getAttribute('data-base-url');
                if (baseText && baseUrl) {
                    const fullUrl = baseUrl + qs;
                    btn.setAttribute('href', 'https://wa.me/?text=' + baseText + encodeURIComponent(fullUrl));
                }
            });
        }

        if (sigCheck) sigCheck.addEventListener('change', updatePrintUrls);
        if (stampCheck) stampCheck.addEventListener('change', updatePrintUrls);

        // Run once on page load to set initial URLs
        updatePrintUrls();

        // Also update when dropdown is opened (Bootstrap 5 event)
        const dropdown = document.querySelector('.dropdown-toggle');
        if (dropdown) {
            dropdown.addEventListener('click', updatePrintUrls);
        }

        const el = document.getElementById('paid_amount');
        if (el) {
            const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
            el.addEventListener('input', function() {
                let digits = this.value.replace(/[^0-9]/g, '');
                this.value = digits ? 'Rp ' + fmt(digits) : '';
            });
            
            // Clean value before form submit
            const form = el.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Strip "Rp " and dots before submitting
                    let cleaned = el.value.replace(/[^0-9]/g, '');
                    if (!cleaned) {
                        e.preventDefault();
                        alert('Jumlah bayar harus diisi');
                        return false;
                    }
                    el.value = cleaned;
                });
            }
        }
    });
</script>
@endpush
