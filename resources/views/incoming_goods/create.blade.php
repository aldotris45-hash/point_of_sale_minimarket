@extends('layouts.app')

@section('title', 'Catat Barang Masuk')

@section('content')
    <section class="container py-4">
        <div class="mb-3">
            <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-box-arrow-in-down"></i> Catat Barang Masuk
            </h1>
            <p class="text-muted mb-0">Isi data barang masuk. Stok produk akan otomatis bertambah.</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('barang-masuk.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror"
                                value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                <option value="">- Tanpa Supplier -</option>
                                @foreach ($suppliers as $id => $name)
                                    <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="product_id" class="form-label">Produk <span class="text-danger">*</span></label>
                            <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="">- Pilih Produk -</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                        data-price="{{ $product->price }}"
                                        data-category="{{ $product->category?->name ?? '-' }}"
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} ({{ $product->category?->name ?? '-' }}) — Stok: {{ $product->stock }}
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
                            <label for="purchase_price" class="form-label">Harga Beli (per satuan) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="purchase_price" id="purchase_price"
                                    class="form-control @error('purchase_price') is-invalid @enderror"
                                    value="{{ old('purchase_price', 0) }}" min="0" required>
                            </div>
                            @error('purchase_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="quantity"
                                class="form-control @error('quantity') is-invalid @enderror"
                                value="{{ old('quantity', 1) }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Total</label>
                            <input type="text" id="totalDisplay" class="form-control" readonly
                                style="background-color: #f8f9fa; font-weight: 600;" value="Rp 0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Keterangan</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                            rows="2" maxlength="500" placeholder="Keterangan tambahan (opsional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Barang Masuk
                        </button>
                        <a href="{{ route('barang-masuk.index') }}" class="btn btn-outline-secondary">
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
            const priceInput = document.getElementById('purchase_price');
            const qtyInput = document.getElementById('quantity');
            const totalDisplay = document.getElementById('totalDisplay');

            function formatRupiah(num) {
                return 'Rp ' + Number(num).toLocaleString('id-ID');
            }

            function updateTotal() {
                const price = parseFloat(priceInput.value) || 0;
                const qty = parseInt(qtyInput.value) || 0;
                totalDisplay.value = formatRupiah(price * qty);
            }

            productSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const price = selected.getAttribute('data-price') || 0;
                priceInput.value = price;
                updateTotal();
            });

            priceInput.addEventListener('input', updateTotal);
            qtyInput.addEventListener('input', updateTotal);

            // Hitung total awal
            updateTotal();
        });
    </script>
@endpush
