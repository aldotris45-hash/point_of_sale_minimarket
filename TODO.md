# TODO - Fitur Cetak Invoice & Arus Kas

## Fitur 1: Cetak Invoice (A4) + Customer di Transaksi

### 1.1 Migration: Tambah customer_id ke transactions
- [x] `database/migrations/2026_03_10_000005_add_customer_id_to_transactions_table.php`

### 1.2 Update Model Transaction
- [ ] Tambah `customer_id` ke `$fillable`
- [ ] Tambah `customer()` belongsTo relationship

### 1.3 Update Model Customer
- [ ] Tambah `transactions()` hasMany relationship

### 1.4 Update CashierServiceInterface
- [ ] Tambah parameter `?int $customerId = null` di `checkout()` dan `hold()`

### 1.5 Update CashierService
- [ ] Terima `$customerId` dan simpan ke transaksi

### 1.6 Update CheckoutRequest & HoldRequest
- [ ] Tambah validasi `customer_id`

### 1.7 Update CashierController
- [ ] Pass customers ke view
- [ ] Kirim `customer_id` ke service

### 1.8 Update Cashier View
- [ ] Tambah dropdown pilih pelanggan

### 1.9 Update TransactionController
- [ ] Tambah method `invoice()`

### 1.10 Buat Invoice View (A4)
- [ ] `resources/views/transactions/invoice.blade.php`
- [ ] Info toko dari Settings (nama, alamat, telp, logo, no rek)
- [ ] Info pelanggan
- [ ] Tabel item
- [ ] Untuk tempo: tampilkan piutang

### 1.11 Update Transaction Show View
- [ ] Tambah tombol "Cetak Invoice"
- [ ] Tampilkan nama pelanggan

### 1.12 Update Routes
- [ ] Tambah route invoice

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

