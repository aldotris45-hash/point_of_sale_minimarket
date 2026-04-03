@extends('layouts.app')

@section('title', $type === 'in' ? 'Catat Pemasukan Kas' : 'Catat Pengeluaran Kas')

@section('content')
    <section class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h3 mb-0">{{ $type === 'in' ? 'Catat Pemasukan Kas' : 'Catat Pengeluaran Kas' }}</h1>
            <a href="{{ route('buku-kas.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>

        <div class="card shadow-sm" style="max-width: 600px;">
            <div class="card-body">
                <form action="{{ route('buku-kas.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="mb-3">
                        <label for="date" class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                @php
                                    $autoCategories = ['penjualan', 'pelunasan_tempo'];
                                @endphp
                                @if(in_array($cat->value, $autoCategories))
                                    @continue
                                @endif
                                @if(
                                    ($type === 'in' && in_array($cat->value, ['tambahan_modal', 'pendapatan_lain'])) ||
                                    ($type === 'out' && !in_array($cat->value, ['tambahan_modal', 'pendapatan_lain']))
                                )
                                    <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>
                                        {{ $cat->label() }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" min="0.01" step="0.01" value="{{ old('amount') }}" placeholder="Contoh: 150000" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Keterangan</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Catatan tambahan (opsional)">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Bukti Transaksi (Opsional)</label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text">Format: JPG, PNG, PDF. Maks: 5MB.</div>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn {{ $type === 'in' ? 'btn-success' : 'btn-danger' }}"><i class="bi bi-save"></i> Simpan Catatan</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
