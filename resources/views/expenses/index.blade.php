@extends('layouts.app')

@section('title', 'Pengeluaran')

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0">Pengeluaran</h1>
            <a href="{{ route('pengeluaran.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Pengeluaran</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert" aria-live="polite">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-3">
                        <label for="filterCategory" class="form-label">Kategori</label>
                        <select id="filterCategory" class="form-select">
                            <option value="">Semua</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->value }}" {{ ($category === $cat->value) ? 'selected' : '' }}>
                                    {{ $cat->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="filterFrom" class="form-label">Dari Tanggal</label>
                        <input type="date" id="filterFrom" class="form-control" value="{{ $from ?? '' }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="filterTo" class="form-label">Ke Tanggal</label>
                        <input type="date" id="filterTo" class="form-control" value="{{ $to ?? '' }}">
                    </div>
                    <div class="col-12 col-md-3 d-grid align-self-end">
                        <button id="btnFilter" class="btn btn-outline-primary"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="expensesTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                                <th>Pemasukan Oleh</th>
                                <th>Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let table = $('#expensesTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: @json(route('pengeluaran.data')),
                    data: function(d) {
                        d.from = document.getElementById('filterFrom').value;
                        d.to = document.getElementById('filterTo').value;
                        d.category = document.getElementById('filterCategory').value;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'date', name: 'expense_date' },
                    { data: 'category_label', name: 'category' },
                    { data: 'amount', name: 'amount' },
                    { data: 'description', name: 'description' },
                    { data: 'user', name: 'user_id' },
                    { data: 'has_file', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                }
            });

            document.getElementById('btnFilter').addEventListener('click', function() {
                table.draw();
            });

            ['filterFrom', 'filterTo', 'filterCategory'].forEach(id => {
                document.getElementById(id).addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        table.draw();
                    }
                });
            });
        });
    </script>
@endpush
