@extends('layouts.app')

@section('title', 'Tambah Pelanggan')

@section('content')
    <section class="container py-4">
        <div class="mb-3">
            <h1 class="h3 d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-people"></i> Tambah Pelanggan
            </h1>
            <p class="text-muted mb-0">Isi data pelanggan baru.</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('pelanggan.store') }}" method="POST" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required maxlength="255" placeholder="Nama pelanggan">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror"
                            rows="2" maxlength="500" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="phone" class="form-label">No. HP / WA</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}" maxlength="30" placeholder="08xx-xxxx-xxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" maxlength="255" placeholder="email@domain.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Keterangan</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                            rows="2" maxlength="500" placeholder="Keterangan tambahan">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan
                        </button>
                        <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
