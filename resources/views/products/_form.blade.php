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
        <label for="price" class="form-label">Harga</label>
        <input id="price" name="price" type="number" step="0.01" min="0"
            class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->price ?? 0) }}"
            required>
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-3">
        <label for="stock" class="form-label">Stok</label>
        <input id="stock" name="stock" type="number" min="0"
            class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock ?? 0) }}">
        @error('stock')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
