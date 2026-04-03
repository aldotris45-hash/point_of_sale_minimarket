<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Arus Kas</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 18pt;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 10pt;
        }
        .info-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .info-table td {
            vertical-align: top;
        }
        .summary-boxes {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin: 0 -10px 25px -10px;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-top: 4px solid #3498db;
            border-radius: 4px;
            padding: 15px;
            text-align: center;
            width: 20%;
        }
        .summary-box.success { border-top-color: #2ecc71; }
        .summary-box.warning { border-top-color: #f1c40f; }
        .summary-box.danger { border-top-color: #e74c3c; }
        .summary-box.dark { border-top-color: #34495e; }
        
        .box-title {
            font-size: 9pt;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .box-value {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
        }
        .box-value.text-success { color: #27ae60; }
        .box-value.text-danger { color: #c0392b; }

        .section-title {
            color: #2c3e50;
            font-size: 12pt;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
            margin-bottom: 15px;
            margin-top: 20px;
        }
        
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 25px;
        }
        table.data th, table.data td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }
        table.data th {
            background-color: #f4f6f7;
            color: #2c3e50;
            text-align: left;
            font-weight: bold;
        }
        table.data td.number {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }
        
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 9pt;
            color: #7f8c8d;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN ARUS KAS</h1>
        <p><strong>{{ $storeName }}</strong><br>
        {{ $storeAddress }}<br>
        Telp: {{ $storePhone }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Periode:</strong><br>
                {{ \Carbon\Carbon::parse($from)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($to)->translatedFormat('d F Y') }}
            </td>
            <td width="50%" style="text-align: right;">
                <strong>Dicetak pada:</strong><br>
                {{ now()->translatedFormat('d F Y H:i:s') }}
            </td>
        </tr>
    </table>

    <table class="summary-boxes">
        <tr>
            <td class="summary-box success">
                <div class="box-title">Pemasukan</div>
                <div class="box-value">{{ $currency . ' ' . number_format($totalIncome, 0, ',', '.') }}</div>
            </td>
            <td class="summary-box warning">
                <div class="box-title">Pembelian Brg</div>
                <div class="box-value">{{ $currency . ' ' . number_format($totalPurchase, 0, ',', '.') }}</div>
            </td>
            <td class="summary-box danger">
                <div class="box-title">Operasional</div>
                <div class="box-value">{{ $currency . ' ' . number_format($totalOperational, 0, ',', '.') }}</div>
            </td>
            <td class="summary-box dark">
                <div class="box-title">Tot Pengeluaran</div>
                <div class="box-value">{{ $currency . ' ' . number_format($totalExpense, 0, ',', '.') }}</div>
            </td>
            <td class="summary-box {{ $netBalance >= 0 ? 'success' : 'danger' }}">
                <div class="box-title">Laba / Rugi</div>
                <div class="box-value {{ $netBalance >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $netBalance < 0 ? '-' : '' }}{{ $currency . ' ' . number_format(abs($netBalance), 0, ',', '.') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Kategori Pengeluaran</div>
    <table class="data" style="width: 50%;">
        <thead>
            <tr>
                <th width="70%">Kategori</th>
                <th width="30%" style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenseByCategory as $expense)
            <tr>
                <td>{{ $expense['category'] }}</td>
                <td class="number">{{ number_format($expense['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @if($expenseByCategory->isEmpty())
            <tr>
                <td colspan="2" style="text-align: center; color: #999;">Tidak ada pengeluaran</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">Rincian Harian</div>
    <table class="data">
        <thead>
            <tr>
                <th width="15%">Tanggal</th>
                <th width="20%" style="text-align: right;">Pemasukan</th>
                <th width="20%" style="text-align: right;">Pembelian Brg</th>
                <th width="20%" style="text-align: right;">Pengeluaran Ops</th>
                <th width="25%" style="text-align: right;">Laba / Rugi Bersih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyData as $day)
            <tr>
                <td>{{ $day['date'] }}</td>
                <td class="number" style="color: #27ae60;">{{ number_format($day['income'], 0, ',', '.') }}</td>
                <td class="number" style="color: #e67e22;">{{ number_format($day['purchase'], 0, ',', '.') }}</td>
                <td class="number" style="color: #c0392b;">{{ number_format($day['operational'], 0, ',', '.') }}</td>
                <td class="number" style="color: {{ $day['balance'] >= 0 ? '#27ae60' : '#c0392b' }};">
                    {{ $day['balance'] < 0 ? '-' : '' }}{{ number_format(abs($day['balance']), 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh sistem Point of Sale pada {{ now()->translatedFormat('d M Y') }}
    </div>

</body>
</html>
