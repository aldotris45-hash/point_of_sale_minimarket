<aside id="appSidebar" class="offcanvas offcanvas-lg offcanvas-start bg-light border-end" tabindex="-1"
    aria-labelledby="appSidebarLabel" aria-modal="true" role="complementary">
    <div class="offcanvas-header d-lg-none">
        <h5 class="offcanvas-title" id="appSidebarLabel">Navigasi</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>

    @php
        $isAdmin = auth()->check() && auth()->user()->role === \App\Enums\RoleStatus::ADMIN->value;
    @endphp

    <div class="offcanvas-body p-0">
        <nav aria-label="Navigasi utama">
            <ul class="nav nav-pills flex-column">

                <!-- Utama -->
                <li class="nav-item px-3 py-2 text-muted text-uppercase small">Utama</li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('/') ? 'active' : '' }}"
                        href="{{ url('/') }}">
                        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mt-1">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('kasir') ? 'active' : '' }}"
                        href="{{ url('/kasir') }}">
                        <i class="bi bi-cash-stack"></i><span>Kasir</span>
                    </a>
                </li>
                <!-- Penjualan -->
                <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Penjualan</li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('transaksi*') ? 'active' : '' }}"
                        href="{{ url('/transaksi') }}">
                        <i class="bi bi-receipt"></i><span>Transaksi</span>
                    </a>
                </li>

                @if ($isAdmin)
                    <!-- Penjualan -->
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('pembayaran*') ? 'active' : '' }}"
                            href="{{ url('/pembayaran') }}">
                            <i class="bi bi-credit-card-2-front"></i><span>Pembayaran</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('laporan*') ? 'active' : '' }}"
                            href="{{ url('/laporan') }}">
                            <i class="bi bi-graph-up-arrow"></i><span>Laporan</span>
                        </a>
                    </li>

                    <!-- Master Data -->
                    <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Master Data</li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('supplier*') ? 'active' : '' }}"
                            href="{{ url('/supplier') }}">
                            <i class="bi bi-building"></i><span>Supplier</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('pelanggan*') ? 'active' : '' }}"
                            href="{{ url('/pelanggan') }}">
                            <i class="bi bi-person-lines-fill"></i><span>Pelanggan</span>
                        </a>
                    </li>

                    <!-- Katalog -->
                    <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Katalog</li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('kategori*') ? 'active' : '' }}"
                            href="{{ url('/kategori') }}">
                            <i class="bi bi-tags"></i><span>Manajemen Kategori</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('produk*') ? 'active' : '' }}"
                            href="{{ url('/produk') }}">
                            <i class="bi bi-box-seam"></i><span>Manajemen Produk</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('harga-produk*') ? 'active' : '' }}"
                            href="{{ url('/harga-produk') }}">
                            <i class="bi bi-tag"></i><span>Harga Produk (Sayur)</span>
                        </a>
                    </li>

                    <!-- Operasional -->
                    <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Operasional</li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('barang-masuk*') ? 'active' : '' }}"
                            href="{{ url('/barang-masuk') }}">
                            <i class="bi bi-box-arrow-in-down"></i><span>Barang Masuk</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('stok-opname*') ? 'active' : '' }}"
                            href="{{ url('/stok-opname') }}">
                            <i class="bi bi-clipboard-check"></i><span>Stok Opname</span>
                        </a>
                    </li>

                    <!-- Administrasi -->
                    <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Administrasi</li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('pengeluaran*') ? 'active' : '' }}"
                            href="{{ url('/pengeluaran') }}">
                            <i class="bi bi-cash-flow"></i><span>Pengeluaran</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('pengguna*') ? 'active' : '' }}"
                            href="{{ url('/pengguna') }}">
                            <i class="bi bi-people"></i><span>Manajemen Pengguna</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('pengaturan*') ? 'active' : '' }}"
                            href="{{ route('pengaturan.index') }}">
                            <i class="bi bi-gear"></i><span>Pengaturan</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('log-aktivitas*') ? 'active' : '' }}"
                            href="{{ url('/log-aktivitas') }}">
                            <i class="bi bi-clipboard-data"></i><span>Log Aktivitas</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
