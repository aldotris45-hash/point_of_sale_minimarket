<?php $__env->startSection('title', 'Catat Barang Masuk'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container py-4">
        <div class="mb-3">
            <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-box-arrow-in-down"></i> Catat Barang Masuk
            </h1>
            <p class="text-muted mb-0">Isi data barang masuk. Stok produk akan otomatis bertambah & harga diperbarui.</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="<?php echo e(route('barang-masuk.store')); ?>" method="POST" novalidate id="incomingGoodForm">
                    <?php echo csrf_field(); ?>

                    
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('date', date('Y-m-d'))); ?>" required>
                            <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select name="supplier_id" id="supplier_id" class="form-select <?php $__errorArgs = ['supplier_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">- Tanpa Supplier -</option>
                                <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($id); ?>" <?php echo e(old('supplier_id') == $id ? 'selected' : ''); ?>><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['supplier_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="productSearch" class="form-label">Produk <span class="text-danger">*</span></label>
                            <input type="hidden" name="product_id" id="product_id" value="<?php echo e(old('product_id')); ?>">
                            <div class="position-relative">
                                <input type="text" id="productSearch" class="form-control <?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    placeholder="Ketik nama produk..." autocomplete="off">
                                <div id="productDropdown" class="dropdown-menu w-100 shadow" style="max-height:250px; overflow-y:auto;"></div>
                            </div>
                            <div id="productSearchError" class="invalid-feedback d-none">Pilih produk dari daftar terlebih dahulu.</div>
                            <?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-3">
                            <label for="purchase_price" class="form-label">Harga Beli (Modal) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="purchase_price" id="purchase_price"
                                    class="form-control <?php $__errorArgs = ['purchase_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('purchase_price', 0)); ?>" min="0" required>
                            </div>
                            <?php $__errorArgs = ['purchase_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="selling_price_bulk" class="form-label">Harga Jual Grosir <small class="text-muted">(opsional)</small></label>
                            <div class="input-group">
                                <span class="input-group-text bg-warning text-dark">Rp</span>
                                <input type="number" name="selling_price_bulk" id="selling_price_bulk"
                                    class="form-control <?php $__errorArgs = ['selling_price_bulk'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('selling_price_bulk')); ?>" min="0" placeholder="Harga per krat">
                            </div>
                            <?php $__errorArgs = ['selling_price_bulk'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="selling_price_retail" class="form-label">Harga Jual Ecer <small class="text-muted">(opsional)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="selling_price_retail" id="selling_price_retail"
                                    class="form-control <?php $__errorArgs = ['selling_price_retail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('selling_price_retail')); ?>" min="0" placeholder="Harga per kg/pcs">
                            </div>
                            <?php $__errorArgs = ['selling_price_retail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="quantity" class="form-label">Jumlah <span id="qtyUnitLabel"></span> <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="quantity"
                                class="form-control <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('quantity', 1)); ?>" min="0.01" step="0.01" required>
                            <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Total</label>
                            <input type="text" id="totalDisplay" class="form-control" readonly
                                style="background-color: #f8f9fa; font-weight: 600;" value="Rp 0">
                        </div>
                    </div>

                    
                    <div id="marginInfo" class="alert alert-info py-2 d-none mb-3">
                        <span id="marginText"></span>
                    </div>

                    
                    <div id="multiUnitSection" class="d-none">
                        <hr>
                        <h6 class="text-muted mb-3"><i class="bi bi-boxes"></i> Detail Multi-Unit</h6>

                        
                        <div id="weightSection" class="row g-3 mb-3 d-none">
                            <div class="col-12 col-md-4">
                                <label for="gross_weight_kg" class="form-label">Berat Kotor per Krat (kg) <span class="text-danger">*</span></label>
                                <input type="number" name="gross_weight_kg" id="gross_weight_kg"
                                    class="form-control <?php $__errorArgs = ['gross_weight_kg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('gross_weight_kg')); ?>" step="0.001" min="0.001"
                                    placeholder="Contoh: 6.5">
                                <?php $__errorArgs = ['gross_weight_kg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="form-text">Berat 1 krat beserta isinya (timbangan).</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="krat_weight_kg" class="form-label">Berat Krat (kg/krat) <span class="text-danger">*</span></label>
                                <input type="number" name="krat_weight_kg" id="krat_weight_kg"
                                    class="form-control <?php $__errorArgs = ['krat_weight_kg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('krat_weight_kg')); ?>" step="0.001" min="0.001"
                                    placeholder="Contoh: 0.5">
                                <?php $__errorArgs = ['krat_weight_kg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Berat Bersih (kg)</label>
                                <input type="text" id="netWeightDisplay" class="form-control" readonly
                                    style="background-color: #f8f9fa; font-weight: 600;" value="— kg">
                            </div>
                        </div>

                        
                        <div id="countSection" class="row g-3 mb-3 d-none">
                            <div class="col-12 col-md-4">
                                <label for="conversion_factor" class="form-label">Isi per <span id="conversionBulkLabel">krat</span> <span class="text-danger">*</span></label>
                                <input type="number" name="conversion_factor" id="conversion_factor"
                                    class="form-control <?php $__errorArgs = ['conversion_factor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('conversion_factor')); ?>" step="0.001" min="0.001"
                                    placeholder="Contoh: 24">
                                <?php $__errorArgs = ['conversion_factor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Total Stok Masuk (<span id="baseUnitLabel">pcs</span>)</label>
                                <input type="text" id="totalStockDisplay" class="form-control" readonly
                                    style="background-color: #f8f9fa; font-weight: 600;" value="— pcs">
                            </div>
                        </div>

                        
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="spoilage_qty" class="form-label">Busuk / Rusak <small class="text-muted">(opsional)</small></label>
                                <input type="number" name="spoilage_qty" id="spoilage_qty"
                                    class="form-control <?php $__errorArgs = ['spoilage_qty'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('spoilage_qty')); ?>" step="0.01" min="0"
                                    placeholder="Jumlah yang rusak">
                                <?php $__errorArgs = ['spoilage_qty'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-12 col-md-8">
                                <label for="spoilage_notes" class="form-label">Catatan Busuk <small class="text-muted">(opsional)</small></label>
                                <input type="text" name="spoilage_notes" id="spoilage_notes"
                                    class="form-control <?php $__errorArgs = ['spoilage_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('spoilage_notes')); ?>" placeholder="Contoh: 2 botol pecah, 1 kg busuk">
                                <?php $__errorArgs = ['spoilage_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Keterangan</label>
                        <textarea name="notes" id="notes" class="form-control <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            rows="2" maxlength="500" placeholder="Keterangan tambahan (opsional)"><?php echo e(old('notes')); ?></textarea>
                        <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Barang Masuk
                        </button>
                        <a href="<?php echo e(route('barang-masuk.index')); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <?php
        $productsJson = $products->map(function ($p) {
            return [
                'id'              => $p->id,
                'name'            => $p->name,
                'category'        => $p->category?->name ?? '-',
                'price'           => (float) $p->price,
                'price_per_bulk'  => (float) ($p->price_per_bulk ?? 0),
                'price_per_unit'  => (float) ($p->price_per_unit ?? $p->price),
                'stock'           => $p->stock,
                'product_type'    => $p->product_type ?? 'weight',
                'has_retail'      => (bool) ($p->has_retail ?? true),
                'unit'            => $p->unit ?? 'kg',
                'bulk_unit'       => $p->bulk_unit ?? '',
                'bulk_conversion' => (float) ($p->bulk_conversion ?? 0),
            ];
        })->values();
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === Data produk ===
            const products = <?php echo json_encode($productsJson, 15, 512) ?>;

            const searchInput     = document.getElementById('productSearch');
            const hiddenInput     = document.getElementById('product_id');
            const dropdown        = document.getElementById('productDropdown');
            const priceInput      = document.getElementById('purchase_price');
            const sellingBulkInput = document.getElementById('selling_price_bulk');
            const sellingRetailInput = document.getElementById('selling_price_retail');
            const qtyInput        = document.getElementById('quantity');
            const totalDisplay    = document.getElementById('totalDisplay');
            const marginInfo      = document.getElementById('marginInfo');
            const marginText      = document.getElementById('marginText');
            const qtyUnitLabel    = document.getElementById('qtyUnitLabel');

            // Multi-unit elements
            const multiUnitSection = document.getElementById('multiUnitSection');
            const weightSection    = document.getElementById('weightSection');
            const countSection     = document.getElementById('countSection');
            const grossWeightInput = document.getElementById('gross_weight_kg');
            const kratWeightInput  = document.getElementById('krat_weight_kg');
            const netWeightDisplay = document.getElementById('netWeightDisplay');
            const convFactorInput  = document.getElementById('conversion_factor');
            const totalStockDisplay = document.getElementById('totalStockDisplay');
            const baseUnitLabel    = document.getElementById('baseUnitLabel');
            const convBulkLabel    = document.getElementById('conversionBulkLabel');
            const spoilageInput    = document.getElementById('spoilage_qty');

            let selectedProduct = null;
            const old_qty = qtyInput.value; // preserve old value for re-selection

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
                    const typeLabel = p.product_type === 'weight' ? '⚖️ Timbang' : '📦 Hitungan';
                    item.innerHTML = `<strong>${p.name}</strong> <small class="text-muted">(${p.category}) — Stok: ${p.stock} ${p.unit} — ${typeLabel}</small>`;
                    item.addEventListener('click', function() {
                        selectProduct(p);
                    });
                    dropdown.appendChild(item);
                });
                dropdown.classList.add('show');
            }

            // === Validasi submit ===
            document.getElementById('incomingGoodForm').addEventListener('submit', function(e) {
                const productId = hiddenInput.value;
                const errorEl = document.getElementById('productSearchError');
                if (!productId) {
                    e.preventDefault();
                    searchInput.classList.add('is-invalid');
                    errorEl.classList.remove('d-none');
                    searchInput.focus();
                    searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    searchInput.classList.remove('is-invalid');
                    errorEl.classList.add('d-none');
                }
            });

            // === Pilih produk ===
            function selectProduct(p) {
                selectedProduct = p;
                hiddenInput.value = p.id;
                searchInput.value = p.name;
                sellingBulkInput.value = p.price_per_bulk > 0 ? p.price_per_bulk : '';
                sellingRetailInput.value = (p.has_retail && p.price_per_unit > 0) ? p.price_per_unit : '';
                dropdown.classList.remove('show');
                searchInput.classList.remove('is-invalid');
                document.getElementById('productSearchError').classList.add('d-none');

                // Show multi-unit section
                multiUnitSection.classList.remove('d-none');

                if (p.product_type === 'weight' && p.has_retail) {
                    // Weight-based (retail): show gross weight & krat weight
                    weightSection.classList.remove('d-none');
                    countSection.classList.add('d-none');
                    qtyUnitLabel.textContent = '(krat)';
                    // Jumlah krat = bilangan bulat
                    qtyInput.step = '1';
                    qtyInput.min = '1';
                    qtyInput.placeholder = 'Contoh: 5';
                    qtyInput.value = old_qty || 1;
                } else {
                    // Count-based: show conversion factor
                    countSection.classList.remove('d-none');
                    weightSection.classList.add('d-none');
                    const bulkUnit = p.bulk_unit || 'krat';
                    qtyUnitLabel.textContent = '(' + bulkUnit + ')';
                    baseUnitLabel.textContent = p.unit;
                    convBulkLabel.textContent = bulkUnit;
                    // Jumlah krat/karton = bilangan bulat
                    qtyInput.step = '1';
                    qtyInput.min = '1';
                    qtyInput.placeholder = 'Contoh: 10';
                    qtyInput.value = old_qty || 1;
                    if (p.bulk_conversion > 0) {
                        convFactorInput.value = p.bulk_conversion;
                    }
                }

                updateTotal();
                updateMargin();
                updateNetWeight();
                updateTotalStock();
            }

            // === Search input ===
            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                if (q.length === 0) {
                    dropdown.classList.remove('show');
                    hiddenInput.value = '';
                    selectedProduct = null;
                    multiUnitSection.classList.add('d-none');
                    weightSection.classList.add('d-none');
                    countSection.classList.add('d-none');
                    qtyUnitLabel.textContent = '';
                    searchInput.classList.remove('is-invalid');
                    document.getElementById('productSearchError').classList.add('d-none');
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
                const qty = parseFloat(qtyInput.value) || 0;
                totalDisplay.value = formatRupiah(price * qty);
            }

            function updateMargin() {
                const modalKrat = parseNum(priceInput.value);
                const jualGrosir = parseNum(sellingBulkInput.value);
                const jualEcer = parseNum(sellingRetailInput.value);
                const qty = parseNum(qtyInput.value) || 1;

                if (modalKrat <= 0) {
                    marginInfo.classList.add('d-none');
                    return;
                }

                let marginHtml = [];

                // 1. Margin Grosir (Per Krat)
                if (jualGrosir > 0) {
                    const profitGrosir = jualGrosir - modalKrat;
                    const marginGrosir = (profitGrosir / modalKrat * 100).toFixed(1);
                    const color = profitGrosir >= 0 ? 'text-success' : 'text-danger';
                    const icon = profitGrosir >= 0 ? 'bi-check-circle' : 'bi-exclamation-triangle';
                    marginHtml.push(`<div class="${color}"><i class="bi ${icon}"></i> Margin Grosir: <strong>${formatRupiah(profitGrosir)}</strong>/krat (${marginGrosir}%)</div>`);
                }

                // 2. Margin Ecer (Per Kg / Pcs)
                if (jualEcer > 0 && selectedProduct) {
                    let isiPerKrat = 0;
                    let unitName = selectedProduct.unit || 'pcs';

                    if (selectedProduct.product_type === 'weight') {
                        const gross = parseNum(grossWeightInput.value);
                        const krat = parseNum(kratWeightInput.value);
                        isiPerKrat = gross - krat;
                    } else {
                        isiPerKrat = parseNum(convFactorInput.value) || 1;
                    }

                    if (isiPerKrat > 0) {
                        const modalEcer = modalKrat / isiPerKrat;
                        const profitEcer = jualEcer - modalEcer;
                        const marginEcer = (profitEcer / modalEcer * 100).toFixed(1);
                        const color = profitEcer >= 0 ? 'text-success' : 'text-danger';
                        const icon = profitEcer >= 0 ? 'bi-check-circle' : 'bi-exclamation-triangle';
                        marginHtml.push(`<div class="${color}"><i class="bi ${icon}"></i> Margin Ecer: <strong>${formatRupiah(profitEcer)}</strong>/${unitName} (${marginEcer}%)</div>`);
                    }
                }

                if (marginHtml.length > 0) {
                    marginInfo.classList.remove('d-none');
                    marginInfo.className = 'alert alert-light border py-2 mb-3';
                    marginText.innerHTML = marginHtml.join('<hr class="my-1">');
                } else {
                    marginInfo.classList.add('d-none');
                }
            }

            // Helper: parseFloat yang handle koma sebagai desimal (locale Indonesia)
            function parseNum(val) {
                if (!val) return 0;
                // Ganti koma jadi titik agar parseFloat bisa baca
                return parseFloat(String(val).replace(',', '.')) || 0;
            }

            // === Weight-based: Hitung berat bersih ===
            // Rumus: Bersih = Jumlah_Krat × (Berat_Kotor_per_Krat - Berat_Krat_Kosong) - Busuk
            function updateNetWeight() {
                if (!selectedProduct || selectedProduct.product_type !== 'weight') return;
                const grossPerKrat = parseNum(grossWeightInput.value);  // berat 1 krat + isi
                const kratWeight   = parseNum(kratWeightInput.value);   // berat krat kosong
                const qty          = parseNum(qtyInput.value);          // jumlah krat
                const spoilage     = parseNum(spoilageInput.value);     // busuk (kg)

                const netPerKrat   = grossPerKrat - kratWeight;         // berat isi per krat
                const totalNet     = (netPerKrat * qty) - spoilage;     // total berat bersih

                if (grossPerKrat <= 0) {
                    netWeightDisplay.value = '— kg';
                    netWeightDisplay.style.color = '';
                } else if (netPerKrat < 0) {
                    netWeightDisplay.value = totalNet.toFixed(3) + ' kg ⚠️ Berat krat > kotor!';
                    netWeightDisplay.style.color = '#dc3545';
                } else if (totalNet < 0) {
                    netWeightDisplay.value = totalNet.toFixed(3) + ' kg ⚠️ Busuk > bersih!';
                    netWeightDisplay.style.color = '#dc3545';
                } else {
                    netWeightDisplay.value = totalNet.toFixed(3) + ' kg';
                    netWeightDisplay.style.color = '#198754';
                }
            }

            // === Count-based: Hitung total stok ===
            function updateTotalStock() {
                if (!selectedProduct || selectedProduct.product_type !== 'count') return;
                const qty = parseNum(qtyInput.value);
                const factor = parseNum(convFactorInput.value);
                const spoilage = parseNum(spoilageInput.value);
                const total = (qty * factor) - spoilage;
                
                // Jika count tapi has_retail = false, factor biasanya 1 dan unit adalah bulk_unit
                let unit = selectedProduct.unit || 'pcs';
                if (!selectedProduct.has_retail) {
                    unit = selectedProduct.bulk_unit || selectedProduct.unit || 'krat';
                }

                if (qty <= 0 || factor <= 0) {
                    totalStockDisplay.value = '— ' + unit;
                    totalStockDisplay.style.color = '';
                } else if (total < 0) {
                    totalStockDisplay.value = total.toFixed(0) + ' ' + unit + ' ⚠️ NEGATIF!';
                    totalStockDisplay.style.color = '#dc3545';
                } else {
                    totalStockDisplay.value = total.toFixed(0) + ' ' + unit;
                    totalStockDisplay.style.color = '#198754';
                }
            }

            // === Event listeners ===
            priceInput.addEventListener('input', function() { updateTotal(); updateMargin(); });
            sellingBulkInput.addEventListener('input', updateMargin);
            sellingRetailInput.addEventListener('input', updateMargin);
            qtyInput.addEventListener('input', function() { updateTotal(); updateNetWeight(); updateTotalStock(); });
            grossWeightInput.addEventListener('input', updateNetWeight);
            kratWeightInput.addEventListener('input', updateNetWeight);
            convFactorInput.addEventListener('input', updateTotalStock);
            spoilageInput.addEventListener('input', function() { updateNetWeight(); updateTotalStock(); });

            // === Pre-fill jika ada old value ===
            <?php if(old('product_id')): ?>
                const oldProduct = products.find(p => p.id == <?php echo e(old('product_id')); ?>);
                if (oldProduct) {
                    selectProduct(oldProduct);
                }
            <?php endif; ?>

            updateTotal();
            updateMargin();
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/incoming_goods/create.blade.php ENDPATH**/ ?>