<aside id="appSidebar" class="offcanvas offcanvas-lg offcanvas-start bg-light border-end" tabindex="-1"
    aria-labelledby="appSidebarLabel" aria-modal="true" role="complementary">
    <div class="offcanvas-header d-lg-none">
        <h5 class="offcanvas-title" id="appSidebarLabel">Navigasi</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>

    <?php
        $isAdmin = auth()->check() && auth()->user()->role === \App\Enums\RoleStatus::ADMIN->value;
    ?>

    <div class="offcanvas-body p-0">
        <nav aria-label="Navigasi utama">
            <ul class="nav nav-pills flex-column">

                <!-- Utama -->
                <li class="nav-item px-3 py-2 text-muted text-uppercase small">Utama</li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('/') ? 'active' : ''); ?>"
                        href="<?php echo e(url('/')); ?>">
                        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item mt-1">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('kasir') ? 'active' : ''); ?>"
                        href="<?php echo e(url('/kasir')); ?>">
                        <i class="bi bi-cash-stack"></i><span>Kasir</span>
                    </a>
                </li>
                <!-- Penjualan -->
                <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Penjualan</li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('transaksi*') ? 'active' : ''); ?>"
                        href="<?php echo e(url('/transaksi')); ?>">
                        <i class="bi bi-receipt"></i><span>Transaksi</span>
                    </a>
                </li>

                <?php if($isAdmin): ?>
                    <!-- Penjualan -->
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('pembayaran*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/pembayaran')); ?>">
                            <i class="bi bi-credit-card-2-front"></i><span>Pembayaran</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('laporan*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/laporan')); ?>">
                            <i class="bi bi-graph-up-arrow"></i><span>Laporan</span>
                        </a>
                    </li>

                    <!-- Master Data -->
                    <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Master Data</li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('supplier*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/supplier')); ?>">
                            <i class="bi bi-building"></i><span>Supplier</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('pelanggan*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/pelanggan')); ?>">
                            <i class="bi bi-person-lines-fill"></i><span>Pelanggan</span>
                        </a>
                    </li>

                    <!-- Katalog -->
                    <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Katalog</li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('kategori*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/kategori')); ?>">
                            <i class="bi bi-tags"></i><span>Manajemen Kategori</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('produk*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/produk')); ?>">
                            <i class="bi bi-box-seam"></i><span>Manajemen Produk</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('katalog*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/katalog')); ?>" target="_blank">
                            <i class="bi bi-megaphone"></i><span>Katalog Harga Klien</span>
                        </a>
                    </li>

                    <!-- Operasional -->
                    <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Operasional</li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('barang-masuk*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/barang-masuk')); ?>">
                            <i class="bi bi-box-arrow-in-down"></i><span>Barang Masuk</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('stok-opname*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/stok-opname')); ?>">
                            <i class="bi bi-clipboard-check"></i><span>Stok Opname</span>
                        </a>
                    </li>

                    <!-- Administrasi -->
                    <li class="nav-item px-3 pt-3 pb-2 text-muted text-uppercase small">Administrasi</li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('arus-kas*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/arus-kas')); ?>">
                            <i class="bi bi-arrow-left-right"></i><span>Arus Kas</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('buku-kas*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/buku-kas')); ?>">
                            <i class="bi bi-wallet2"></i><span>Buku Kas</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('pajak*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/pajak')); ?>">
                            <i class="bi bi-calculator"></i><span>Laporan Pajak</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('pengguna*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/pengguna')); ?>">
                            <i class="bi bi-people"></i><span>Manajemen Pengguna</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('pengaturan*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('pengaturan.index')); ?>">
                            <i class="bi bi-gear"></i><span>Pengaturan</span>
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a class="nav-link d-flex align-items-center gap-2 <?php echo e(request()->is('log-aktivitas*') ? 'active' : ''); ?>"
                            href="<?php echo e(url('/log-aktivitas')); ?>">
                            <i class="bi bi-clipboard-data"></i><span>Log Aktivitas</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</aside>
<?php /**PATH C:\Users\nwlen\Documents\point_of_sale_minimarket.worktrees\copilot-worktree-2026-03-10T06-54-12\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>