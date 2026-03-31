@extends('layouts.app')

@section('title', 'Riwayat Harga Produk - ' . $product->name)

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 mb-2">Riwayat Harga Jual: {{ $product->name }}</h1>
                <p class="text-muted mb-0">Catatan: Perubahan harga jual terdokumentasi dengan tanggal dan waktu</p>
            </div>
            <a href="{{ route('harga-produk.index') }}" class="btn btn-outline-secondary">← Kembali</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="historyTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Harga Jual (Rp)</th>
                                <th>Tanggal Berlaku</th>
                                <th>Waktu Perubahan</th>
                                <th>Catatan/Alasan</th>
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
            $('#historyTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: @json(route('harga-produk.history-data', $product))
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'selling_price', name: 'selling_price' },
                    { data: 'effective_date', name: 'effective_date' },
                    { data: 'changed_at', name: 'changed_at' },
                    { data: 'notes', name: 'notes' },
                ],
                order: [[3, 'desc']],
                language: {
                    url: @json(asset('assets/vendor/id.json'))
                }
            });
        });
    </script>
@endpush
