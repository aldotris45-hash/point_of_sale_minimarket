@extends('layouts.app')

@section('title', 'Edit Pengeluaran')

@section('content')
    <section class="container-fluid py-4">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Pengeluaran</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('pengeluaran.update', $expense) }}" method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->value }}" {{ (old('category', $expense->category?->value) === $cat->value) ? 'selected' : '' }}>
                                            {{ $cat->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="expense_date" class="form-label">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                                <input type="date" id="expense_date" name="expense_date" 
                                    value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                                    class="form-control @error('expense_date') is-invalid @enderror" required>
                                @error('expense_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="amount" name="amount" 
                                        value="{{ old('amount', number_format($expense->amount,0,',','')) }}"
                                        class="form-control @error('amount') is-invalid @enderror" 
                                        placeholder="0" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Keterangan</label>
                                <textarea id="description" name="description" rows="3" 
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Contoh: Gaji bulan Maret 2026, Bayar listrik PLN, etc.">{{ old('description', $expense->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="file" class="form-label">Bukti (Foto/PDF) - Opsional</label>
                                @if($expense->file_path)
                                    <div class="mb-1">
                                        <a href="{{ asset($expense->file_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-file-earmark"></i> Lihat Bukti</a>
                                    </div>
                                @endif
                                <input type="file" id="file" name="file" 
                                    class="form-control @error('file') is-invalid @enderror"
                                    accept="image/jpeg,image/png,application/pdf">
                                <small class="form-text text-muted">Maksimal 5MB. Format: JPG, PNG, atau PDF</small>
                                @error('file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Perbarui</button>
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
            const amountInput = document.getElementById('amount');
            if (amountInput) {
                const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
                amountInput.addEventListener('input', function() {
                    let digits = this.value.replace(/[^0-9]/g, '');
                    this.value = digits ? fmt(digits) : '';
                });

                const form = amountInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        let cleaned = amountInput.value.replace(/[^0-9]/g, '');
                        amountInput.value = cleaned || '0';
                    });
                }
            }
        });
    </script>
@endpush
