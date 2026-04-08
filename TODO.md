# TODO - Fitur Cetak Invoice & Arus Kas

## Fitur 1: Cetak Invoice (A4) + Customer di Transaksi

### 1.1 Migration: Tambah customer_id ke transactions
- [x] `database/migrations/2026_03_10_000005_add_customer_id_to_transactions_table.php`

### 1.2 Update Model Transaction
- [x] Tambah `customer_id` ke `$fillable`
- [x] Tambah `customer()` belongsTo relationship

### 1.3 Update Model Customer
- [x] Tambah `transactions()` hasMany relationship

### 1.4 Update CashierServiceInterface
- [x] Tambah parameter `?int $customerId = null` di `checkout()` dan `hold()`

### 1.5 Update CashierService
- [x] Terima `$customerId` dan simpan ke transaksi

### 1.6 Update CheckoutRequest & HoldRequest
- [x] Tambah validasi `customer_id`

### 1.7 Update CashierController
- [x] Pass customers ke view
- [x] Kirim `customer_id` ke service

### 1.8 Update Cashier View
- [x] Tambah dropdown pilih pelanggan

### 1.9 Update TransactionController
- [x] Tambah method `invoice()`

### 1.10 Buat Invoice View (A4)
- [x] `resources/views/transactions/invoice.blade.php`
- [x] Info toko dari Settings (nama, alamat, telp, logo, no rek)
- [x] Info pelanggan
- [x] Tabel item
- [x] Untuk tempo: tampilkan piutang

### 1.11 Update Transaction Show View
- [x] Tambah tombol "Cetak Invoice"
- [x] Tampilkan nama pelanggan

### 1.12 Update Routes
- [x] Tambah route invoice

## Fitur 2: Arus Kas (Cash Flow)

### 2.1 Tambah Kategori Pengeluaran
- [x] `ExpenseCategory` enum sudah lengkap (gaji, listrik, bensin, supplies, lainnya)

### 2.2 Buat CashFlowController
- [x] `app/Http/Controllers/CashFlowController.php`

### 2.3 Buat Cash Flow View
- [x] `resources/views/cash_flow/index.blade.php`

### 2.4 Update Routes
- [x] Tambah route arus-kas

### 2.5 Update Sidebar
- [x] Tambah menu "Arus Kas"

## Update Pekerjaan (02 April 2026) -> Selesai ✅

### ✅ 1. Perbaikan Bug Import Excel (Legacy Data)
- [x] Memperbaiki *constraint violation* nomor invoice duplikat pada file `ImportLegacyExcel.php` saat mengeksekusi data Januari & Februari.
- [x] Sistem otomatis memberikan akhiran (suffix) jika mendeteksi *duplicate invoice number*.

### ✅ 2. Penambahan Fitur "Ubah Tanggal Transaksi" di Kasir
- [x] Memperbarui `CheckoutRequest` untuk menerima parameter format tanggal bebas.
- [x] Memperbarui Service & Controller Kasir untuk menampung waktu input.
- [x] Menambahkan antarmuka (UI) form input waktu (datetime-local) di Sidebar Kasir untuk menunjang pencatatan transaksi manual yang belum sempat masuk sistem.

### ✅ 3. Perombakan Total "Pengeluaran" -> "Buku Kas" (Cashbook)
- [x] **Database**: Migration merubah tabel `expenses` menjadi `cash_transactions` dengan menambahkan penanda `type` (Pemasukan/Pengeluaran).
- [x] **Controller & Enum**: Refactoring menyeluruh membedakan 2 jenis `in` dan `out` menggunakan `CashTransactionController.php`
- [x] **Views**: Memperbarui tampilan List Table yang menunjukkan indikator "hijau" (+) dan "merah" (-)
- [x] **Sidebar**: Mengubah nama menu menjadi Buku Kas.
- [x] **Arus Kas Dashboard**: Menggabungkan suntikan "Modal / Pemasukan Lain" dengan total Penjualan dalam query kalkulasi Net Balance hari/bulan.

### ✅ 4. Perbaikan Bug Dashboard (Soft Deletes)
- [x] Memperbaiki issue dimana Invoice transaksi cacat (Excel) yang susah dihapus manual dari Tong Sampah (Soft Deletes) masih terbaca di kalkulasi grafik omset dan dashboard `DashboardController.php` & `ReportService.php`.
- [x] Semua *raw query* (penggunaan DB::table) untuk laporan & dashboard telah disuntik filter `whereNull('deleted_at')`.

