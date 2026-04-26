<?php echo csrf_field(); ?>
<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="sku" class="form-label">SKU</label>
        <input id="sku" name="sku" type="text" class="form-control <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            value="<?php echo e(old('sku', $product->sku ?? '')); ?>" maxlength="100" required>
        <?php $__errorArgs = ['sku'];
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
    <div class="col-12 col-md-6">
        <label for="name" class="form-label">Nama Produk</label>
        <input id="name" name="name" type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            value="<?php echo e(old('name', $product->name ?? '')); ?>" maxlength="255" required>
        <?php $__errorArgs = ['name'];
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

    <div class="col-12 col-md-6">
        <label for="category_id" class="form-label">Kategori</label>
        <select id="category_id" name="category_id" class="form-select <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            required>
            <option value="">Pilih kategori</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($id); ?>" <?php if(old('category_id', $product->category_id ?? '') == $id): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['category_id'];
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
        <label for="price_display" class="form-label">Harga</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input id="price_display" name="price_display" type="text"
                class="form-control <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" inputmode="decimal"
                value="<?php echo e((float) old('price', $product->price ?? 0) == 0 ? '0' : number_format((float) old('price', $product->price ?? 0), 2, ',', '.')); ?>"
                placeholder="0" autocomplete="off" required>
            <input id="price" name="price" type="hidden" value="<?php echo e(old('price', $product->price ?? 0)); ?>">
            <?php $__errorArgs = ['price'];
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

    <div class="col-12 col-md-3">
        <label for="stock" class="form-label">Stok</label>
        <input id="stock" name="stock" type="number" min="0" step="any"
            class="form-control <?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('stock', $product->stock ?? 0)); ?>">
        <?php $__errorArgs = ['stock'];
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
        <label for="min_stock" class="form-label">Stok Minimal</label>
        <input id="min_stock" name="min_stock" type="number" min="0" step="any"
            class="form-control <?php $__errorArgs = ['min_stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            value="<?php echo e(old('min_stock', $product->min_stock ?? 0)); ?>">
        <?php $__errorArgs = ['min_stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <div class="form-text">Notifikasi akan muncul jika stok <= minimal.</div>
        </div>

        <div class="col-12 col-md-3">
            <label for="expiry_date" class="form-label">Tanggal Kadaluarsa</label>
            <input id="expiry_date" name="expiry_date" type="date"
                class="form-control <?php $__errorArgs = ['expiry_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                value="<?php echo e(old('expiry_date', optional($product->expiry_date ?? null)->format('Y-m-d'))); ?>">
            <?php $__errorArgs = ['expiry_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <div class="form-text">Kosongkan jika tidak berlaku.</div>
        </div>
    </div>

    
    
    
    <div class="col-12">
        <hr class="my-2">
        <h6 class="fw-semibold mb-3"><i class="bi bi-boxes"></i> Pengaturan Satuan & Harga</h6>

        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="has_retail" value="0">
                <input class="form-check-input" type="checkbox" role="switch"
                    id="has_retail_toggle" name="has_retail" value="1"
                    <?php echo e(old('has_retail', $product->has_retail ?? true) ? 'checked' : ''); ?>>
                <label class="form-check-label fw-semibold text-primary" for="has_retail_toggle">
                    Bisa Dijual Eceran? (Ada harga kg/pcs)
                </label>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-3 retail-field">
                <label for="product_type" class="form-label">Tipe Produk <span class="text-danger">*</span></label>
                <select id="product_type" name="product_type" class="form-select <?php $__errorArgs = ['product_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="weight" <?php if(old('product_type', $product->product_type ?? 'weight') === 'weight'): echo 'selected'; endif; ?>>⚖️ Timbangan (kg)</option>
                    <option value="count" <?php if(old('product_type', $product->product_type ?? '') === 'count'): echo 'selected'; endif; ?>>📦 Hitungan (pcs/botol)</option>
                </select>
                <?php $__errorArgs = ['product_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <div class="form-text">Timbangan = dijual per kg. Hitungan = dijual per pcs/botol.</div>
            </div>

            <div class="col-12 col-md-3 retail-field">
                <label for="unit" class="form-label">Satuan Eceran <span class="text-danger">*</span></label>
                <input id="unit" name="unit" type="text" class="form-control <?php $__errorArgs = ['unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('unit', $product->unit ?? 'kg')); ?>" placeholder="kg, pcs, botol, bungkus">
                <?php $__errorArgs = ['unit'];
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
                <label for="bulk_unit" class="form-label">Satuan <span class="bulk-label-text">Grosir</span> <small class="text-muted">(opsional)</small></label>
                <input id="bulk_unit" name="bulk_unit" type="text" class="form-control <?php $__errorArgs = ['bulk_unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('bulk_unit', $product->bulk_unit ?? '')); ?>" placeholder="krat, karton, kardus">
                <?php $__errorArgs = ['bulk_unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <div class="form-text non-retail-hint text-warning" style="display:none;"><i class="bi bi-info-circle"></i> Jika tidak diecer, ini jadi satuan utama.</div>
            </div>

            <div class="col-12 col-md-3" id="bulkConversionDiv">
                <label for="bulk_conversion" class="form-label">
                    <span id="bulkConvLabel2">Isi</span> per <span id="bulkConvLabel">krat</span>
                    <span class="text-danger">*</span>
                </label>
                <input id="bulk_conversion" name="bulk_conversion" type="number" step="0.001" min="0"
                    class="form-control <?php $__errorArgs = ['bulk_conversion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('bulk_conversion', $product->bulk_conversion ?? '')); ?>" placeholder="Contoh: 6">
                <?php $__errorArgs = ['bulk_conversion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <div class="form-text">1 <span class="bulk-unit-text">krat</span> = berapa <span class="base-unit-text">kg</span>?</div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-12 col-md-4 retail-field">
                <label for="price_per_unit_display" class="form-label">Harga Jual Eceran (per <span class="base-unit-text2">kg</span>) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input id="price_per_unit_display" type="text" inputmode="decimal"
                        class="form-control <?php $__errorArgs = ['price_per_unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e((float) old('price_per_unit', $product->price_per_unit ?? $product->price ?? 0) == 0 ? '0' : number_format((float) old('price_per_unit', $product->price_per_unit ?? $product->price ?? 0), 0, ',', '.')); ?>"
                        placeholder="0" autocomplete="off">
                    <input id="price_per_unit" name="price_per_unit" type="hidden"
                        value="<?php echo e(old('price_per_unit', $product->price_per_unit ?? $product->price ?? 0)); ?>">
                    <?php $__errorArgs = ['price_per_unit'];
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

            <div class="col-12 col-md-4" id="bulkPriceDiv">
                <label for="price_per_bulk_display" class="form-label">Harga Jual Grosir (per <span class="bulk-unit-text2">krat</span>)</label>
                <div class="input-group">
                    <span class="input-group-text bg-warning text-dark">Rp</span>
                    <input id="price_per_bulk_display" type="text" inputmode="decimal"
                        class="form-control <?php $__errorArgs = ['price_per_bulk'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e((float) old('price_per_bulk', $product->price_per_bulk ?? 0) == 0 ? '' : number_format((float) old('price_per_bulk', $product->price_per_bulk ?? 0), 0, ',', '.')); ?>"
                        placeholder="Kosongkan = otomatis dari eceran" autocomplete="off">
                    <input id="price_per_bulk" name="price_per_bulk" type="hidden"
                        value="<?php echo e(old('price_per_bulk', $product->price_per_bulk ?? '')); ?>">
                    <?php $__errorArgs = ['price_per_bulk'];
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
                <div class="form-text">Kosongkan = harga eceran × isi per <span class="bulk-unit-text3">krat</span>.</div>
            </div>

            <div class="col-12 col-md-4" id="kratWeightDiv">
                <label for="krat_weight_kg" class="form-label">Berat Krat Kosong (kg)</label>
                <input id="krat_weight_kg" name="krat_weight_kg" type="number" step="0.001" min="0"
                    class="form-control <?php $__errorArgs = ['krat_weight_kg'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('krat_weight_kg', $product->krat_weight_kg ?? '')); ?>" placeholder="Contoh: 0.5">
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
                <div class="form-text">Untuk menghitung berat bersih saat barang masuk.</div>
            </div>
        </div>
    </div>

    
    
    
    <div class="col-12">
        <hr class="my-2">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch"
                    id="promo_toggle"
                    <?php echo e(old('promo_price', $product->promo_price ?? null) ? 'checked' : ''); ?>>
                <label class="form-check-label fw-semibold" for="promo_toggle">
                    🔥 Tandai sebagai Promo
                </label>
            </div>
        </div>

        <div id="promo_fields" class="row g-3" style="<?php echo e(old('promo_price', $product->promo_price ?? null) ? '' : 'display:none;'); ?>">
            <div class="col-12 col-md-4">
                <label for="promo_price_display" class="form-label">Harga Promo <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-danger text-white">Rp</span>
                    <input id="promo_price_display" type="text" inputmode="decimal"
                        class="form-control <?php $__errorArgs = ['promo_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('promo_price', $product->promo_price ?? '') ? number_format((float) old('promo_price', $product->promo_price ?? 0), 0, ',', '.') : ''); ?>"
                        placeholder="Harga setelah diskon" autocomplete="off">
                    <input id="promo_price" name="promo_price" type="hidden"
                        value="<?php echo e(old('promo_price', $product->promo_price ?? '')); ?>">
                    <?php $__errorArgs = ['promo_price'];
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
                <div class="form-text">Harga normal (Rp <?php echo e(number_format((float) old('price', $product->price ?? 0), 0, ',', '.')); ?>) akan jadi harga coret di katalog.</div>
            </div>
            <div class="col-12 col-md-4">
                <label for="promo_label" class="form-label">Label Promo <span class="text-muted">(opsional)</span></label>
                <input id="promo_label" name="promo_label" type="text"
                    class="form-control <?php $__errorArgs = ['promo_label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('promo_label', $product->promo_label ?? '')); ?>"
                    placeholder="e.g. Flash Sale, Hemat 30%" maxlength="50">
                <?php $__errorArgs = ['promo_label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <div class="form-text">Kosongkan untuk label otomatis "PROMO".</div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('script'); ?>
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

                function setupRupiahInput(displayEl, hiddenEl) {
                    if (!displayEl || !hiddenEl) return;
                    function sync() { hiddenEl.value = normalizeToNumber(displayEl.value); }
                    function onInput() {
                        const pos = displayEl.selectionStart;
                        const before = displayEl.value.length;
                        displayEl.value = formatRupiahDisplay(displayEl.value);
                        const delta = displayEl.value.length - before;
                        try { displayEl.setSelectionRange(pos + delta, pos + delta); } catch(e) {}
                        sync();
                    }
                    if (hiddenEl.value && !displayEl.value) {
                        displayEl.value = formatRupiahDisplay(String(hiddenEl.value).replace(/\./g, ','));
                    } else { sync(); }
                    displayEl.addEventListener('input', onInput);
                    const form = displayEl.closest('form');
                    if (form) form.addEventListener('submit', sync);
                }

                // ── Harga utama (legacy) ─────────────────────────
                setupRupiahInput(
                    document.getElementById('price_display'),
                    document.getElementById('price')
                );

                // ── Harga Eceran (price_per_unit) ────────────────
                setupRupiahInput(
                    document.getElementById('price_per_unit_display'),
                    document.getElementById('price_per_unit')
                );

                // ── Harga Grosir (price_per_bulk) ────────────────
                setupRupiahInput(
                    document.getElementById('price_per_bulk_display'),
                    document.getElementById('price_per_bulk')
                );

                // ── Sync price_per_unit ke price (legacy) saat submit ──
                const mainForm = document.querySelector('form');
                if (mainForm) {
                    mainForm.addEventListener('submit', function() {
                        const ppu = document.getElementById('price_per_unit');
                        const ppb = document.getElementById('price_per_bulk');
                        const ph  = document.getElementById('price');
                        const isRetail = document.getElementById('has_retail_toggle').checked;
                        
                        if (ph) {
                            if (isRetail) {
                                ph.value = ppu ? ppu.value : 0;
                            } else {
                                ph.value = ppb ? ppb.value : 0;
                            }
                        }
                    });
                }

                // ── Multi-unit: Dynamic labels & visibility ────────
                const unitInput     = document.getElementById('unit');
                const bulkUnitInput = document.getElementById('bulk_unit');
                const productType   = document.getElementById('product_type');
                
                const $hasRetailToggle = $('#has_retail_toggle');
                const $retailFields = $('.retail-field');
                const $bulkConversionDiv = $('#bulkConversionDiv');
                const $bulkPriceDiv = $('#bulkPriceDiv');
                const $kratWeightDiv = $('#kratWeightDiv');

                function updateVisibility() {
                    const isRetail = $hasRetailToggle.is(':checked');
                    const type = productType?.value || 'weight';
                    const baseUnit = (unitInput?.value || 'pcs').trim();
                    const bulkUnit = (bulkUnitInput?.value || '').trim();

                    if (isRetail) {
                        $retailFields.show();
                        $('.bulk-label-text').text('Grosir');
                        $('.non-retail-hint').hide();

                        if (bulkUnit) {
                            $bulkConversionDiv.show();
                            $bulkPriceDiv.show();
                            
                            $('.bulk-unit-text').text(bulkUnit);
                            $('.bulk-unit-text2').text(bulkUnit);
                            $('.bulk-unit-text3').text(bulkUnit);
                            $('#bulkConvLabel').text(bulkUnit);
                            
                            $('.base-unit-text').text(baseUnit);
                            $('.base-unit-text2').text(baseUnit);
                            
                            if (type === 'weight') {
                                $('#bulkConvLabel2').text('Berat Isi');
                                $kratWeightDiv.show();
                            } else {
                                $('#bulkConvLabel2').text('Isi');
                                $kratWeightDiv.hide();
                            }
                        } else {
                            $bulkConversionDiv.hide();
                            $bulkPriceDiv.hide();
                            $kratWeightDiv.hide();
                            $('.base-unit-text2').text(baseUnit);
                        }
                    } else {
                        // Not retail
                        $retailFields.hide();
                        $('.bulk-label-text').text('Utama');
                        $('.non-retail-hint').show();
                        
                        $bulkConversionDiv.hide();
                        $kratWeightDiv.hide();
                        $bulkPriceDiv.show(); // Show bulk price as the main price
                        
                        const showUnit = bulkUnit || 'krat';
                        $('.bulk-unit-text2').text(showUnit);
                        $('.bulk-unit-text3').text(showUnit);
                    }
                }

                $hasRetailToggle.on('change', updateVisibility);
                if (productType) productType.addEventListener('change', updateVisibility);
                if (unitInput) {
                    unitInput.addEventListener('input', function() {
                        this.dataset.userEdited = '1';
                        updateVisibility();
                    });
                }
                if (bulkUnitInput) bulkUnitInput.addEventListener('input', updateVisibility);

                // Init on load
                updateVisibility();

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

                setupRupiahInput(promoPriceDisplay, promoPriceHidden);
            })();
        </script>
    <?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\nwlen\Documents\selalu_fresh\resources\views/products/_form.blade.php ENDPATH**/ ?>