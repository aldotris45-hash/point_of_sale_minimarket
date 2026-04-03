@extends('layouts.app')

@section('title', 'Catat Barang Masuk')

@section('content')
    <section class="container py-4">
        <div class="mb-3">
            <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-box-arrow-in-down"></i> Catat Barang Masuk
            </h1>
            <p class="text-muted mb-0">Isi data barang masuk. Stok produk akan otomatis bertambah & harga diperbarui.</p>
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
                            <label for="productSearch" class="form-label">Produk <span class="text-danger">*</span></label>
                            <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id') }}">
                            <div class="position-relative">
                                <input type="text" id="productSearch" class="form-control @error('product_id') is-invalid @enderror"
                                    placeholder="Ketik nama produk..." autocomplete="off" required>
                                <div id="productDropdown" class="dropdown-menu w-100 shadow" style="max-height:250px; overflow-y:auto;"></div>
                            </div>
                            @error('product_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-3">
                            <label for="purchase_price" class="form-label">Harga Beli (Modal) <span class="text-danger">*</span></label>
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
                        <div class="col-12 col-md-3">
                            <label for="selling_price" class="form-label">Harga Jual <small class="text-muted">(opsional)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="selling_price" id="selling_price"
                                    class="form-control @error('selling_price') is-invalid @enderror"
                                    value="{{ old('selling_price') }}" min="0" placeholder="Isi untuk update harga jual">
                            </div>
                            @error('selling_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="quantity"
                                class="form-control @error('quantity') is-invalid @enderror"
                                value="{{ old('quantity', 1) }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Total</label>
                            <input type="text" id="totalDisplay" class="form-control" readonly
                                style="background-color: #f8f9fa; font-weight: 600;" value="Rp 0">
                        </div>
                    </div>

                    {{-- Info margin --}}
                    <div id="marginInfo" class="alert alert-info py-2 d-none mb-3">
                        <i class="bi bi-info-circle"></i>
                        <span id="marginText"></span>
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
            // === Data produk ===
            const products = @json($products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category?->name ?? '-',
                'price' => (float) $p->price,
                'stock' => $p->stock,
            ]));

            const searchInput = document.getElementById('productSearch');
            const hiddenInput = document.getElementById('product_id');
            const dropdown = document.getElementById('productDropdown');
            const priceInput = document.getElementById('purchase_price');
            const sellingInput = document.getElementById('selling_price');
            const qtyInput = document.getElementById('quantity');
            const totalDisplay = document.getElementById('totalDisplay');
            const marginInfo = document.getElementById('marginInfo');
            const marginText = document.getElementById('marginText');

            // === Searchable dropdown ===
            function renderDropdown(filtered) {
                dropdown.innerHTML = '';
                if (filtered.length === 0) {
                    dropdown.innerHTML = '<div class="dropdown-item text-muted">Tidak ditemukan</div>';
                    dropdown.classList.add('show');
                    return;
                }
                filtered.forEach(p => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'dropdown-item';
                    item.innerHTML = `<strong>${p.name}</strong> <small class="text-muted">(${p.category}) — Stok: ${p.stock}</small>`;
                    item.addEventListener('click', function() {
                        selectProduct(p);
                    });
                    dropdown.appendChild(item);
                });
                dropdown.classList.add('show');
            }

            function selectProduct(p) {
                hiddenInput.value = p.id;
                searchInput.value = p.name;
                sellingInput.value = p.price > 0 ? p.price : '';
                dropdown.classList.remove('show');
                updateTotal();
                updateMargin();
            }

            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                if (q.length === 0) {
                    dropdown.classList.remove('show');
                    hiddenInput.value = '';
                    return;
                }
                const filtered = products.filter(p =>
                    p.name.toLowerCase().includes(q) ||
                    p.category.toLowerCase().includes(q)
                ).slice(0, 20);
                renderDropdown(filtered);
            });

            searchInput.addEventListener('focus', function() {
                if (this.value.trim().length > 0) {
                    const q = this.value.toLowerCase().trim();
                    const filtered = products.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        p.category.toLowerCase().includes(q)
                    ).slice(0, 20);
                    renderDropdown(filtered);
                }
            });

            // Close dropdown on outside click
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });

            // === Total & Margin ===
            function formatRupiah(num) {
                return 'Rp ' + Number(num).toLocaleString('id-ID');
            }

            function updateTotal() {
                const price = parseFloat(priceInput.value) || 0;
                const qty = parseInt(qtyInput.value) || 0;
                totalDisplay.value = formatRupiah(price * qty);
            }

            function updateMargin() {
                const buy = parseFloat(priceInput.value) || 0;
                const sell = parseFloat(sellingInput.value) || 0;
                if (buy > 0 && sell > 0) {
                    const margin = ((sell - buy) / buy * 100).toFixed(1);
                    const profit = sell - buy;
                    marginInfo.classList.remove('d-none');
                    if (profit >= 0) {
                        marginInfo.className = 'alert alert-success py-2 mb-3';
                        marginText.innerHTML = `Margin: <strong>${formatRupiah(profit)}</strong> per unit (<strong>${margin}%</strong>)`;
                    } else {
                        marginInfo.className = 'alert alert-danger py-2 mb-3';
                        marginText.innerHTML = `⚠ Rugi: <strong>${formatRupiah(Math.abs(profit))}</strong> per unit (<strong>${margin}%</strong>)`;
                    }
                } else {
                    marginInfo.classList.add('d-none');
                }
            }

            priceInput.addEventListener('input', function() { updateTotal(); updateMargin(); });
            sellingInput.addEventListener('input', updateMargin);
            qtyInput.addEventListener('input', updateTotal);

            // === Pre-fill jika ada old value ===
            @if(old('product_id'))
                const oldProduct = products.find(p => p.id == {{ old('product_id') }});
                if (oldProduct) {
                    searchInput.value = oldProduct.name;
                    hiddenInput.value = oldProduct.id;
                }
            @endif

            updateTotal();
            updateMargin();
        });
    </script>
@endpush
