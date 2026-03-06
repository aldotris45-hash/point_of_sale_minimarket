@extends('layouts.app')

@section('title', 'Manajemen Harga Produk Sayur')

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0">Manajemen Harga Produk Sayur</h1>
            <a href="{{ route('harga-produk.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Input Harga Harian</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert" aria-live="polite">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="filterProduct" class="form-label">Nama Produk</label>
                        <select id="filterProduct" class="form-select">
                            <option value="">Semua Produk</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 d-grid align-self-end">
                        <button id="btnFilter" class="btn btn-outline-primary"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="pricesTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Produk</th>
                                <th>Harga Beli (Rp)</th>
                                <th>Harga Jual (Rp)</th>
                                <th>Margin</th>
                                <th>Tanggal</th>
                                <th>Catatan</th>
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
            let table = $('#pricesTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: @json(route('harga-produk.data')),
                    data: function(d) {
                        d.product_id = document.getElementById('filterProduct').value;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'product_name', name: 'product.name' },
                    { data: 'cost_price', name: 'cost_price' },
                    { data: 'selling_price', name: 'selling_price' },
                    { 
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            if (data.cost_price > 0) {
                                let margin = ((data.selling_price - data.cost_price) / data.cost_price * 100).toFixed(1);
                                return margin + '%';
                            }
                            return '-';
                        }
                    },
                    { data: 'date', name: 'price_date' },
                    { data: 'notes', name: 'notes' },
                    { data: 'action', orderable: false, searchable: false },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                }
            });

            document.getElementById('btnFilter').addEventListener('click', function() {
                table.draw();
            });

            document.getElementById('filterProduct').addEventListener('change', function() {
                table.draw();
            });
        });
    </script>
@endpush
