@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
    <section class="container-fluid py-4">
        <header class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-cash-stack"></i> Kasir
                </h1>
                <p class="text-muted mb-0">Scan SKU atau cari produk, tambahkan ke keranjang, lalu proses pembayaran.</p>
            </div>
            <div class="text-muted small">
                Diskon: {{ number_format($discount_percent, 2, ',', '.') }}% • Pajak:
                {{ number_format($tax_percent, 2, ',', '.') }}% • Mata Uang: {{ $currency }}
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <section class="row g-3">
            <div class="col-12 col-lg-8">
                <section class="card shadow-sm h-100">
                    <div class="card-body">
                        <form id="productSearchForm" class="row g-2" role="search" aria-label="Pencarian produk"
                            onsubmit="return false;">
                            <div class="col-12 col-md-8">
                                <label for="q" class="visually-hidden">Cari produk</label>
                                <div class="dropdown w-100 position-relative" id="searchDropdown">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                        <input id="q" type="search" class="form-control"
                                            placeholder="Scan SKU atau ketik nama produk..." autocomplete="off"
                                            aria-expanded="false" aria-haspopup="listbox">
                                    </div>
                                    <div class="dropdown-menu p-3 w-100" id="inlineDropMenu"
                                        style="max-height: 420px; overflow: auto;">
                                        <div id="inlineDropResults" aria-live="polite"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 d-grid">
                                <button id="btnSearch" type="button" class="btn btn-outline-secondary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                        </form>



                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="fw-semibold">Keranjang</div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearCart" disabled>
                                <i class="bi bi-trash"></i> Hapus Semua
                            </button>
                        </div>

                        <div class="table-responsive mt-2">
                            <table class="table align-middle" id="cartTable">
                                <caption>Keranjang</caption>
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-end" style="width:120px;">Harga</th>
                                        <th class="text-center" style="width:140px;">Qty</th>
                                        <th class="text-end" style="width:140px;">Total</th>
                                        <th class="text-end" style="width:80px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody"></tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-12 col-lg-4">
                <aside class="card shadow-sm h-100" role="complementary">
                    <div class="card-body">
                        <h2 class="h6 text-muted">Ringkasan</h2>
                        <dl class="row small mb-0">
                            <dt class="col-6">Subtotal</dt>
                            <dd class="col-6 text-end" id="sumSubtotal">0</dd>

                            <dt class="col-6">Diskon ({{ number_format($discount_percent, 2, ',', '.') }}%)</dt>
                            <dd class="col-6 text-end" id="sumDiscount">0</dd>

                            <dt class="col-6">Pajak ({{ number_format($tax_percent, 2, ',', '.') }}%)</dt>
                            <dd class="col-6 text-end" id="sumTax">0</dd>

                            <dt class="col-6 fw-bold border-top pt-2">Total</dt>
                            <dd class="col-6 text-end fw-bold border-top pt-2" id="sumTotal">0</dd>
                        </dl>

                        <form id="checkoutForm" action="{{ route('kasir.checkout') }}" method="POST" class="mt-3"
                            novalidate>
                            @csrf
                            <input type="hidden" name="payment_method" id="payment_method" value="cash" />
                            <input type="hidden" name="items" id="items_json" />

                            <fieldset class="mb-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary active" data-method="cash"><i
                                            class="bi bi-cash"></i> Tunai</button>
                                    <button type="button" class="btn btn-outline-secondary" data-method="qris" disabled><i
                                            class="bi bi-qr-code"></i> QRIS</button>
                                </div>
                                <small class="text-muted d-block mt-1">QRIS dapat diaktifkan saat integrasi pembayaran
                                    siap.</small>
                            </fieldset>

                            <fieldset class="mb-3" id="cashSection">
                                <label for="paid_amount" class="form-label">Jumlah Bayar ({{ $currency }})</label>
                                <input type="text" inputmode="numeric" id="paid_amount" name="paid_amount"
                                    class="form-control" placeholder="Rp 0">
                                <div class="form-text">Format uang otomatis. Kembalian dihitung saat pembayaran.</div>
                                <div id="changeDisplay" class="mt-2 fw-semibold"></div>
                            </fieldset>

                            <fieldset class="mb-3">
                                <label for="note" class="form-label">Catatan</label>
                                <input type="text" name="note" id="note" class="form-control"
                                    maxlength="255" placeholder="Opsional">
                            </fieldset>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg" id="btnCheckout" disabled>
                                    <i class="bi bi-check2-circle"></i> Proses Pembayaran
                                </button>
                            </div>
                        </form>
                    </div>
                </aside>
            </div>
        </section>
    </section>
