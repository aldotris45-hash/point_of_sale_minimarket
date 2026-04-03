<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur #{{ $transaction->invoice_number }}</title>
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 9pt; color: #333; line-height: 1.4; padding: 15mm 20mm; }

        .page { width: 100%; margin: 0 auto; }

        /* Layout table (DomPDF-compatible) */
        .layout-table { width: 100%; border-collapse: collapse; }
        .layout-table td { vertical-align: top; padding: 0; }

        /* Header */
        .header { margin-bottom: 8px; }
        .store-name { font-size: 16pt; font-weight: bold; color: #1a5c2a; }
        .store-info { font-size: 8pt; color: #555; }
        .header-right { text-align: right; }
        .header-right .title { font-size: 18pt; font-weight: bold; color: #333; }

        /* Meta info */
        .meta-row { border-top: 2px solid #333; border-bottom: 1px solid #ccc; padding: 6px 0; margin-bottom: 12px; }
        .meta-left .label { font-size: 9pt; color: #555; }
        .meta-left .name { font-size: 10pt; font-weight: bold; }
        .meta-left .detail { font-size: 8pt; color: #777; }
        .meta-right { text-align: right; font-size: 9pt; }
        .meta-right span { color: #c0392b; font-weight: bold; }

        /* Table */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.items th { background: #2c3e50; color: #fff; padding: 6px 5px; font-size: 9pt; text-align: left; }
        table.items td { padding: 5px; border-bottom: 1px solid #dee2e6; font-size: 9pt; }
        table.items .text-right { text-align: right; }
        table.items .text-center { text-align: center; }

        /* Terbilang */
        .terbilang { font-size: 8pt; font-style: italic; color: #555; margin-bottom: 8px; }

        /* Summary */
        .summary-section { margin-bottom: 15px; }
        .notes-box { font-size: 9pt; }
        .notes-box strong { display: block; margin-bottom: 2px; }
        .totals-table { border-collapse: collapse; }
        .totals-table td { padding: 2px 5px; font-size: 9pt; }
        .totals-table .total-row td { font-weight: bold; font-size: 11pt; border-top: 2px solid #333; padding-top: 4px; }

        /* Footer */
        .signature { text-align: center; width: 180px; }
        .signature .line { border-bottom: 1px solid #333; margin-top: 50px; padding-bottom: 4px; }
        .signature .name { font-size: 8pt; margin-top: 4px; }

        .no-print { text-align: center; margin-bottom: 15px; }
        .no-print button { padding: 8px 24px; font-size: 12pt; cursor: pointer; background: #2c3e50; color: #fff; border: none; border-radius: 4px; }
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="page">
        @if(!($is_pdf ?? false))
        <div class="no-print" style="display:flex; gap:8px; justify-content:center;">
            <button onclick="window.print()">🖨️ Cetak Faktur Penjualan</button>
            <x-share-wa :transaction="$transaction" type="faktur" btnClass="btn btn-success" />
        </div>
        @endif

        <!-- Header -->
        <div class="header">
            <table class="layout-table">
                <tr>
                    <td style="width: 60%;">
                        <table class="layout-table"><tr>
                            @if($store_logo)
                                <td style="width: 50px; padding-right: 10px;">
                                    @if($is_pdf ?? false)
                                        @if(!empty($pdf_logo_path))
                                            <img src="{{ $pdf_logo_path }}" alt="Logo" style="max-height: 50px; max-width: 50px;">
                                        @endif
                                    @else
                                        <img src="{{ asset($store_logo) }}" alt="Logo" style="max-height: 50px; max-width: 50px;">
                                    @endif
                                </td>
                            @endif
                            <td>
                                <div class="store-name">{{ $store_name }}</div>
                                <div class="store-info">
                                    Alamat : {{ $store_address }}<br>
                                    Telepon/HP : {{ $store_phone }}
                                </div>
                            </td>
                        </tr></table>
                    </td>
                    <td class="header-right">
                        <div class="title">FAKTUR PENJUALAN</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Meta Row -->
        <div class="meta-row">
            <table class="layout-table">
                <tr>
                    <td class="meta-left" style="width: 55%;">
                        <div class="label">Kepada Yth.</div>
                        @if($transaction->customer)
                            <div class="name">{{ $transaction->customer->name }}</div>
                            <div class="detail">Alamat: {{ $transaction->customer->address ?? '' }}</div>
                            <div class="detail">No. HP / WA: {{ $transaction->customer->phone ?? '' }}</div>
                        @else
                            <div class="name">Umum</div>
                        @endif
                    </td>
                    <td class="meta-right">
                        Tanggal / Jam : <span>{{ $transaction->created_at->format('d/M/Y H:i') }}</span><br>
                        No. Faktur : <span>{{ $transaction->invoice_number }}</span><br>
                        Kasir : <span>{{ $transaction->user->name ?? '-' }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 38%;">Nama Produk</th>
                    <th class="text-center" style="width: 8%;">Qty</th>
                    <th class="text-center" style="width: 10%;">Satuan</th>
                    <th class="text-right" style="width: 14%;">Harga</th>
                    <th class="text-right" style="width: 12%;">Diskon/Item</th>
                    <th class="text-right" style="width: 14%;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $detail)
                    <tr>
                        <td>{{ strtoupper($detail->product->name) }}</td>
                        <td class="text-center">{{ number_format($detail->quantity, 0) }}</td>
                        <td class="text-center">{{ $detail->product->unit ?? 'Pcs' }}</td>
                        <td class="text-right">{{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="text-right">-</td>
                        <td class="text-right">{{ number_format($detail->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Terbilang -->
        <div class="terbilang">Terbilang: {{ $terbilang }}</div>

        <!-- Summary -->
        <div class="summary-section">
            <table class="layout-table">
                <tr>
                    <td class="notes-box" style="width: 55%; padding-right: 20px;">
                        <strong>Keterangan:</strong>
                        Terima kasih atas kepercayaan Anda.<br>
                        Mohon simpan dokumen ini sebagai bukti transaksi.<br><br>

                        @if($store_bank_account)
                            <strong>Metode Pembayaran:</strong>
                            {{ $store_bank_account }}
                        @endif
                    </td>
                    <td>
                        <table class="totals-table" style="width: 100%;">
                            <tr><td>Subtotal :</td><td style="text-align:right;">{{ number_format($transaction->subtotal, 0, ',', '.') }}</td></tr>
                            <tr><td>Diskon :</td><td style="text-align:right;">{{ $transaction->discount > 0 ? number_format($transaction->discount, 0, ',', '.') : '-' }}</td></tr>
                            <tr><td>Total Sblm Pajak :</td><td style="text-align:right;">{{ number_format($transaction->subtotal - $transaction->discount, 0, ',', '.') }}</td></tr>
                            <tr><td>Pajak {{ $tax_percent }}% :</td><td style="text-align:right;">{{ $transaction->tax > 0 ? number_format($transaction->tax, 0, ',', '.') : '-' }}</td></tr>
                            <tr class="total-row"><td><strong>Total :</strong></td><td style="text-align:right;"><strong>{{ number_format($transaction->total, 0, ',', '.') }}</strong></td></tr>
                            <tr><td>Tunai :</td><td style="text-align:right;">{{ number_format($transaction->amount_paid, 0, ',', '.') }}</td></tr>
                            <tr><td>Kembalian :</td><td style="text-align:right;">{{ $transaction->change > 0 ? number_format($transaction->change, 0, ',', '.') : '-' }}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Signatures -->
        <table class="layout-table">
            <tr>
                <td style="width: 60%;"></td>
                <td class="signature" style="position: relative; text-align: center;">
                    <div>Hormat Kami,</div>
                    
                    @if(isset($with_stamp) && $with_stamp && !empty($store_stamp))
                        @if($is_pdf ?? false)
                            <div style="height: 0; position: absolute; left: 50%; margin-left: -40px; top: 20px; z-index: -1;">
                                <img src="{{ $pdf_stamp_path }}" alt="Stempel" style="max-width: 110px; opacity: 0.55;">
                            </div>
                        @else
                            <img class="stamp-overlay" src="{{ asset($store_stamp) }}" alt="Stempel" style="position: absolute; top: -5px; right: -20px; transform: rotate(-12deg); max-width: 130px; max-height: 130px; opacity: 0.55; z-index: 3; pointer-events: none;">
                        @endif
                    @endif
                    
                    @if(isset($with_signature) && $with_signature && !empty($store_signature))
                        <div style="min-height: 50px; margin-top: 8px;">
                            @if($is_pdf ?? false)
                                <img src="{{ $pdf_signature_path }}" alt="Tanda Tangan" style="max-width: 140px; max-height: 50px;">
                            @else
                                <img src="{{ asset($store_signature) }}" alt="Tanda Tangan" style="max-width: 140px; max-height: 50px;">
                            @endif
                        </div>
                        <div class="line" style="margin-top: 3px;"></div>
                    @else
                        <div class="line"></div>
                    @endif
                    <div class="name">( {{ $store_name }} )</div>
                </td>
                <td class="signature" style="text-align: center;">
                    <div>Diterima Oleh,</div>
                    <div class="line"></div>
                    <div class="name">&nbsp;</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