## Update Pekerjaan (03 April 2026) -> Selesai ✅

### ✅ 5. Perbaikan Bug Tanggal Transaksi Tidak Sinkron
- [x] **Model `Transaction.php`**: Menambahkan `created_at` dan `updated_at` ke `$fillable` agar tanggal custom tidak di-ignore oleh mass assignment protection Laravel.
- [x] **Service `CashierService.php`**: Method `generateInvoiceNumber()` sekarang menerima parameter tanggal opsional dan menggunakan tanggal transaksi (bukan `now()`) untuk bagian `{YYYY}`, `{MM}`, `{DD}` di nomor invoice.
- [x] **Interface `CashierServiceInterface.php`**: Update signature `generateInvoiceNumber()` agar konsisten dengan implementasi.

### ✅ 6. Penggabungan Harga Produk & Barang Masuk (Optimasi Workflow)
- [x] Menyatukan "Harga Produk (Sayur)" dengan form "Barang Masuk" untuk menghindari *double input*.
- [x] Implementasi fitur pencarian (*searchable dropdown*) menggunakan JavaScript kustom di form supaya cepat menemukan produk.
- [x] Menambahkan kalkulator margin (profit / rugi per unit) secara *real-time* saat menginput harga beli dan harga jual di form.
- [x] Menghapus menu Harga Produk dari Sidebar untuk menyederhanakan menu Admin.

### ✅ 7. Otomatisasi Buku Kas
- [x] **Enum Category**: Menambahkan `PENJUALAN` dan `PELUNASAN_TEMPO` ke dalam `CashTransactionCategory`.
- [x] **Integrasi Penjualan Tunai**: Memodifikasi `CashierService` agar otomatis membuat *record* Pemasukan Buku Kas setiap ada transaksi *Checkout Lunas*.
- [x] **Integrasi Piutang**: Memodifikasi `TransactionController` agar pelunasan piutang (Tempo) via *Mark as Paid* otomatis masuk ke Buku Kas.
- [x] **Pencegahan Double-Count**: Memodifikasi `CashFlowController` agar tidak terjadi hitung ganda antara data penjualan/pelunasan otomatis di Buku Kas dan laporan utama Arus Kas.

### ✅ 8. Cetak Laporan (Ekspor PDF) Arus Kas
- [x] Memisahkan core logika pengambilan data ke method *private* di `CashFlowController` untuk *reusability*.
- [x] Merancang template bersih (clean, korporat) `resources/views/cash_flow/pdf.blade.php`.
- [x] Menggunakan *library* DOMPDF untuk merealisasikan eksport dokumen A4 yang merangkum keseluruhan Pemasukan, Operasional, Laba Bersih, dan Break-down Harian.
- [x] Menambahkan tombol "Ekspor PDF" yang reaktif terhadap input periode tanggal yang sedang difilter pengguna di UI web.

### ✅ 9. Perbaikan Bug Lanjutan (Post-Review)
- [x] **Bug Kategori Dropdown**: Mencegah histori kategori `penjualan` dan `pelunasan_tempo` dari tampil di form input manual Buku Kas sehingga user tidak keliru melakukan *double input* manual.
- [x] **Bug Kesalahan String di Catatan Barang Masuk**: Memperbaiki variabel null exception `$data['notes']` pada `IncomingGoodService.php` guna memastikan error ketika string tidak ada tidak terjadi lagi (mengganti strict ternary dengan `!empty`).

### ✅ 10. Opsi Cetak Stempel & Tanda Tangan pada Dokumen
- [x] Menambahkan slot upload file gambar transparan (`.png`) untuk *Stempel Toko* dan *Tanda Tangan* pada Menu **Pengaturan Sistem**.
- [x] Menyisipkan opsi kotak-centang (checkbox) praktis `[ ] Tambah Tanda Tangan` dan `[ ] Tambah Stempel` langsung di layar Antarmuka **Detail Transaksi**.
- [x] Merakit antarmuka cetak dokumen (PDF dan HTML/Web Print) agar stempel dapat muncul melayang (*overlay* transparan) secara realistis menimpa tanda tangan saat dicetak jika opsi tersebut dicentang, baik untuk `Invoice` maupun `Faktur`.

