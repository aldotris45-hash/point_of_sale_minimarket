@extends('layouts.app')

@section('title', 'Kas Toko')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">💰 Kas Toko</h4>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted">💵 Saldo Kas Saat Ini</h6>
                    <h3 class="fw-bold text-primary">Rp {{ number_format($currentBalance, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="text-muted">📥 Total Pemasukan</h6>
                    <h4 class="fw-bold text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6 class="text-muted">📤 Total Pengeluaran</h6>
                    <h4 class="fw-bold text-danger">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Isi Saldo & Pengeluaran --}}
    <div class="row mb-4">
        {{-- Isi Saldo --}}
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white fw-semibold">
                    📥 Isi / Tambah Saldo Kas
                </div>
                <div class="card-body">
                    <form action="{{ route('kas.topup') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Jumlah (Rp)</label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                placeholder="Contoh: 500000" min="1" required>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                                placeholder="Contoh: Modal awal, Setoran dari pemilik" required>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-success w-100">💾 Tambah Saldo</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Catat Pengeluaran --}}
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white fw-semibold">
                    📤 Catat Pengeluaran Belanja
                </div>
                <div class="card-body">
                    <form action="{{ route('kas.expense') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Jumlah Pengeluaran (Rp)</label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                placeholder="Contoh: 200000" min="1" required>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan Belanja</label>
                            <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                                placeholder="Contoh: Beli tomat 5kg, Beli bawang" required>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-danger w-100">💸 Catat Pengeluaran</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('kas.index') }}" class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Cari keterangan..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select">
                        <option value="">Semua Transaksi</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>📥 Pemasukan</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>📤 Pengeluaran</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Riwayat --}}
    <div class="card">
        <div class="card-header fw-semibold">📋 Riwayat Transaksi Kas</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Keterangan</th>
                        <th>Jumlah</th>
                        <th>Saldo Sebelum</th>
                        <th>Saldo Sesudah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $kas)
                    <tr class="{{ $kas->type === 'income' ? 'table-success' : 'table-danger' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $kas->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($kas->type === 'income')
                                <span class="badge bg-success">📥 Pemasukan</span>
                            @else
                                <span class="badge bg-danger">📤 Pengeluaran</span>
                            @endif
                        </td>
                        <td>{{ $kas->description }}</td>
                        <td class="fw-semibold {{ $kas->type === 'income' ? 'text-success' : 'text-danger' }}">
                            {{ $kas->type === 'income' ? '+' : '-' }} Rp {{ number_format($kas->amount, 0, ',', '.') }}
                        </td>
                        <td>Rp {{ number_format($kas->balance_before, 0, ',', '.') }}</td>
                        <td class="fw-bold">Rp {{ number_format($kas->balance_after, 0, ',', '.') }}</td>
                        <td>
                            <form action="{{ route('kas.destroy', $kas) }}" method="POST"
                                onsubmit="return confirm('Hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada transaksi kas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $histories->links() }}</div>
</div>
@endsection
