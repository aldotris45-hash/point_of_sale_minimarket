@extends('layouts.app')

@section('title', 'Catat Stok Opname')

@section('content')
    <section class="container py-4">
        <div class="mb-3">
            <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-clipboard-check"></i> Catat Stok Opname
            </h1>
            <p class="text-muted mb-0">Pilih produk, masukkan stok fisik. Stok produk akan otomatis disesuaikan.</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('stok-opname.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror"
                                value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="product_id" class="form-label">Produk <span class="text-danger">*</span></label>
                            <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="">- Pilih Produk -</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                        data-stock="{{ $product->stock }}"
                                        data-category="{{ $product->category?->name ?? '-' }}"
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} ({{ $product->category?->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="system_stock" class="form-label">Stok Sistem</label>
                            <input type="number" id="system_stock" class="form-control" readonly
                                style="background-color: #f8f9fa; font-weight: 600;" value="0">
                            <small class="text-muted">Otomatis dari data produk</small>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="physical_stock" class="form-label">Stok Fisik <span class="text-danger">*</span></label>
                            <input type="number" name="physical_stock" id="physical_stock"
                                class="form-control @error('physical_stock') is-invalid @enderror"
                                value="{{ old('physical_stock', 0) }}" min="0" required>
                            @error('physical_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="difference" class="form-label">Selisih</label>
                            <input type="text" id="difference" class="form-control" readonly
                                style="background-color: #f8f9fa; font-weight: 700;" value="0">
                            <small class="text-muted">Fisik − Sistem</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Keterangan</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                            rows="2" maxlength="500" placeholder="Misal: Busuk, Hilang, Salah hitung, dll.">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Stok Opname
                        </button>
                        <a href="{{ route('stok-opname.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('product_id');
            const systemStockInput = document.getElementById('system_stock');
            const physicalStockInput = document.getElementById('physical_stock');
            const differenceInput = document.getElementById('difference');

            function updateDifference() {
                const system = parseInt(systemStockInput.value) || 0;
                const physical = parseInt(physicalStockInput.value) || 0;
                const diff = physical - system;
                differenceInput.value = (diff > 0 ? '+' : '') + diff;

                // Warna selisih
                if (diff < 0) {
                    differenceInput.style.color = '#dc3545'; // merah
                } else if (diff > 0) {
                    differenceInput.style.color = '#198754'; // hijau
                } else {
                    differenceInput.style.color = '#6c757d'; // abu
                }
            }

            productSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const stock = selected.getAttribute('data-stock') || 0;
                systemStockInput.value = stock;
                updateDifference();
            });

            physicalStockInput.addEventListener('input', updateDifference);

            // Hitung awal
            updateDifference();
        });
    </script>
@endpush
