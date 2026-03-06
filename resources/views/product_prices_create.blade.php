@extends('layouts.app')

@section('title', 'Tambah Harga Produk')

@section('content')
    <section class="container-fluid py-4">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Form Input Harga Harian Produk Sayur</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('harga-produk.store') }}" method="POST" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="product_id" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="cost_price" class="form-label">Harga Beli (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="cost_price" name="cost_price" 
                                        value="{{ old('cost_price') }}"
                                        class="form-control @error('cost_price') is-invalid @enderror" 
                                        placeholder="0" required>
                                </div>
                                @error('cost_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="selling_price" class="form-label">Harga Jual (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="selling_price" name="selling_price" 
                                        value="{{ old('selling_price') }}"
                                        class="form-control @error('selling_price') is-invalid @enderror" 
                                        placeholder="0" required>
                                </div>
                                <small class="form-text text-muted">
                                    Margin: <span id="marginDisplay">0%</span>
                                </small>
                                @error('selling_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="price_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" id="price_date" name="price_date" 
                                    value="{{ old('price_date', date('Y-m-d')) }}"
                                    class="form-control @error('price_date') is-invalid @enderror" required>
                                @error('price_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan (Opsional)</label>
                                <textarea id="notes" name="notes" rows="2" 
                                    class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Contoh: Harga naik karena cuaca, stok terbatas, dsb.">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('harga-produk.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const costInput = document.getElementById('cost_price');
            const sellingInput = document.getElementById('selling_price');
            const marginDisplay = document.getElementById('marginDisplay');

            const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
            const calculateMargin = () => {
                let cost = parseInt(costInput.value.replace(/[^0-9]/g, '')) || 0;
                let selling = parseInt(sellingInput.value.replace(/[^0-9]/g, '')) || 0;
                
                if (cost > 0) {
                    let margin = ((selling - cost) / cost * 100).toFixed(1);
                    marginDisplay.textContent = margin + '%';
                } else {
                    marginDisplay.textContent = '0%';
                }
            };

            [costInput, sellingInput].forEach(input => {
                input.addEventListener('input', function() {
                    let digits = this.value.replace(/[^0-9]/g, '');
                    this.value = digits ? fmt(digits) : '';
                    calculateMargin();
                });
            });

            const form = costInput.closest('form');
            if (form) {
                form.addEventListener('submit', function() {
                    costInput.value = costInput.value.replace(/[^0-9]/g, '') || '0';
                    sellingInput.value = sellingInput.value.replace(/[^0-9]/g, '') || '0';
                });
            }

            calculateMargin();
        });
    </script>
@endpush