@endsection

@push('script')
    <script src="{{ asset('assets/vendor/jquery-3.7.0.min.js') }}"></script>
    <script>
        (function() {
            const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
            const $q = $('#q');
            const $cartBody = $('#cartBody');
            const $sumSubtotal = $('#sumSubtotal');
            const $sumDiscount = $('#sumDiscount');
            const $sumTax = $('#sumTax');
            const $sumTotal = $('#sumTotal');
            const discount = {{ json_encode((float) $discount_percent) }};
            const tax = {{ json_encode((float) $tax_percent) }};
            const $btnCheckout = $('#btnCheckout');
            const $itemsJson = $('#items_json');
            const $paymentMethod = $('#payment_method');
            const $paidAmount = $('#paid_amount');
            const $btnClearCart = $('#btnClearCart');

            const $searchDropdown = $('#searchDropdown');
            const $inlineDropMenu = $('#inlineDropMenu');
            const $inlineDropResults = $('#inlineDropResults');
            const $changeDisplay = $('#changeDisplay');

            let cart = [];
            let lastSubtotal = 0;
            let lastTotal = 0;
            let dropdownReq = null;
            let dropDebounce = null;

            function renderInlineResults(list) {
                if (!Array.isArray(list) || !list.length) {
                    $inlineDropResults.html('<div class="text-muted small">Produk tidak ditemukan.</div>');
                    return;
                }
                const rows = list.map(p => {
                    const disabled = p.stock <= 0 ? 'disabled' : '';
                    const stockInfo = p.stock <= 0 ? '<span class="badge bg-secondary">Habis</span>' :
                        `<span class="badge bg-success">Stok: ${p.stock}</span>`;
                    return `
                    <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                        <div class="me-2" style="min-width:0;">
                            <div class="fw-semibold text-truncate" title="${p.name}">${p.name}</div>
                            <div class="small text-muted">SKU: ${p.sku} • Rp ${fmt(p.price)} ${stockInfo}</div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="number" class="form-control form-control-sm" style="width: 70px" min="1" value="1" id="qty_${p.id}" ${disabled}>
                            <button class="btn btn-sm btn-primary" data-add="${p.id}" ${disabled} title="Tambah ke keranjang"><i class="bi bi-cart-plus"></i></button>
                        </div>
                    </div>
                `;
                }).join('');
                $inlineDropResults.html(rows);
            }

            function upsertCart(product, qty) {
                const idx = cart.findIndex(x => x.product_id === product.id);
                if (idx >= 0) {
                    cart[idx].qty = Math.min((cart[idx].qty + qty), product.stock);
                } else {
                    cart.push({
                        product_id: product.id,
                        name: product.name,
                        price: Number(product.price),
                        qty: Math.min(qty, product.stock),
                        stock: product.stock
                    });
                }
                renderCart();
            }

            function renderCart() {
                if (!cart.length) {
                    $cartBody.html('<tr><td colspan="5" class="text-center text-muted">Keranjang kosong.</td></tr>');
                    calcSummary();
                    $btnClearCart.prop('disabled', true);
                    return;
                }
                const rows = cart.map((it, i) => {
                    const line = Number(it.price) * Number(it.qty);
                    return `
                    <tr>
                        <td>
                            <div class="fw-semibold">${it.name}</div>
                            <div class="small text-muted">ID: ${it.product_id}</div>
                        </td>
                        <td class="text-end">Rp ${fmt(it.price)}</td>
                        <td class="text-center">
                            <div class="input-group input-group-sm justify-content-center" style="max-width: 140px;">
                                <button class="btn btn-outline-secondary" data-dec="${i}" ${it.qty <= 1 ? 'disabled':''}>-</button>
                                <input type="number" class="form-control text-center" min="1" max="${it.stock}" value="${it.qty}" data-qty="${i}">
                                <button class="btn btn-outline-secondary" data-inc="${i}" ${it.qty >= it.stock ? 'disabled':''}>+</button>
                            </div>
                            <div class="small text-muted mt-1">Stok: ${it.stock}</div>
                        </td>
                        <td class="text-end">Rp ${fmt(line)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger" data-del="${i}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;
                }).join('');
                $cartBody.html(rows);
                calcSummary();
                $btnClearCart.prop('disabled', cart.length === 0);
            }

            function calcSummary() {
                const subtotal = cart.reduce((s, it) => s + (Number(it.price) * Number(it.qty)), 0);
                const discountAmount = subtotal * (discount / 100);
                const afterDiscount = subtotal - discountAmount;
                const taxAmount = afterDiscount * (tax / 100);
                const total = afterDiscount + taxAmount;

                $sumSubtotal.text(fmt(subtotal));
                $sumDiscount.text(fmt(discountAmount));
                $sumTax.text(fmt(taxAmount));
                $sumTotal.text(fmt(total));

                $btnCheckout.prop('disabled', cart.length === 0);
                $itemsJson.val(JSON.stringify(cart.map(({
                    product_id,
                    qty
                }) => ({
                    product_id,
                    qty
                }))));

                lastSubtotal = subtotal;
                lastTotal = total;
                updatePaidState();
            }

            function showInlineMenu() {
                if (!$inlineDropMenu.hasClass('show')) {
                    $inlineDropMenu.addClass('show');
                }
            }

            function hideInlineMenu() {
                if ($inlineDropMenu.hasClass('show')) {
                    $inlineDropMenu.removeClass('show');
                }
            }

            function searchInline(q) {
                $inlineDropResults.html('<div class="text-muted small">Memuat…</div>');
                const params = q ? {
                    q,
                    limit: 20
                } : {
                    limit: 20
                };
                // cancel previous request if any
                if (dropdownReq && typeof dropdownReq.abort === 'function') {
                    try {
                        dropdownReq.abort();
                    } catch (e) {}
                }
                dropdownReq = $.get(@json(route('kasir.products')), params)
                    .done(renderInlineResults)
                    .fail((xhr, status) => {
                        if (status === 'abort') return; // ignore manual aborts
                        $inlineDropResults.html('<div class="text-danger small">Gagal memuat data.</div>');
                    })
                    .always(() => {
                        dropdownReq = null;
                    });
            }

            function parseMoneyToInt(str) {
                if (typeof str !== 'string') str = String(str || '');
                // remove non-digits
                const digits = str.replace(/[^0-9]/g, '');
                return Number(digits || 0);
            }

            function formatMoney(val) {
                return 'Rp ' + fmt(val);
            }

            function updatePaidState() {
                const method = $paymentMethod.val();
                const paid = parseMoneyToInt($paidAmount.val());
                const allow = method !== 'cash' || paid >= Math.ceil(lastSubtotal);
                const canPay = allow && cart.length > 0;
                $btnCheckout.prop('disabled', !canPay);
                if (method === 'cash') {
                    const change = Math.max(0, paid - Math.ceil(lastTotal));
                    $changeDisplay.text('Kembalian: ' + formatMoney(change));
                } else {
                    $changeDisplay.text('');
                }
            }

            $('#btnSearch').on('click', function() {
                const q = ($q.val() || '').trim();
                showInlineMenu();
                searchInline(q);
                $q.trigger('focus');
            });
            $q.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const code = ($q.val() || '').trim();
                    if (!code) {
                        showInlineMenu();
                        searchInline('');
                        return;
                    }
                    $.get(@json(route('kasir.products')), {
                        q: code,
                        limit: 5
                    }).done((list) => {
                        let prod = null;
                        if (Array.isArray(list) && list.length) {
                            prod = list.find(p => String(p.sku) === code) || (/^\d+$/.test(code) ? list
                                .find(p => Number(p.id) === Number(code)) : null) || list[0];
                        }
                        if (prod && prod.stock > 0) {
                            upsertCart(prod, 1);
                            $q.val('');
                        } else {
                            showInlineMenu();
                            searchInline(code);
                        }
                    }).fail(() => {
                        showInlineMenu();
                        searchInline(code);
                    });
                }
            });

            // Inline dropdown: add to cart
            $inlineDropResults.on('click', '[data-add]', function() {
                const id = Number($(this).data('add'));
                const qty = Number($('#qty_' + id).val() || 1);
                $.get(@json(route('kasir.products')), {
                    q: id,
                    limit: 1
                }).done((list) => {
                    const p = Array.isArray(list) ? list.find(x => Number(x.id) === id) : null;
                    if (p) upsertCart(p, qty);
                });
            });

            // Show dropdown when focusing the search input
            $q.on('focus', function() {
                showInlineMenu();
                if (!$inlineDropResults.children().length) {
                    searchInline('');
                }
            });
            // ESC to hide
            $q.on('keydown', function(e) {
                if (e.key === 'Escape') hideInlineMenu();
            });
            // Hide when clicking outside
            $(document).on('click', function(e) {
                const el = $searchDropdown[0];
                if (el && !el.contains(e.target)) {
                    hideInlineMenu();
                    if (dropdownReq && typeof dropdownReq.abort === 'function') {
                        try {
                            dropdownReq.abort();
                        } catch (err) {}
                        dropdownReq = null;
                    }
                }
            });
            // Live filtering with debounce on the main input
            $q.on('input', function() {
                const q = ($q.val() || '').trim();
                showInlineMenu();
                if (dropDebounce) clearTimeout(dropDebounce);
                dropDebounce = setTimeout(() => searchInline(q), 250);
            });
            $('#cartTable').on('click', '[data-del]', function() {
                const i = Number($(this).data('del'));
                cart.splice(i, 1);
                renderCart();
            });
            $('#cartTable').on('click', '[data-inc]', function() {
                const i = Number($(this).data('inc'));
                cart[i].qty = Math.min(cart[i].qty + 1, cart[i].stock);
                renderCart();
            });
            $('#cartTable').on('click', '[data-dec]', function() {
                const i = Number($(this).data('dec'));
                cart[i].qty = Math.max(cart[i].qty - 1, 1);
                renderCart();
            });
            $('#cartTable').on('input', '[data-qty]', function() {
                const i = Number($(this).data('qty'));
                let v = Number($(this).val() || 1);
                v = Math.min(Math.max(v, 1), cart[i].stock);
                cart[i].qty = v;
                renderCart();
            });

            $btnClearCart.on('click', function() {
                cart = [];
                renderCart();
            });

            $('#checkoutForm [data-method]').on('click', function() {
                $('#checkoutForm [data-method]').removeClass('active');
                $(this).addClass('active');
                const method = $(this).data('method');
                $('#payment_method').val(method);
                const isCash = method === 'cash';
                $('#cashSection').toggle(isCash);
                updatePaidState();
            });

            $paidAmount.on('input', function() {
                const caretEnd = this.selectionEnd;
                const rawNum = parseMoneyToInt($(this).val());
                const formatted = formatMoney(rawNum);
                $(this).val(formatted);
                try {
                    this.setSelectionRange(formatted.length, formatted.length);
                } catch (e) {}
                updatePaidState();
            });

            $('#checkoutForm').on('submit', function() {
                if (!cart.length) {
                    alert('Keranjang kosong.');
                    return false;
                }
                try {
                    const parsed = JSON.parse($('#items_json').val() || '[]');
                    if (!Array.isArray(parsed) || !parsed.length) {
                        alert('Keranjang kosong.');
                        return false;
                    }
                } catch (e) {
                    alert('Keranjang tidak valid.');
                    return false;
                }
                const paidInt = parseMoneyToInt($paidAmount.val());
                $paidAmount.val(String(paidInt));
                return true;
            });

            renderCart();
        })();
    </script>
@endpush
