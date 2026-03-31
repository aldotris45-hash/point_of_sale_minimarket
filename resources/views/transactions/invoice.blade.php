<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $transaction->invoice_number }}</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12pt; line-height: 1.4; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-height: 80px; max-width: 200px; margin-bottom: 10px; }
        .store-name { font-size: 24pt; font-weight: bold; color: #2d5a2d; margin-bottom: 5px; }
        .store-info { font-size: 11pt; margin-bottom: 20px; }
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .invoice-info { border: 2px solid #2d5a2d; padding: 20px; border-radius: 8px; flex: 1; }
        .invoice-number { font-size: 18pt; font-weight: bold; color: #2d5a2d; }
        .date { font-size: 12pt; margin-top: 10px; }
        .customer-info { margin-bottom: 30px; }
        .customer-title { font-size: 14pt; font-weight: bold; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .table th { background: #f8f9fa; border: 1px solid #dee2e6; padding: 12px 8px; text-align: left; font-weight: 600; font-size: 11pt; }
        .table td { border: 1px solid #dee2e6; padding: 10px 8px; font-size: 11pt; }
        .table tr:nth-child(even) { background: #f8f9fa; }
        .total-section { border: 2px solid #2d5a2d; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .total-row { display: flex; justify-content: space-between; font-size: 12pt; margin-bottom: 8px; }
        .total-row.total { font-size: 16pt; font-weight: bold; border-top: 2px solid #2d5a2d; padding-top: 10px; margin-top: 10px; }
        .piutang-info { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin-top: 20px; }
        .piutang-info h4 { color: #856404; margin-bottom: 10px; }
        .bank-info { background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 6px; margin-top: 20px; }
        .bank-info h4 { color: #0c5460; margin-bottom: 10px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10pt; color: #666; }
        @media print { body { -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="header">
        @if($store_logo)
            <img src="{{ $store_logo }}" alt="Logo" class="logo">
        @endif
        <div class="store-name">{{ $store_name }}</div>
        <div class="store-info">
            {{ $store_address }}<br>
            📞 {{ $store_phone }}
        </div>
    </div>

    <div class="invoice-header">
        <div class="invoice-info">
            <div class="invoice-number">INVOICE #{{ $transaction->invoice_number }}</div>
            <div class="date">
                <strong>Tanggal:</strong> {{ $transaction->created_at->format('d/m/Y H:i') }}<br>
                <strong>Kasir:</strong> {{ $transaction->user->name }}
            </div>
        </div>
    </div>

    @if($transaction->customer)
        <div class="customer-info">
            <div class="customer-title">PELANGGAN</div>
            <div><strong>{{ $transaction->customer->name }}</strong></div>
            @if($transaction->customer->address)
                <div>{{ $transaction->customer->address }}</div>
            @endif
            @if($transaction->customer->phone)
                <div>📞 {{ $transaction->customer->phone }}</div>
            @endif
        </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Nama Barang</th>
                <th style="width: 80px; text-align: right;">Qty</th>
                <th style="width: 100px; text-align: right;">Harga</th>
                <th style="width: 120px; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->product->name }}</td>
                    <td style="text-align: right;">{{ number_format($detail->quantity, 0, ',', '.') }} {{ $detail->product->unit ?? '' }}</td>
                    <td style="text-align: right;">{{ $currency }} {{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ $currency }} {{ number_format($detail->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>Subtotal</span>
            <span>{{ $currency }} {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($transaction->discount > 0)
            <div class="total-row">
                <span>Diskon ({{ $discount_percent }}%)</span>
                <span>- {{ $currency }} {{ number_format($transaction->discount, 0, ',', '.') }}</span>
            </div>
        @endif
        @if($transaction->tax > 0)
            <div class="total-row">
                <span>Pajak ({{ $tax_percent }}%)</span>
                <span>+ {{ $currency }} {{ number_format($transaction->tax, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="total-row total">
            <span>TOTAL</span>
            <span>{{ $currency }} {{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
        @if($transaction->payment_method === 'cash_tempo')
            <div class="total-row">
                <span>Sudah Dibayar</span>
                <span>{{ $currency }} {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span>
            </div>
            <div class="total-row total">
                <span>PIUTANG</span>
                <span style="color: #dc3545;">{{ $currency }} {{ number_format($transaction->total - $transaction->amount_paid, 0, ',', '.') }}</span>
            </div>
        @else
            <div class="total-row">
                <span>Bayar</span>
                <span>{{ $currency }} {{ number_format($transaction->amount_paid, 0, ',', '.') }}</span>
            </div>
            @if($transaction->change > 0)
                <div class="total-row">
                    <span>Kembali</span>
                    <span style="color: #28a745;">{{ $currency }} {{ number_format($transaction->change, 0, ',', '.') }}</span>
                </div>
            @endif
        @endif
    </div>

    @if($transaction->payment_method === 'cash_tempo' && ($transaction->total - $transaction->amount_paid) > 0)
        <div class="piutang-info">
            <h4>📋 JADWAL PELUNASAN PIUTANG</h4>
            <p><strong>Piutang:</strong> {{ $currency }} {{ number_format($transaction->total - $transaction->amount_paid, 0, ',', '.') }}</p>
            <p><strong>Status:</strong> @if($transaction->status === 'paid') Lunas @else Belum Lunas @endif</p>
            <p><em>Hubungi {{ $transaction->customer->name ?? 'pelanggan' }} untuk pelunasan tepat waktu.</em></p>
        </div>
    @endif

    @if($store_bank_account)
        <div class="bank-info">
            <h4>🏦 TRANSFER BANK</h4>
            {{ $store_bank_account }}
        </div>
    @endif

    <div class="footer">
        <p>Terima kasih telah berbelanja di {{ $store_name }}</p>
        <p><em>Cetak: {{ now()->format('d/m/Y H:i') }}</em></p>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
