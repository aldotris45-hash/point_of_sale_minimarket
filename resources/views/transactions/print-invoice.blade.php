<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $transaction->invoice_number }}</title>
    <style>
        @page { size: A4; margin: 15mm 20mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11pt; color: #333; line-height: 1.5; }

        .page { max-width: 210mm; margin: 0 auto; padding: 20px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header-left { display: flex; align-items: flex-start; gap: 12px; }
        .header-left img { max-height: 60px; max-width: 60px; }
        .store-name { font-size: 20pt; font-weight: bold; color: #1a5c2a; }
        .store-info { font-size: 9pt; color: #555; }
        .header-right { text-align: right; }
        .header-right .title { font-size: 22pt; font-weight: bold; color: #c0392b; }
        .header-right .info { font-size: 10pt; margin-top: 4px; }
        .header-right .info span { color: #c0392b; font-weight: bold; }

        /* Customer & Amount */
        .meta-section { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .customer-box { flex: 1; }
        .customer-box .label { font-size: 10pt; color: #555; }
        .customer-box .name { font-size: 12pt; font-weight: bold; }
        .customer-box .detail { font-size: 10pt; color: #555; }
        .amount-box { text-align: right; }
        .amount-box .label { font-size: 10pt; color: #c0392b; font-weight: bold; }
        .amount-box .value { font-size: 28pt; font-weight: bold; color: #333; }

        /* Table */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items th { background: #2c3e50; color: #fff; padding: 8px 6px; font-size: 10pt; text-align: left; }
        table.items td { padding: 7px 6px; border-bottom: 1px solid #dee2e6; font-size: 10pt; }
        table.items .text-right { text-align: right; }
        table.items .text-center { text-align: center; }

        /* Terbilang */
        .terbilang { font-size: 9pt; font-style: italic; color: #555; margin-bottom: 10px; }

        /* Summary */
        .summary-section { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .notes-box { flex: 1; font-size: 10pt; }
        .notes-box strong { display: block; margin-bottom: 4px; }
        .totals-box { width: 280px; }
        .totals-box .row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 10pt; }
        .totals-box .row.total { font-weight: bold; font-size: 12pt; border-top: 2px solid #333; padding-top: 6px; margin-top: 4px; }

        /* Footer */
        .footer-section { display: flex; justify-content: space-between; margin-top: 30px; }
        .bank-info { font-size: 9pt; }
        .bank-info strong { display: block; margin-bottom: 4px; }
        .signature { text-align: center; width: 180px; }
        .signature .line { border-bottom: 1px solid #333; margin-top: 60px; padding-bottom: 4px; }
        .signature .name { font-size: 9pt; margin-top: 4px; }

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
        <div class="no-print" style="text-align:center; margin-bottom:15px; display:flex; gap:8px; justify-content:center;">
            <button onclick="window.print()" style="padding:8px 24px; font-size:12pt; cursor:pointer; background:#2c3e50; color:#fff; border:none; border-radius:4px;">🖨️ Cetak Invoice</button>
            <x-share-wa :transaction="$transaction" type="invoice" btnClass="btn btn-success" />
        </div>
        @endif

        <!-- Header -->
        <div class="header">
            <div class="header-left">
                @if($store_logo)
                    <img src="{{ asset($store_logo) }}" alt="Logo">
                @endif
                <div>
                    <div class="store-name">{{ $store_name }}</div>
                    <div class="store-info">
                        Alamat : {{ $store_address }}<br>
                        Telepon/HP : {{ $store_phone }}<br>
                        Email :
                    </div>
                </div>
            </div>
            <div class="header-right">
                <div class="title">INVOICE</div>
                <div class="info">No. Invoice : <span>{{ $transaction->invoice_number }}</span></div>
                <div class="info">Tanggal : <span>{{ $transaction->created_at->format('d/M/Y') }}</span></div>
                <div class="info">Pembayaran : <span>
                    @php
                        $pm = $transaction->payment_method->value ?? $transaction->payment_method;
                        echo $pm === 'cash_tempo' ? 'Tunai Tempo' : ucfirst($pm);
                    @endphp
                </span></div>
            </div>
        </div>

        <!-- Customer & Amount -->
        <div class="meta-section">
            <div class="customer-box">
                <div class="label">Kepada Yth.</div>
                @if($transaction->customer)
                    <div class="name">{{ $transaction->customer->name }}</div>
                    <div class="detail">Alamat: {{ $transaction->customer->address ?? '' }}</div>
                    <div class="detail">No. HP / WA: {{ $transaction->customer->phone ?? '' }}</div>
                    <div class="detail">Email: {{ $transaction->customer->email ?? '' }}</div>
                @else
                    <div class="name">Umum</div>
                @endif
            </div>
            <div class="amount-box">
                <div class="label">JUMLAH YANG HARUS DIBAYAR</div>
                <div class="value">Rp{{ number_format($transaction->total, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 40%;">Nama Produk</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-center" style="width: 12%;">Satuan</th>
                    <th class="text-right" style="width: 15%;">Harga</th>
                    <th class="text-right" style="width: 12%;">Diskon/item</th>
                    <th class="text-right" style="width: 15%;">Jumlah</th>
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
            <div class="notes-box">
                <strong>Keterangan:</strong>
                Terima kasih atas kepercayaan Anda.<br>
                Mohon simpan dokumen ini sebagai bukti transaksi.<br><br>

                @if($store_bank_account)
                    <strong>Metode Pembayaran:</strong>
                    {{ $store_bank_account }}
                @endif
            </div>
            <div class="totals-box">
                <div class="row"><span>Subtotal :</span><span>{{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
                <div class="row"><span>Diskon :</span><span>{{ $transaction->discount > 0 ? number_format($transaction->discount, 0, ',', '.') : '-' }}</span></div>
                <div class="row"><span>Total Sblm Pajak :</span><span>{{ number_format($transaction->subtotal - $transaction->discount, 0, ',', '.') }}</span></div>
                <div class="row"><span>Pajak {{ $tax_percent }}% :</span><span>{{ $transaction->tax > 0 ? number_format($transaction->tax, 0, ',', '.') : '-' }}</span></div>
                <div class="row total"><span>Total :</span><span>{{ number_format($transaction->total, 0, ',', '.') }}</span></div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="footer-section">
            <div style="flex:1;"></div>
            <div class="signature" style="position: relative;">
                <div>Hormat Kami,</div>
                
                @if(isset($with_stamp) && $with_stamp && !empty($store_stamp))
                    <img class="stamp-overlay" src="{{ asset($store_stamp) }}" alt="Stempel" style="position: absolute; top: -5px; right: -20px; transform: rotate(-12deg); max-width: 130px; max-height: 130px; opacity: 0.55; z-index: 3; pointer-events: none;">
                @endif
                
                @if(isset($with_signature) && $with_signature && !empty($store_signature))
                    <div style="min-height: 60px; margin-top: 10px; position: relative; z-index: 2;">
                        <img src="{{ asset($store_signature) }}" alt="Tanda Tangan" style="max-width: 140px; max-height: 60px;">
                    </div>
                    <div class="line" style="margin-top: 5px;"></div>
                @else
                    <div class="line"></div>
                @endif
                <div class="name">( {{ $store_name }} )</div>
            </div>
            <div class="signature">
                <div>Diterima Oleh,</div>
                <div class="line"></div>
                <div class="name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            </div>
        </div>
    </div>
</body>
</html>
