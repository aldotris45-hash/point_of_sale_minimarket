@extends('layouts.app')

@section('title', 'Buku Kas')

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0">Buku Kas</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('buku-kas.create', ['type' => 'in']) }}" class="btn btn-success"><i class="bi bi-plus-circle"></i> Catat Pemasukan</a>
                <a href="{{ route('buku-kas.create', ['type' => 'out']) }}" class="btn btn-danger"><i class="bi bi-dash-circle"></i> Catat Pengeluaran</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert" aria-live="polite">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-2">
                        <label for="filterType" class="form-label">Tipe Kas</label>
                        <select id="filterType" class="form-select">
                            <option value="">Semua (Keluar & Masuk)</option>
                            <option value="in" {{ ($type ?? '') === 'in' ? 'selected' : '' }}>Masuk (+)</option>
                            <option value="out" {{ ($type ?? '') === 'out' ? 'selected' : '' }}>Keluar (-)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="filterCategory" class="form-label">Kategori</label>
                        <select id="filterCategory" class="form-select">
                            <option value="">Semua Kategori</option>
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
                    <div class="col-12 col-md-2 d-grid align-self-end">
                        <button id="btnFilter" class="btn btn-outline-primary"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="cashTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                                <th>User</th>
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
            let table = $('#cashTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: @json(route('buku-kas.data')),
                    data: function(d) {
                        d.from = document.getElementById('filterFrom').value;
                        d.to = document.getElementById('filterTo').value;
                        d.category = document.getElementById('filterCategory').value;
                        d.type = document.getElementById('filterType').value;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'date', name: 'date' },
                    { data: 'type_badge', name: 'type', orderable: false, searchable: false },
                    { data: 'category_label', name: 'category' },
                    { data: 'amount', name: 'amount' },
                    { data: 'description', name: 'description' },
                    { data: 'user', name: 'user_id' },
                    { data: 'action', orderable: false, searchable: false },
                ],
                language: {
                    url: @json(asset('assets/vendor/id.json'))
                }
            });

            document.getElementById('btnFilter').addEventListener('click', function() {
                table.draw();
            });

            ['filterFrom', 'filterTo', 'filterCategory', 'filterType'].forEach(id => {
                document.getElementById(id).addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        table.draw();
                    }
                });
                document.getElementById(id).addEventListener('change', function(e) {
                    // Let user click filter btn initially but this is also fine
                });
            });
        });
    </script>
@endpush
