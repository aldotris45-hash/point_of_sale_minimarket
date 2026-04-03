@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0"><i class="bi bi-receipt"></i> Transaksi</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="filterForm" class="card shadow-sm mb-3">
            <div class="card-body row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Cari</label>
                    <input type="text" class="form-control" name="q" value="{{ $q }}"
                        placeholder="No/ket.">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" {{ $status === $s->value ? 'selected' : '' }}>
                                {{ strtoupper($s->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Metode</label>
                    <select class="form-select" name="method">
                        <option value="">Semua</option>
                        @foreach ($methods as $m)
                            <option value="{{ $m->value }}" {{ $method === $m->value ? 'selected' : '' }}>
                                {{ strtoupper($m->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Piutang</label>
                    <select class="form-select" name="due">
                        <option value=""{{ empty($due) ? ' selected' : '' }}>Semua</option>
                        <option value="utang"{{ ($due === 'utang') ? ' selected' : '' }}>Belum Lunas</option>
                        <option value="lunas"{{ ($due === 'lunas') ? ' selected' : '' }}>Sudah Lunas</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Pelanggan</label>
                    <select class="form-select" name="customer_id">
                        <option value="">Semua</option>
                        @foreach ($customers as $cust)
                            <option value="{{ $cust->id }}" {{ ($customer_id == $cust->id) ? 'selected' : '' }}>{{ $cust->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Dari</label>
                    <input type="date" class="form-control" name="from" value="{{ $from }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" class="form-control" name="to" value="{{ $to }}">
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label">Per halaman</label>
                    <select class="form-select" name="per_page">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="col-12 col-md-12 d-flex gap-2 justify-content-end">
                    <button type="button" id="btnReset" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i>
                        Reset</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Terapkan</button>
                </div>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table id="transactionsTable" class="table align-middle mb-0" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Metode</th>
                            <th>Piutang</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                            <th class="text-end" style="width:160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mt-2">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-muted"><i class="bi bi-calculator"></i> Total Keseluruhan (Hasil Filter)</span>
                <span class="fw-bold fs-5 text-primary" id="grandTotalDisplay">Rp 0</span>
            </div>
        </div>
    </section>

    {{-- Modal Edit Tanggal Transaksi --}}
    <div class="modal fade" id="editTrxDateModal" tabindex="-1" aria-labelledby="editTrxDateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <form id="editTrxDateForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTrxDateModalLabel">
                            <i class="bi bi-calendar-event"></i> Ubah Tanggal
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Invoice: <strong id="editTrxInvoice"></strong></p>
                        <label for="editTrxDateInput" class="form-label">Tanggal Baru</label>
                        <input type="date" class="form-control" id="editTrxDateInput" name="date" required
                               max="{{ date('Y-m-d') }}">
                        <small class="text-muted mt-1 d-block">Data pembayaran & buku kas terkait akan otomatis disesuaikan.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables.min.css') }}">
@endpush

@push('script')
    <script src="{{ asset('assets/vendor/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.min.js') }}"></script>
    <script>
        (function() {
            const $form = $('#filterForm');
            const $perPage = $form.find('select[name="per_page"]');
            const table = $('#transactionsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('transaksi.data') }}',
                    type: 'GET',
                    data: function(d) {
                        const fd = Object.fromEntries(new FormData($form[0]).entries());
                        return Object.assign(d, fd);
                    }
                },
                language: {
                    url: '{{ asset('assets/vendor/id.json') }}'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'invoice',
                        name: 'invoice_number'
                    },
                    {
                        data: 'date',
                        name: 'created_at'
                    },
                    {
                        data: 'cashier',
                        name: 'user.name',
                        defaultContent: '',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'method',
                        name: 'payment_method'
                    },
                    {
                        data: 'due_badge',
                        name: 'due',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'total',
                        name: 'total',
                        className: 'text-end'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    },
                ],
                order: [
                    [2, 'desc']
                ],
                pageLength: 10,
                drawCallback: function(settings) {
                    const json = settings.json;
                    if (json && json.grand_total !== undefined) {
                        const fmt = Number(json.grand_total || 0).toLocaleString('id-ID');
                        $('#grandTotalDisplay').text('Rp ' + fmt);
                    }
                },
            });

            const initialLen = parseInt($perPage.val() || '10', 10);
            if (!Number.isNaN(initialLen)) {
                table.page.len(initialLen).draw();
            }

            $form.on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            $perPage.on('change', function() {
                const len = parseInt(this.value || '10', 10);
                table.page.len(len).draw();
            });

            $('#btnReset').on('click', function() {
                $form[0].reset();
                $perPage.val('10');
                table.page.len(10).draw();
                table.ajax.reload();
            });

            // Edit Date Modal handler
            const trxModal = new bootstrap.Modal(document.getElementById('editTrxDateModal'));
            const trxForm = document.getElementById('editTrxDateForm');
            const trxDateInput = document.getElementById('editTrxDateInput');
            const trxInvoice = document.getElementById('editTrxInvoice');

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-edit-trx-date');
                if (!btn) return;

                trxForm.action = btn.dataset.url;
                trxDateInput.value = btn.dataset.date;
                trxInvoice.textContent = btn.dataset.invoice;
                trxModal.show();
            });
        })();
    </script>
@endpush