### ✅ 11. Perbaikan Bug Fitur Stempel & TTD (Post-Review)
- [x] **Bug Dropdown PDF/WA tidak ikut terupdate**: Link "Download Invoice PDF", "Download Faktur PDF", "Invoice via WA", dan "Faktur via WA" di menu dropdown "Bagikan" kini ikut merespons status checkbox TTD & Stempel.
- [x] **Label validasi hilang**: Menambahkan label Indonesia `Tanda Tangan` dan `Stempel` di `UpdateSettingsRequest` agar pesan error validasi tidak menampilkan nama field mentah.
- [x] **Pesan `validation.uploaded` mentah**: Menambahkan terjemahan pesan upload gagal agar tampil berbahasa Indonesia yang informatif.
- [x] **Override PHP upload limit**: Menambahkan `public/.user.ini` (`upload_max_filesize=5M`, `post_max_size=10M`) agar file stempel/TTD bisa diupload tanpa ditolak PHP. Memerlukan restart `php8.3-fpm` di VPS.
- [x] **Posisi stempel lebih realistis**: Mengubah posisi stempel dari tengah-rata menjadi miring `-12deg` dan geser kanan atas untuk efek cap tangan sungguhan.

## Update Pekerjaan (08 April 2026) -> Selesai ✅

### ✅ 12. Visual Upgrade Halaman Katalog Publik
- [x] **Hero section**: Diubah menjadi gradien hijau gelap yang segar sebagai ganti biru polos.
- [x] **Tema per kategori**: Setiap section kategori kini punya identitas warna sendiri — Sayur (hijau), Buah (oranye-merah), Bumbu (coklat), Daging (merah), default (biru).
- [x] **Banner kategori**: Header section berupa banner gradient dengan emoji floating tipis di background (opacity 15-20%).
- [x] **Card produk**: Tiap card punya border aksen kiri berwarna sesuai kategori + hover glow tematik.
- [x] **Animasi**: Fade-slide-up per section saat halaman dibuka.
- [x] **Font**: Upgrade ke Plus Jakarta Sans (Google Fonts).

### ✅ 13. Fitur Promo Produk di Katalog & Kasir
- [x] **Migration**: Tambah kolom `promo_price` (decimal, nullable) dan `promo_label` (string, nullable) ke tabel `products`.
- [x] **Model Product**: Tambah `isOnPromo()` dan `effectivePrice()` helper methods.
- [x] **Admin Form Produk**: Toggle switch 🔥 "Tandai sebagai Promo" + input harga promo + label promo opsional dengan format rupiah otomatis.
- [x] **Katalog Publik**: Banner promo merah-oranye berdenyut + grid kartu promo (harga coret + hemat) + badge 🔥 di kartu kategori.
- [x] **Popup Promo**: Modal popup otomatis muncul 0.6 detik setelah halaman dibuka, tampil sekali per sesi (sessionStorage), bisa ditutup via ×, klik luar, atau ESC.
- [x] **Kasir**: `CashierService` kini menggunakan `effectivePrice()` sehingga harga promo otomatis berlaku di kasir saat checkout dan hold.

### ✅ 14. Perbaikan Bug (Post-Review 08 April 2026)
- [x] **Bug Stok Desimal**: Kolom `stock` dan `min_stock` sudah diubah ke decimal di migration sebelumnya (untuk sayur/kg), tapi validasi FormRequest masih `integer`. Diubah ke `numeric` + `step="any"` di input form.
- [x] **Bug JS Scope Promo**: Fungsi `normalizeToNumber` dan `formatRupiahDisplay` tidak bisa diakses kode promo karena scope IIFE. Seluruh kode JS form produk digabung ke dalam satu IIFE.
- [x] **Bug Promo Empty String**: `$data['promo_price'] ?? null` tidak menangkap empty string `''`. Diganti ke `?: null` di `ProductService::create()` dan `update()`.
- [x] **Bug Hemat Negatif**: Query promo di `CatalogController` tidak memfilter `promo_price >= price`, sehingga bisa tampil "Hemat -Rp X". Ditambah `whereColumn('promo_price', '<', 'price')`.
