<?php $__env->startSection('title', 'Kasir'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container-fluid py-4">
        <header class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-cash-stack"></i> Kasir
                </h1>
                <p class="text-muted mb-0">Scan SKU atau cari produk, tambahkan ke keranjang, lalu proses pembayaran.</p>
            </div>
            <div class="text-muted small">
                • Diskon: <?php echo e(number_format($discount_percent, 2, ',', '.')); ?>% • Pajak:
                <?php echo e(number_format($tax_percent, 2, ',', '.')); ?>% • Mata Uang: <?php echo e($currency); ?>

            </div>
        </header>

        <?php if(session('success')): ?>
            <div class="alert alert-success" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger" role="alert"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        
        <div class="modal fade" id="cashSuccessModal" tabindex="-1" aria-labelledby="cashSuccessLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cashSuccessLabel"><i class="bi bi-receipt-cutoff"></i> Pembayaran
                            Berhasil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1">Transaksi <span
                                class="text-uppercase"><?php echo e(session('printed_payment_method', 'cash')); ?></span> telah berhasil
                            diproses.</p>
                        <?php if(session('printed_transaction_id')): ?>
                            <p class="text-muted small mb-0">No. Transaksi: <span
                                    class="fw-semibold"><?php echo e(session('printed_invoice')); ?></span></p>
                        <?php endif; ?>
                        <p class="text-muted small">Anda dapat mencetak atau bagikan struk ke pelanggan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <?php if(session('printed_transaction_id')): ?>
                            <a id="btnPrintReceipt" class="btn btn-primary" target="_blank"
                                href="<?php echo e(route('transaksi.struk', ['transaction' => session('printed_transaction_id'), 'print' => 1])); ?>"><i
                                    class="bi bi-printer"></i> Cetak Struk</a>
                            <a class="btn btn-outline-primary"
                                href="<?php echo e(route('transaksi.struk.pdf', session('printed_transaction_id'))); ?>">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                            <?php
                                $__trx = \App\Models\Transaction::find(session('printed_transaction_id'));
                                $__waText = "*Struk Pembayaran*\nNo: " . session('printed_invoice', '-') . "\nTotal: Rp " . number_format($__trx->total ?? 0, 0, ',', '.') . "\n\nDownload PDF:\n" . route('transaksi.struk.pdf', session('printed_transaction_id'));
                            ?>
                            <a class="btn btn-success" href="<?php echo e('https://wa.me/?text=' . rawurlencode($__waText)); ?>"
                                target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <section class="row g-3">
            <div class="col-12 col-lg-8">
                <section class="card shadow-sm h-100">
                    <div class="card-body">
                        <form id="productSearchForm" class="row g-2" role="search" aria-label="Pencarian produk"
                            onsubmit="return false;">
                            <div class="col-12 col-md-8">
                                <label for="q" class="visually-hidden">Cari produk</label>
                                <div class="w-100 position-relative" id="searchDropdown">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                        <input id="q" type="search" class="form-control"
                                            placeholder="Scan SKU atau ketik nama produk..." autocomplete="off"
                                            aria-expanded="false" aria-haspopup="listbox">
                                    </div>
                                    <div class="p-3 w-100 position-absolute bg-white border rounded shadow"
                                        id="inlineDropMenu"
                                        style="max-height: 420px; overflow: auto; z-index: 1000; top: 100%; left: 0; margin-top: .25rem; display: none;">
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



                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3">
                            <div class="fw-semibold">Keranjang</div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnShowHolds">
                                    <i class="bi bi-inboxes"></i> Tertunda
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" id="btnHold" disabled>
                                    <i class="bi bi-pause-circle"></i> Tunda Transaksi
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearCart" disabled>
                                    <i class="bi bi-trash"></i> Hapus Semua
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive mt-2">
                            <table class="table align-middle" id="cartTable">
                                <caption>Keranjang</caption>
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-center" style="width:100px;">Satuan</th>
                                        <th class="text-end" style="width:120px;">Harga</th>
                                        <th class="text-center" style="width:140px;">Qty</th>
                                        <th class="text-end" style="width:140px;">Total</th>
                                        <th class="text-end" style="width:80px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody"><tr><td colspan="6" class="text-center text-muted">Keranjang kosong.</td></tr></tbody>
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

                            <dt class="col-6">Diskon (<?php echo e(number_format($discount_percent, 2, ',', '.')); ?>%)</dt>
                            <dd class="col-6 text-end" id="sumDiscount">0</dd>

                            <dt class="col-6">Pajak (<?php echo e(number_format($tax_percent, 2, ',', '.')); ?>%)</dt>
                            <dd class="col-6 text-end" id="sumTax">0</dd>

                            <dt class="col-6 fw-bold border-top pt-2">Total</dt>
                            <dd class="col-6 text-end fw-bold border-top pt-2" id="sumTotal">0</dd>
                        </dl>

                        <form id="checkoutForm" action="<?php echo e(route('kasir.checkout')); ?>" method="POST" class="mt-3"
                            novalidate>
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="payment_method" id="payment_method" value="cash" />
                            <input type="hidden" name="items" id="items_json" />

                            <fieldset class="mb-3">
                                <legend class="col-form-label pt-0 fw-semibold fs-6">Metode Pembayaran</legend>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary active" data-method="cash"><i
                                            class="bi bi-cash"></i> Tunai</button>
                                    <button type="button" class="btn btn-outline-warning" data-method="cash_tempo"><i
                                            class="bi bi-clock-history"></i> Tempo</button>
                                </div>
                            </fieldset>

                            <fieldset class="mb-3" id="cashSection">
                                <label for="paid_amount" class="form-label">Jumlah Bayar (<?php echo e($currency); ?>)</label>
                                <input type="text" inputmode="numeric" id="paid_amount" name="paid_amount"
                                    class="form-control" placeholder="Rp 0">
                                <div id="changeDisplay" class="mt-2 fw-semibold"></div>
                            </fieldset>

                            <fieldset class="mb-3">
                                <label for="customer_id" class="form-label">Pelanggan</label>
                                <select name="customer_id" id="customer_id" class="form-select">
                                    <option value="">-- Umum / Tanpa Pelanggan --</option>
                                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cust): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cust->id); ?>"><?php echo e($cust->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </fieldset>

                            <fieldset class="mb-3">
                                <label for="note" class="form-label">Catatan</label>
                                <input type="text" name="note" id="note" class="form-control" maxlength="255"
                                    placeholder="Catatan tambahan (opsional)">
                            </fieldset>

                            <fieldset class="mb-3">
                                <label for="transaction_date" class="form-label">Ubah Tanggal Transaksi</label>
                                <input type="datetime-local" name="transaction_date" id="transaction_date"
                                    class="form-control" title="Kosongkan jika ingin memakai tanggal & jam saat ini.">
                                <div class="form-text small text-muted">Abaikan jika transaksi baru (hari ini).</div>
                            </fieldset>
                            <input type="hidden" name="suspended_from_id" id="suspended_from_id" />

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
    <!-- Modal Holds -->
    <div class="modal fade" id="holdsModal" tabindex="-1" aria-labelledby="holdsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="holdsLabel"><i class="bi bi-inboxes"></i> Transaksi Tertunda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="holdsList" class="list-group small"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('assets/vendor/jquery-3.7.0.min.js')); ?>"></script>

    <script>
        (function () {
            const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
            const $q = $('#q');
            const $cartBody = $('#cartBody');
            const $sumSubtotal = $('#sumSubtotal');
            const $sumDiscount = $('#sumDiscount');
            const $sumTax = $('#sumTax');
            const $sumTotal = $('#sumTotal');
            const discount = <?php echo e(json_encode((float) $discount_percent)); ?>;
            const tax = <?php echo e(json_encode((float) $tax_percent)); ?>;
            const $btnCheckout = $('#btnCheckout');
            const $itemsJson = $('#items_json');
            const $paymentMethod = $('#payment_method');
            const $paidAmount = $('#paid_amount');
            const $btnClearCart = $('#btnClearCart');
            const $btnHold = $('#btnHold');
            const $btnShowHolds = $('#btnShowHolds');

            const $searchDropdown = $('#searchDropdown');
            const $inlineDropMenu = $('#inlineDropMenu');
            const $inlineDropResults = $('#inlineDropResults');
            const $changeDisplay = $('#changeDisplay');

            let cart = [];
            let lastSubtotal = 0;
            let lastTotal = 0;
            let dropdownReq = null;
            let dropDebounce = null;

            // Cache produk dari search results
            let productCache = {};

            function renderInlineResults(list) {
                if (!Array.isArray(list) || !list.length) {
                    $inlineDropResults.html('<div class="text-muted small">Produk tidak ditemukan.</div>');
                    return;
                }
                // Cache products
                list.forEach(p => { productCache[p.id] = p; });

                const rows = list.map(p => {
                    const disabled = p.stock <= 0 ? 'disabled' : '';
                    const stockInfo = p.stock <= 0 ? '<span class="badge bg-secondary">Habis</span>' :
                        `<span class="badge bg-success">Stok: ${p.stock} ${p.unit}</span>`;
                    const typeIcon = p.is_weight ? '⚖️' : '📦';

                    // Tombol eceran & grosir
                    let unitBtns = '';
                    if (p.has_retail) {
                        unitBtns += `<button class="btn btn-sm btn-primary" data-add="${p.id}" data-bulk="0" ${disabled} title="Tambah eceran"><i class="bi bi-cart-plus"></i> ${p.unit}</button>`;
                        if (p.has_bulk && p.bulk_unit) {
                            unitBtns += ` <button class="btn btn-sm btn-outline-warning" data-add="${p.id}" data-bulk="1" ${disabled} title="Tambah grosir"><i class="bi bi-box-seam"></i> ${p.bulk_unit}</button>`;
                        }
                    } else {
                        // Hanya grosir (count)
                        unitBtns += `<button class="btn btn-sm btn-warning text-dark fw-semibold" data-add="${p.id}" data-bulk="0" ${disabled} title="Tambah"><i class="bi bi-box-seam"></i> ${p.unit}</button>`;
                    }

                    // Step: kg → 0.01, pcs/krat → 1
                    const isDecimal = p.has_retail && p.is_weight;
                    const step = isDecimal ? '0.01' : '1';
                    const minVal = isDecimal ? '0.01' : '1';
                    const defVal = isDecimal ? '0.5' : '1';

                    return `
                        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                            <div class="me-2" style="min-width:0;">
                                <div class="fw-semibold text-truncate" title="${p.name}">${typeIcon} ${p.name}</div>
                                <div class="small text-muted">SKU: ${p.sku} • Rp ${fmt(p.price)}/${p.unit} ${p.price_per_bulk ? '• Rp ' + fmt(p.price_per_bulk) + '/' + p.bulk_unit : ''} ${stockInfo}</div>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="number" class="form-control form-control-sm" style="width: 80px" min="${minVal}" step="${step}" value="${defVal}" id="qty_${p.id}" ${disabled}>
                                ${unitBtns}
                            </div>
                        </div>
                    `;
                }).join('');
                $inlineDropResults.html(rows);
            }

            function upsertCart(product, qty, isBulk) {
                const saleUnit = isBulk ? (product.bulk_unit || product.unit) : product.unit;
                const unitPrice = isBulk ? (product.price_per_bulk || product.price) : product.price;

                // Cari apakah item dengan produk + unit yang sama sudah ada di cart
                const idx = cart.findIndex(x => x.product_id === product.id && x.sale_unit === saleUnit);
                if (idx >= 0) {
                    cart[idx].qty += qty;
                } else {
                    cart.push({
                        product_id: product.id,
                        name: product.name,
                        price: Number(unitPrice),
                        qty: qty,
                        stock: product.stock,
                        sale_unit: saleUnit,
                        is_bulk: isBulk,
                        is_weight: product.is_weight,
                        has_retail: product.has_retail,
                        unit: product.unit,
                        bulk_unit: product.bulk_unit,
                        bulk_conversion: product.bulk_conversion || 1,
                    });
                }
                renderCart();
            }

            function renderCart() {
                if (!cart.length) {
                    $cartBody.html('<tr><td colspan="6" class="text-center text-muted">Keranjang kosong.</td></tr>');
                    calcSummary();
                    $btnClearCart.prop('disabled', true);
                    $btnHold.prop('disabled', true);
                    return;
                }
                const rows = cart.map((it, i) => {
                    const line = Number(it.price) * Number(it.qty);

                    // Krat/grosir → integer step, Eceran kg → desimal, Eceran pcs → integer
                    const isDecimalQty = it.has_retail && it.is_weight && !it.is_bulk;
                    const step    = isDecimalQty ? '0.01' : '1';
                    const minVal  = isDecimalQty ? '0.01' : '1';
                    const decStep = isDecimalQty ? 0.1 : 1;

                    const unitBadge = it.is_bulk
                        ? `<span class="badge bg-warning text-dark">${it.sale_unit}</span>`
                        : `<span class="badge bg-info">${it.sale_unit}</span>`;
                    const qtyDisplay = isDecimalQty ? Number(it.qty).toFixed(2) : Math.round(it.qty);

                    // Stok info: tampilkan stok base + konversi ke krat jika grosir
                    let stockHint = `Stok: ${Number(it.stock).toFixed(1)} ${it.unit}`;
                    if (it.is_bulk && it.bulk_conversion > 0) {
                        const maxBulk = Math.floor(Number(it.stock) / Number(it.bulk_conversion));
                        stockHint += ` (~${maxBulk} ${it.sale_unit})`;
                    }

                    return `
                        <tr>
                            <td>
                                <div class="fw-semibold">${it.name}</div>
                                <div class="small text-muted">ID: ${it.product_id}</div>
                            </td>
                            <td class="text-center">${unitBadge}</td>
                            <td class="text-end">Rp ${fmt(it.price)}/${it.sale_unit}</td>
                            <td class="text-center">
                                <div class="input-group input-group-sm justify-content-center" style="max-width: 150px;">
                                    <button class="btn btn-outline-secondary" data-dec="${i}" data-step="${decStep}" ${it.qty <= Number(minVal) ? 'disabled' : ''}>-</button>
                                    <input type="number" class="form-control text-center" min="${minVal}" step="${step}" value="${qtyDisplay}" data-qty="${i}">
                                    <button class="btn btn-outline-secondary" data-inc="${i}" data-step="${decStep}">+</button>
                                </div>
                                <div class="small text-muted mt-1">${stockHint}</div>
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
                $btnHold.prop('disabled', cart.length === 0);
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
                $itemsJson.val(JSON.stringify(cart.map(it => ({
                    product_id: it.product_id,
                    qty: it.qty,
                    sale_unit: it.sale_unit || null,
                    is_bulk: it.is_bulk || false,
                }))));

                lastSubtotal = subtotal;
                lastTotal = total;
                updatePaidState();
            }

            function showInlineMenu() {
                if ($inlineDropMenu.css('display') === 'none') {
                    $inlineDropMenu.css('display', 'block');
                    $q.attr('aria-expanded', 'true');
                }
            }

            function hideInlineMenu() {
                if ($inlineDropMenu.css('display') !== 'none') {
                    $inlineDropMenu.css('display', 'none');
                    $q.attr('aria-expanded', 'false');
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

                if (dropdownReq && typeof dropdownReq.abort === 'function') {
                    try {
                        dropdownReq.abort();
                    } catch (e) { }
                }

                dropdownReq = $.get(<?php echo json_encode(route('kasir.products'), 15, 512) ?>, params)
                    .done(renderInlineResults)
                    .fail((xhr, status) => {
                        if (status === 'abort') return;
                        $inlineDropResults.html('<div class="text-danger small">Gagal memuat data.</div>');
                    })
                    .always(() => {
                        dropdownReq = null;
                    });
            }

            function parseMoneyToInt(str) {
                if (typeof str !== 'string') str = String(str || '');

                const digits = str.replace(/[^0-9]/g, '');
                return Number(digits || 0);
            }

            function formatMoney(val) {
                return 'Rp ' + fmt(val);
            }

            function updatePaidState() {
                const method = $paymentMethod.val();
                const paid = parseMoneyToInt($paidAmount.val());
                const allow = method !== 'cash' || paid >= Math.ceil(lastTotal);
                const canPay = allow && cart.length > 0;
                $btnCheckout.prop('disabled', !canPay);
                if (method === 'cash') {
                    const change = Math.max(0, paid - Math.ceil(lastTotal));
                    $changeDisplay.text('Kembalian: ' + formatMoney(change));
                } else {
                    $changeDisplay.text('');
                }
            }



            $('#btnSearch').on('click', function () {
                const q = ($q.val() || '').trim();
                showInlineMenu();
                searchInline(q);
                $q.trigger('focus');
            });

            $q.on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const code = ($q.val() || '').trim();
                    if (!code) {
                        showInlineMenu();
                        searchInline('');
                        return;
                    }
                    $.get(<?php echo json_encode(route('kasir.products'), 15, 512) ?>, {
                        q: code,
                        limit: 5
                    }).done((list) => {
                        let prod = null;
                        if (Array.isArray(list) && list.length) {
                            prod = list.find(p => String(p.sku) === code) || (/^\d+$/.test(code) ? list
                                .find(p => Number(p.id) === Number(code)) : null) || list[0];
                        }
                        if (prod && prod.stock > 0) {
                            upsertCart(prod, 1, false);
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

            $inlineDropResults.on('click', '[data-add]', function () {
                const id = Number($(this).data('add'));
                const isBulk = $(this).data('bulk') == 1;
                const qty = Number($('#qty_' + id).val() || 1);
                // Cari dari cache dulu
                const cached = productCache[id];
                if (cached) {
                    upsertCart(cached, qty, isBulk);
                } else {
                    $.get(<?php echo json_encode(route('kasir.products'), 15, 512) ?>, {
                        q: id,
                        limit: 1
                    }).done((list) => {
                        const p = Array.isArray(list) ? list.find(x => Number(x.id) === id) : null;
                        if (p) upsertCart(p, qty, isBulk);
                    });
                }
            });

            $q.on('focus', function () {
                showInlineMenu();
                if (!$inlineDropResults.children().length) {
                    searchInline('');
                }
            });

            // ESC to hide
            $q.on('keydown', function (e) {
                if (e.key === 'Escape') {
                    e.stopPropagation();
                    hideInlineMenu();
                }
            });

            // ESC to hide
            $inlineDropMenu.on('keydown', function (e) {
                if (e.key === 'Escape') {
                    e.stopPropagation();
                    hideInlineMenu();
                    $q.trigger('focus');
                }
            });

            // Hide when clicking outside
            $(document).on('click', function (e) {
                const el = $searchDropdown[0];
                if (el && !el.contains(e.target)) {
                    hideInlineMenu();
                    if (dropdownReq && typeof dropdownReq.abort === 'function') {
                        try {
                            dropdownReq.abort();
                        } catch (err) { }
                        dropdownReq = null;
                    }
                }
            });

            // Live filtering with debounce on the main input
            $q.on('input', function () {
                const q = ($q.val() || '').trim();
                showInlineMenu();
                if (dropDebounce) clearTimeout(dropDebounce);
                dropDebounce = setTimeout(() => searchInline(q), 250);
            });

            $('#cartTable').on('click', '[data-del]', function () {
                const i = Number($(this).data('del'));
                cart.splice(i, 1);
                renderCart();
            });

            $('#cartTable').on('click', '[data-inc]', function () {
                const i = Number($(this).data('inc'));
                const step = Number($(this).data('step') || 1);
                cart[i].qty = Math.round((cart[i].qty + step) * 100) / 100;
                // Jika grosir, bulatkan ke integer
                if (cart[i].is_bulk) cart[i].qty = Math.round(cart[i].qty);
                renderCart();
            });

            $('#cartTable').on('click', '[data-dec]', function () {
                const i = Number($(this).data('dec'));
                const step = Number($(this).data('step') || 1);
                const isDecimalQty = cart[i].has_retail && cart[i].is_weight && !cart[i].is_bulk;
                const minVal = isDecimalQty ? 0.01 : 1;
                cart[i].qty = Math.max(Math.round((cart[i].qty - step) * 100) / 100, minVal);
                if (cart[i].is_bulk || !cart[i].has_retail) cart[i].qty = Math.round(cart[i].qty);
                renderCart();
            });

            $('#cartTable').on('input', '[data-qty]', function () {
                const i = Number($(this).data('qty'));
                let v = Number($(this).val() || 0.01);
                const isDecimalQty = cart[i].has_retail && cart[i].is_weight && !cart[i].is_bulk;
                const minVal = isDecimalQty ? 0.01 : 1;
                v = Math.max(v, minVal);
                if (cart[i].is_bulk || !cart[i].has_retail) v = Math.round(v); // harus integer
                cart[i].qty = Math.round(v * 100) / 100;
                renderCart();
            });

            $btnClearCart.on('click', function () {
                cart = [];
                renderCart();
                $('#suspended_from_id').val('');
                // If modal open, refresh list to remove badge
                if ($('#holdsModal').hasClass('show')) loadHolds();
            });

            // Create Hold
            $btnHold.on('click', function () {
                if (!cart.length) return;
                const payload = {
                    _token: <?php echo json_encode(csrf_token(), 15, 512) ?>,
                    items: JSON.parse($itemsJson.val() || '[]'),
                    note: ($('#note').val() || '').trim(),
                    customer_id: ($('#customer_id').val() || '').trim() || null,
                    suspended_from_id: ($('#suspended_from_id').val() || '').trim()
                };
                $.ajax({
                    url: <?php echo json_encode(route('kasir.hold'), 15, 512) ?>,
                    method: 'POST',
                    data: payload,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done((res) => {
                    alert('Transaksi ditunda: ' + (res?.invoice || ''));
                    cart = [];
                    renderCart();
                    $('#suspended_from_id').val('');
                    $('#note').val('');
                    $('#customer_id').val('');
                    if ($('#holdsModal').hasClass('show')) loadHolds();
                }).fail((xhr) => {
                    alert(xhr?.responseJSON?.message || 'Gagal menunda transaksi');
                });
            });

            function loadHolds() {
                $('#holdsList').html('<div class="text-muted">Memuat…</div>');
                $.get(<?php echo json_encode(route('kasir.holds'), 15, 512) ?>).done((list) => {
                    if (!Array.isArray(list) || !list.length) {
                        $('#holdsList').html('<div class="text-muted">Tidak ada transaksi tertunda.</div>');
                        return;
                    }
                    const currentIdNum = Number(($('#suspended_from_id').val() || '').toString().trim());
                    const rows = list.map(h => {
                        const isCurrent = Number.isFinite(currentIdNum) && currentIdNum > 0 && Number(h
                            .id) === currentIdNum;
                        const badge = isCurrent ?
                            '<span class="badge bg-info ms-2">Sedang dimuat</span>' : '';
                        return `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">${h.invoice_number} ${badge}</div>
                                    <div class="text-muted">Pelanggan/Catatan: <span class="fw-semibold">${(h.note || '-')}</span></div>
                                    <div class="small text-muted">${new Date(h.created_at).toLocaleString('id-ID')} • Total Rp ${fmt(h.total)}</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary" data-resume="${h.id}"><i class="bi bi-download"></i> Muat</button>
                                    <button class="btn btn-sm btn-outline-danger" data-delete="${h.id}"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>`;
                    }).join('');
                    $('#holdsList').html(rows);
                }).fail(() => {
                    $('#holdsList').html('<div class="text-danger">Gagal memuat data.</div>');
                });
            }

            $btnShowHolds.on('click', function () {
                const el = document.getElementById('holdsModal');
                if (!el) return;
                const m = new bootstrap.Modal(el);
                m.show();
                loadHolds();
                $('#holdsList').off('click').on('click', '[data-resume]', function () {
                    const id = $(this).data('resume');
                    $.post(<?php echo json_encode(route('kasir.holds.resume', ['transaction' => '__ID__']), 512) ?>.replace('__ID__', id), {
                        _token: <?php echo json_encode(csrf_token(), 15, 512) ?>
                    }).done((res) => {
                        if (Array.isArray(res?.items)) {
                            const items = res.items;
                            $('#suspended_from_id').val(id);
                            $('#note').val(res.note || '');
                            $('#customer_id').val(res.customer_id || '');
                            const ids = items.map(i => i.product_id);
                            $.get(<?php echo json_encode(route('kasir.products'), 15, 512) ?>, {
                                q: '',
                                limit: 100
                            }).done((all) => {
                                cart = items.map(it => {
                                    const p = Array.isArray(all) ? all.find(x =>
                                        Number(x.id) === Number(it
                                            .product_id)) : null;
                                    const stock = p ? Number(p.stock) : it
                                        .qty; // fallback
                                    return {
                                        product_id: it.product_id,
                                        name: p ? p.name : ('Produk #' + it
                                            .product_id),
                                        price: Number(it.price || 0),
                                        qty: Math.min(Number(it.qty), stock),
                                        stock: stock
                                    };
                                });
                                renderCart();
                                loadHolds(); // refresh badge state
                                m.hide();
                            });
                        }
                    }).fail(() => alert('Gagal memuat transaksi.'));
                }).on('click', '[data-delete]', function () {
                    const id = $(this).data('delete');
                    if (!confirm('Hapus transaksi tertunda ini?')) return;
                    $.ajax({
                        url: <?php echo json_encode(route('kasir.holds.destroy', ['transaction' => '__ID__']), 512) ?>.replace('__ID__', id),
                        method: 'DELETE',
                        data: {
                            _token: <?php echo json_encode(csrf_token(), 15, 512) ?>
                        }
                    })
                        .done(() => loadHolds())
                        .fail(() => alert('Gagal menghapus.'));
                });
            });

            $('#checkoutForm [data-method]').on('click', function () {
                $('#checkoutForm [data-method]').removeClass('active');
                $(this).addClass('active');
                const method = $(this).data('method');
                $('#payment_method').val(method);
                const isCash = method === 'cash';
                const isTempo = method === 'cash_tempo';
                $('#cashSection').toggle(isCash || isTempo);
                // update label for optional deposit when tempo
                const $paidLabel = $('label[for="paid_amount"]');
                if (isTempo) {
                    $paidLabel.text('Jumlah Bayar (opsional) (<?php echo e($currency); ?>)');
                } else {
                    $paidLabel.text('Jumlah Bayar (<?php echo e($currency); ?>)');
                }
                updatePaidState();
            });

            $paidAmount.on('input', function () {
                const caretEnd = this.selectionEnd;
                const rawNum = parseMoneyToInt($(this).val());
                const formatted = formatMoney(rawNum);
                $(this).val(formatted);
                try {
                    this.setSelectionRange(formatted.length, formatted.length);
                } catch (e) { }
                updatePaidState();
            });

            $('#checkoutForm').on('submit', function (e) {
                e.preventDefault();
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
                const method = $('#payment_method').val();
                const paidInt = parseMoneyToInt($paidAmount.val());
                $paidAmount.val(String(paidInt));

                // Cash or tempo: normal form submit
                HTMLFormElement.prototype.submit.call(this);
                return false;
            });

            renderCart();

            <?php if(session('printed_transaction_id')): ?>
                try {
                    const el = document.getElementById('cashSuccessModal');
                    if (el) {
                        const m = new bootstrap.Modal(el);
                        m.show();
                        const btn = document.getElementById('btnPrintReceipt');
                        if (btn) {
                            btn.addEventListener('click', function () {
                                cart = [];
                                renderCart();
                                $('#suspended_from_id').val('');
                            });
                        }
                    }
                } catch (e) { }
            <?php endif; ?>
            })();
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/cashier/index.blade.php ENDPATH**/ ?>