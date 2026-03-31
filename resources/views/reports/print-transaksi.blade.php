<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - {{ $period_label }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; font-size: 8.5pt; color: #333; }

        .page { max-width: 297mm; margin: 0 auto; padding: 10px; }

        /* Header */
        .report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; border-bottom: 3px solid #2c3e50; padding-bottom: 8px; }
        .header-left { display: flex; align-items: center; gap: 10px; }
        .header-left img { max-height: 45px; }
        .store-name { font-size: 14pt; font-weight: bold; color: #1a5c2a; }
        .store-info { font-size: 8pt; color: #666; }
        .report-title { font-size: 16pt; font-weight: bold; text-align: center; color: #2c3e50; }

        /* Filter info */
        .filter-info { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 6px 10px; margin-bottom: 8px; font-size: 8pt; display: flex; gap: 20px; flex-wrap: wrap; }
        .filter-info span { color: #555; }
        .filter-info strong { color: #333; }

        /* Summary cards */
        .summary-row { display: flex; gap: 8px; margin-bottom: 8px; }
        .summary-card { flex: 1; background: #f0f8ff; border: 1px solid #b3d9ff; border-radius: 4px; padding: 6px 10px; text-align: center; }
        .summary-card .label { font-size: 7pt; color: #666; text-transform: uppercase; }
        .summary-card .value { font-size: 11pt; font-weight: bold; color: #2c3e50; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table th { background: #2c3e50; color: #fff; padding: 5px 4px; font-size: 7.5pt; text-align: left; white-space: nowrap; }
        table td { padding: 4px; border-bottom: 1px solid #dee2e6; font-size: 7.5pt; }
        table tr:nth-child(even) { background: #f8f9fa; }
        table .text-right { text-align: right; }
        table .text-center { text-align: center; }
        table .profit-positive { color: #28a745; font-weight: bold; }
        table .profit-negative { color: #dc3545; font-weight: bold; }

        /* Total row */
        table tfoot td { background: #e9ecef; font-weight: bold; border-top: 2px solid #2c3e50; padding: 6px 4px; }

        /* Record count */
        .record-count { font-size: 8pt; margin-bottom: 4px; color: #555; }

        /* Footer */
        .report-footer { margin-top: 15px; border-top: 1px solid #ccc; padding-top: 8px; display: flex; justify-content: space-between; font-size: 7.5pt; color: #999; }

        /* Print controls */
        .no-print { text-align: center; margin-bottom: 12px; padding: 10px; background: #e8f5e9; border-radius: 6px; }
        .no-print button { padding: 10px 30px; font-size: 12pt; cursor: pointer; background: #2c3e50; color: #fff; border: none; border-radius: 4px; margin: 0 5px; }
        .no-print button:hover { background: #1a252f; }
        .no-print p { font-size: 9pt; color: #666; margin-top: 6px; }

        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="no-print">
            <button onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
            <button onclick="window.close()">✕ Tutup</button>
            <p>Untuk menyimpan sebagai PDF: klik <strong>Cetak</strong> → pilih <strong>"Save as PDF"</strong> di printer</p>
        </div>

        <!-- Header -->
        <div class="report-header">
            <div class="header-left">
                @if($store_logo)
                    <img src="{{ asset($store_logo) }}" alt="Logo">
                @endif
                <div>
                    <div class="store-name">{{ $store_name }}</div>
                    <div class="store-info">{{ $store_address }} | ☎ {{ $store_phone }}</div>
                </div>
            </div>
            <div class="report-title">DAFTAR TRANSAKSI KELUAR</div>
        </div>

        <!-- Filter Info -->
        <div class="filter-info">
            <span>Periode: <strong>{{ $period_label }}</strong></span>
            <span>Dari: <strong>{{ $from ?? 'Semua' }}</strong></span>
            <span>Sampai: <strong>{{ $to ?? 'Semua' }}</strong></span>
            <span>Status: <strong>{{ strtoupper($status ?? 'Semua') }}</strong></span>
            <span>Metode: <strong>{{ strtoupper($method ?? 'Semua') }}</strong></span>
        </div>

        <!-- Summary -->
        <div class="summary-row">
            <div class="summary-card">
                <div class="label">Total Record</div>
                <div class="value">{{ $records->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Harga Modal</div>
                <div class="value">Rp {{ number_format($totals['cost'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Harga Jual</div>
                <div class="value">Rp {{ number_format($totals['selling'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Qty</div>
                <div class="value">{{ number_format($totals['qty'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Jumlah</div>
                <div class="value">Rp {{ number_format($totals['amount'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Kas Masuk</div>
                <div class="value">Rp {{ number_format($totals['kas_masuk'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Profit</div>
                <div class="value" style="color: {{ $totals['profit'] >= 0 ? '#28a745' : '#dc3545' }}">Rp {{ number_format($totals['profit'], 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Record count -->
        <div class="record-count">Total Record : <strong>{{ $records->count() }}</strong></div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tgl. Transaksi</th>
                    <th>Kasir</th>
                    <th>Pelanggan</th>
                    <th>Metode Bayar</th>
                    <th>Nama Produk</th>
                    <th class="text-right">Harga Modal</th>
                    <th class="text-right">Harga Jual</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Kas Masuk</th>
                    <th class="text-right">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $row)
                    <tr>
                        <td>{{ $row['invoice_number'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['cashier'] }}</td>
                        <td>{{ $row['customer'] }}</td>
                        <td>{{ $row['payment_method'] }}</td>
                        <td>{{ strtoupper($row['product_name']) }}</td>
                        <td class="text-right">{{ number_format($row['cost_price'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['selling_price'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($row['qty'], 0) }}</td>
                        <td class="text-center">{{ $row['unit'] }}</td>
                        <td class="text-right">{{ number_format($row['amount'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['kas_masuk'], 0, ',', '.') }}</td>
                        <td class="text-right {{ $row['profit'] >= 0 ? 'profit-positive' : 'profit-negative' }}">{{ number_format($row['profit'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right">TOTAL :</td>
                    <td class="text-right">{{ number_format($totals['cost'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['selling'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($totals['qty'], 0, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($totals['amount'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($totals['kas_masuk'], 0, ',', '.') }}</td>
                    <td class="text-right" style="color: {{ $totals['profit'] >= 0 ? '#28a745' : '#dc3545' }}">{{ number_format($totals['profit'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Footer -->
        <div class="report-footer">
            <span>{{ $store_name }} — Laporan Transaksi Keluar</span>
            <span>Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</body>
</html>
