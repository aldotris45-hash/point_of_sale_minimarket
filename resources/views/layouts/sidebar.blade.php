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
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('cashier') ? 'active' : '' }}"
                        href="{{ url('/cashier') }}">
                        <i class="bi bi-cash-stack"></i><span>Kasir</span>
                    </a>
                </li>

                <!-- Penjualan -->
                <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Penjualan</li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('transactions*') ? 'active' : '' }}"
                        href="{{ url('/transactions') }}">
                        <i class="bi bi-receipt"></i><span>Transaksi</span>
                    </a>
                </li>
                <li class="nav-item mt-1">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('payments*') ? 'active' : '' }}"
                        href="{{ url('/payments') }}">
                        <i class="bi bi-credit-card-2-front"></i><span>Pembayaran</span>
                    </a>
                </li>
                <li class="nav-item mt-1">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('reports*') ? 'active' : '' }}"
                        href="{{ url('/reports') }}">
                        <i class="bi bi-graph-up-arrow"></i><span>Laporan</span>
                    </a>
                </li>

                <!-- Katalog -->
                <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Katalog</li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('categories*') ? 'active' : '' }}"
                        href="{{ url('/categories') }}">
                        <i class="bi bi-tags"></i><span>Manajemen Kategori</span>
                    </a>
                </li>
                <li class="nav-item mt-1">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('products*') ? 'active' : '' }}"
                        href="{{ url('/products') }}">
                        <i class="bi bi-box-seam"></i><span>Manajemen Produk</span>
                    </a>
                </li>

                <!-- Administrasi (Admin saja) -->
                @if ($isAdmin)
                    <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Administrasi</li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('users*') ? 'active' : '' }}"
                            href="{{ url('/users') }}">
                            <i class="bi bi-people"></i><span>Manajemen Pengguna</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('settings*') ? 'active' : '' }}"
                            href="{{ url('/settings') }}">
                            <i class="bi bi-gear"></i><span>Pengaturan</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('activity-logs*') ? 'active' : '' }}"
                            href="{{ url('/activity-logs') }}">
                            <i class="bi bi-clipboard-data"></i><span>Log Aktivitas</span>
                        </a>
                    </li>
                @endif

                <!-- Bantuan -->
                <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Bantuan</li>
                <li class="nav-item mb-3">
                    <a class="nav-link d-flex align-items-center gap-2 {{ request()->is('help*') ? 'active' : '' }}"
                        href="{{ url('/help') }}">
                        <i class="bi bi-life-preserver"></i><span>Panduan</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
