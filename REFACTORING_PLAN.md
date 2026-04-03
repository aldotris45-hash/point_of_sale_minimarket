# Rencana Refactoring Controller & Pembersihan Kode

Dokumen ini berisi draf perencanaaan dari arsitektur perangkat lunak *"clean code"* yang akan diimplementasikan bertahap pada *Point of Sales Integrator Application* ini.

Tujuan utama dari refactoring ini adalah untuk "mengompres" *Controller* yang membengkak (Fat Controllers) seperti `TransactionController` (±20KB) dan `ReportController` (±11KB) dengan cara memecah logika komputasi berat ke komponen yang spesifik. Hal ini akan mempermudah integrasi sistem POS di kemudian hari.

---

## 1. Pemisahan Logika Bisnis ke *Service Classes*
Mengekstraksi proses komputasi yang rumit dari *Controller*. Semua *controller* hanya bertugas mengarahkan alur *request/response*, bukan memproses kalkulasi, mengontrol mutasi database *stock*, atau kalkulasi *cash register*.

- **`app/Services/Transactions/TransactionService.php`**
  Khusus mengurus kalkulasi transaksi (`checkout`, `suspend`, pelunasan, pengembalian/pemotongan stok barang pada inventori).
- **`app/Services/Reports/ReportBuilderService.php`**
  Khusus menangani query aggregat, rentang waktu pencarian, omset harian.

## 2. Pemisahan Logika PDF & Render Khusus
Terkait kode untuk pencetakan nota/pdf, pembuatan *barcode*, stempel, rotasi gambar, dll.
- **`app/Services/Pdfs/InvoicePdfBuilder.php`** (atau sejenisnya)
  Spesifik merender DomPDF, melakukan filter resolusi *absolute path* dan penyematan margin stempel. Sehingga `TransactionController->pdfData()` tidak lagi diperlukan secara lokal.

## 3. Form Requests (Validasi Standard Laravel)
Alih-alih menggunakan `$request->validate()` yang panjangnya 20-30 baris per fungsi simpan data, kita pecah menggunakan form custom:
- **`app/Http/Requests/StoreTransactionRequest.php`**
- **`app/Http/Requests/UpdateProductRequest.php`**

---

## Urutan Eksekusi Bertahap (Planned Progress)
Proses refactoring WAJIB dilakukan perlahan berdasarkan prioritas agar tidak merusak fungsionalitas yang ada (Breaking Changes):

1. **Phase 1: PDF Preparation Separation**
   Mencabut fungsi-fungsi *print* dan *DOM PDF processing* terlebih dahulu karena paling terisolasi.
2. **Phase 2: Validation Isolation**
   Membersihkan `store()` dan `update()` pada `TransactionController` dengan *Form Request*.
3. **Phase 3: Deep Logic Extraction (TransactionService)**
   Memindahkan proses transaksi inti, validasi stok, log aktivitas, dan update tabel *cash register*.
4. **Phase 4: Dashboard & Report Aggregation**
   Merapikan *dashboard* agar *query*-nya terpanggil dari *Service Classes*.

*Document generated dynamically during planning & research phase.*
