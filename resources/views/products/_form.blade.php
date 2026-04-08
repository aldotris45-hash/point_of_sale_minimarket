@csrf
<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="sku" class="form-label">SKU</label>
        <input id="sku" name="sku" type="text" class="form-control @error('sku') is-invalid @enderror"
            value="{{ old('sku', $product->sku ?? '') }}" maxlength="100" required>
        @error('sku')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="name" class="form-label">Nama Produk</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $product->name ?? '') }}" maxlength="255" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="category_id" class="form-label">Kategori</label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror"
            required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $id => $label)
                <option value="{{ $id }}" @selected(old('category_id', $product->category_id ?? '') == $id)>{{ $label }}</option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-3">
        <label for="price_display" class="form-label">Harga</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input id="price_display" name="price_display" type="text"
                class="form-control @error('price') is-invalid @enderror" inputmode="decimal"
                value="{{ (float) old('price', $product->price ?? 0) == 0 ? '0' : number_format((float) old('price', $product->price ?? 0), 2, ',', '.') }}"
                placeholder="0" autocomplete="off" required>
            <input id="price" name="price" type="hidden" value="{{ old('price', $product->price ?? 0) }}">
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12 col-md-3">
        <label for="stock" class="form-label">Stok</label>
        <input id="stock" name="stock" type="number" min="0" step="any"
            class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock ?? 0) }}">
        @error('stock')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-3">
        <label for="min_stock" class="form-label">Stok Minimal</label>
        <input id="min_stock" name="min_stock" type="number" min="0" step="any"
            class="form-control @error('min_stock') is-invalid @enderror"
            value="{{ old('min_stock', $product->min_stock ?? 0) }}">
        @error('min_stock')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Notifikasi akan muncul jika stok <= minimal.</div>
        </div>

        <div class="col-12 col-md-3">
            <label for="expiry_date" class="form-label">Tanggal Kadaluarsa</label>
            <input id="expiry_date" name="expiry_date" type="date"
                class="form-control @error('expiry_date') is-invalid @enderror"
                value="{{ old('expiry_date', optional($product->expiry_date ?? null)->format('Y-m-d')) }}">
            @error('expiry_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Kosongkan jika tidak berlaku.</div>
        </div>
    </div>

    {{-- ══════════════════════════════════════ --}}
    {{-- SECTION PROMO                          --}}
    {{-- ══════════════════════════════════════ --}}
    <div class="col-12">
        <hr class="my-2">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch"
                    id="promo_toggle"
                    {{ old('promo_price', $product->promo_price ?? null) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="promo_toggle">
                    🔥 Tandai sebagai Promo
                </label>
            </div>
        </div>

        <div id="promo_fields" class="row g-3" style="{{ old('promo_price', $product->promo_price ?? null) ? '' : 'display:none;' }}">
            <div class="col-12 col-md-4">
                <label for="promo_price_display" class="form-label">Harga Promo <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-danger text-white">Rp</span>
                    <input id="promo_price_display" type="text" inputmode="decimal"
                        class="form-control @error('promo_price') is-invalid @enderror"
                        value="{{ old('promo_price', $product->promo_price ?? '') ? number_format((float) old('promo_price', $product->promo_price ?? 0), 0, ',', '.') : '' }}"
                        placeholder="Harga setelah diskon" autocomplete="off">
                    <input id="promo_price" name="promo_price" type="hidden"
                        value="{{ old('promo_price', $product->promo_price ?? '') }}">
                    @error('promo_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-text">Harga normal (Rp {{ number_format((float) old('price', $product->price ?? 0), 0, ',', '.') }}) akan jadi harga coret di katalog.</div>
            </div>
            <div class="col-12 col-md-4">
                <label for="promo_label" class="form-label">Label Promo <span class="text-muted">(opsional)</span></label>
                <input id="promo_label" name="promo_label" type="text"
                    class="form-control @error('promo_label') is-invalid @enderror"
                    value="{{ old('promo_label', $product->promo_label ?? '') }}"
                    placeholder="e.g. Flash Sale, Hemat 30%" maxlength="50">
                @error('promo_label')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Kosongkan untuk label otomatis "PROMO".</div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            (function() {
                // ── Helper functions ─────────────────────────────
                function normalizeToNumber(str) {
                    if (!str) return '';
                    str = String(str).replace(/[^0-9,]/g, '');
                    const parts = str.split(',');
                    let intPart = parts[0] || '';
                    let decPart = parts[1] || '';
                    intPart = intPart.replace(/^0+(?=\d)/, '');
                    if (decPart.length > 2) decPart = decPart.slice(0, 2);
                    return decPart ? (intPart + '.' + decPart) : intPart;
                }

                function formatRupiahDisplay(str) {
                    if (!str) return '';
                    str = String(str).replace(/[^0-9,]/g, '');
                    const parts = str.split(',');
                    let intPart = parts[0] || '';
                    let decPart = parts[1] || '';
                    intPart = intPart.replace(/^0+(?=\d)/, '');
                    intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    if (decPart.length > 0) decPart = decPart.slice(0, 2);
                    return decPart ? (intPart + ',' + decPart) : intPart;
                }

                // ── Harga utama ──────────────────────────────────
                const priceDisplay = document.getElementById('price_display');
                const priceHidden  = document.getElementById('price');

                if (priceDisplay && priceHidden) {
                    function syncHidden() {
                        priceHidden.value = normalizeToNumber(priceDisplay.value);
                    }

                    function onInput() {
                        const pos = priceDisplay.selectionStart;
                        const beforeLen = priceDisplay.value.length;
                        priceDisplay.value = formatRupiahDisplay(priceDisplay.value);
                        const afterLen = priceDisplay.value.length;
                        const delta = afterLen - beforeLen;
                        priceDisplay.setSelectionRange(pos + delta, pos + delta);
                        syncHidden();
                    }

                    if (priceHidden.value && !priceDisplay.value) {
                        const normalized = String(priceHidden.value).replace(/\./g, ',');
                        priceDisplay.value = formatRupiahDisplay(normalized);
                    } else {
                        syncHidden();
                    }

                    priceDisplay.addEventListener('input', onInput);

                    const form = priceDisplay.closest('form');
                    if (form) {
                        form.addEventListener('submit', function() {
                            syncHidden();
                        });
                    }
                }

                // ── Promo toggle ─────────────────────────────────
                const promoToggle      = document.getElementById('promo_toggle');
                const promoFields      = document.getElementById('promo_fields');
                const promoPriceDisplay = document.getElementById('promo_price_display');
                const promoPriceHidden  = document.getElementById('promo_price');

                if (promoToggle && promoFields) {
                    promoToggle.addEventListener('change', function () {
                        promoFields.style.display = this.checked ? '' : 'none';
                        if (!this.checked) {
                            if (promoPriceDisplay) promoPriceDisplay.value = '';
                            if (promoPriceHidden)  promoPriceHidden.value  = '';
                        }
                    });
                }

                if (promoPriceDisplay && promoPriceHidden) {
                    function syncPromo() {
                        promoPriceHidden.value = normalizeToNumber(promoPriceDisplay.value);
                    }

                    // Init: format nilai awal dari DB
                    if (promoPriceHidden.value && !promoPriceDisplay.value) {
                        const normalized = String(promoPriceHidden.value).replace(/\./g, ',');
                        promoPriceDisplay.value = formatRupiahDisplay(normalized);
                    }

                    promoPriceDisplay.addEventListener('input', function () {
                        const pos    = this.selectionStart;
                        const before = this.value.length;
                        this.value   = formatRupiahDisplay(this.value);
                        const delta  = this.value.length - before;
                        this.setSelectionRange(pos + delta, pos + delta);
                        syncPromo();
                    });

                    const form2 = promoPriceDisplay.closest('form');
                    if (form2) {
                        form2.addEventListener('submit', syncPromo);
                    }
                }
            })();
        </script>
    @endpush
