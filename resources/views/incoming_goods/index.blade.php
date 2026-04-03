@extends('layouts.app')

@section('title', 'Barang Masuk')

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-box-arrow-in-down"></i> Barang Masuk
                </h1>
                <p class="text-muted mb-0">Catat barang masuk dari supplier. Stok produk otomatis bertambah.</p>
            </div>
            <a href="{{ route('barang-masuk.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Catat Barang Masuk
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert" aria-live="polite">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="incomingGoodsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="text-end">Harga Beli</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Total</th>
                                <th>Dicatat Oleh</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal Edit Tanggal --}}
    <div class="modal fade" id="editDateModal" tabindex="-1" aria-labelledby="editDateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <form id="editDateForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDateModalLabel">
                            <i class="bi bi-calendar-event"></i> Ubah Tanggal
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <label for="editDateInput" class="form-label">Tanggal Baru</label>
                        <input type="date" class="form-control" id="editDateInput" name="date" required
                               max="{{ date('Y-m-d') }}">
                        <small class="text-muted mt-1 d-block">Data harga & riwayat terkait akan otomatis disesuaikan.</small>
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

@push('script')
    <script src="{{ asset('assets/vendor/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#incomingGoodsTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: @json(route('barang-masuk.data')),
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'date_formatted', name: 'date' },
                    { data: 'supplier_name', name: 'supplier_id' },
                    { data: 'product_name', name: 'product_id' },
                    { data: 'category_name', orderable: false, searchable: false },
                    { data: 'purchase_price', name: 'purchase_price', className: 'text-end' },
                    { data: 'quantity', name: 'quantity', className: 'text-end' },
                    { data: 'total', name: 'total', className: 'text-end' },
                    { data: 'user_name', name: 'user_id' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end' },
                ],
                order: [[1, 'desc']],
                language: {
                    url: '{{ asset('assets/vendor/id.json') }}'
                }
            });

            // Edit Date Modal handler
            const modal = new bootstrap.Modal(document.getElementById('editDateModal'));
            const form = document.getElementById('editDateForm');
            const dateInput = document.getElementById('editDateInput');

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-edit-date');
                if (!btn) return;

                form.action = btn.dataset.url;
                dateInput.value = btn.dataset.date;
                modal.show();
            });
        });
    </script>
@endpush
