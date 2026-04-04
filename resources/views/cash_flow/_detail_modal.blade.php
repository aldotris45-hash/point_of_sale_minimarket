<div class="modal-header bg-light">
    <h5 class="modal-title">
        @if($type === 'income')
            <i class="bi bi-arrow-down-left-circle text-success"></i> Rincian Pemasukan
        @elseif($type === 'purchase')
            <i class="bi bi-box-seam text-warning"></i> Rincian Pembelian Barang
        @elseif($type === 'operational')
            <i class="bi bi-arrow-up-right-circle text-danger"></i> Rincian Pengeluaran Ops.
        @endif
        <span class="fs-6 text-muted ms-2">{{ $date }}</span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-0">
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Waktu</th>
                    <th>Keterangan</th>
                    <th class="text-end pe-3">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp

                @if($type === 'income')
                    @foreach($salesItems as $item)
                        @php $total += $item['amount']; @endphp
                        <tr>
                            <td class="ps-3 text-muted" style="width: 80px;">{{ $item['time'] }}</td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success me-1">{{ $item['label'] }}</span>
                                {{ $item['note'] }}
                            </td>
                            <td class="text-end pe-3 fw-medium">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    @foreach($otherIncomes as $item)
                        @php $total += $item['amount']; @endphp
                        <tr>
                            <td class="ps-3 text-muted">{{ $item['time'] }}</td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info me-1">{{ $item['label'] }}</span>
                                {{ $item['note'] }}
                            </td>
                            <td class="text-end pe-3 fw-medium">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                @elseif($type === 'purchase')
                    @foreach($purchases as $item)
                        @php $total += $item['amount']; @endphp
                        <tr>
                            <td class="ps-3 text-muted" style="width: 80px;">{{ $item['time'] }}</td>
                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-warning me-1">{{ $item['label'] }}</span>
                                {{ $item['note'] }}
                            </td>
                            <td class="text-end pe-3 fw-medium">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                @elseif($type === 'operational')
                    @foreach($operationals as $item)
                        @php $total += $item['amount']; @endphp
                        <tr>
                            <td class="ps-3 text-muted" style="width: 80px;">{{ $item['time'] }}</td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger me-1">{{ $item['label'] }}</span>
                                {{ $item['note'] }}
                            </td>
                            <td class="text-end pe-3 fw-medium">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif

                @if($total == 0)
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">Tidak ada riwayat transaksi detail di hari ini.</td>
                    </tr>
                @endif
            </tbody>
            @if($total > 0)
            <tfoot class="table-light">
                <tr>
                    <th colspan="2" class="text-end">Total Harian</th>
                    <th class="text-end pe-3 fs-5">Rp {{ number_format($total, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
<div class="modal-footer bg-light">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>
