<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Pajak UMKM {{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; padding: 20px; }

        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 12px; }
        .header h1 { font-size: 16px; margin-bottom: 2px; }
        .header h2 { font-size: 13px; font-weight: normal; color: #555; }
        .header .store-info { font-size: 10px; color: #777; margin-top: 4px; }

        .summary-grid { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 8px; }
        .summary-box { flex: 1; border: 1px solid #ddd; border-radius: 4px; padding: 8px 10px; text-align: center; }
        .summary-box .label { font-size: 9px; color: #666; text-transform: uppercase; }
        .summary-box .value { font-size: 14px; font-weight: bold; margin-top: 2px; }
        .summary-box .value.green { color: #198754; }
        .summary-box .value.red { color: #dc3545; }
        .summary-box .value.blue { color: #0d6efd; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: right; }
        th { background: #f5f5f5; font-weight: bold; text-align: center; font-size: 10px; }
        td:first-child { text-align: left; }
        tr.total { background: #f0f0f0; font-weight: bold; }
        .badge-free { background: #d1e7dd; color: #0f5132; padding: 1px 6px; border-radius: 3px; font-size: 9px; }
        .badge-taxed { background: #fff3cd; color: #664d03; padding: 1px 6px; border-radius: 3px; font-size: 9px; }

        .info-section { margin-top: 20px; font-size: 10px; color: #555; }
        .info-section h3 { font-size: 11px; color: #333; margin-bottom: 5px; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $store_name ?? 'Toko' }}</h1>
        @if (!empty($store_address))
            <div class="store-info">{{ $store_address }} {{ !empty($store_phone) ? '| Telp: ' . $store_phone : '' }}</div>
        @endif
        <h2>Rekap Pajak UMKM — Tahun {{ $year }}</h2>
    </div>

    {{-- Summary --}}
    <table>
        <tr>
            <td style="border: none; text-align: center; width: 25%; padding: 8px;">
                <div style="font-size: 9px; color: #666;">OMSET TAHUNAN</div>
                <div style="font-size: 13px; font-weight: bold; color: #0d6efd;">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
            </td>
            <td style="border: none; text-align: center; width: 25%; padding: 8px;">
                <div style="font-size: 9px; color: #666;">TOTAL PPh FINAL</div>
                <div style="font-size: 13px; font-weight: bold; color: #dc3545;">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</div>
            </td>
            <td style="border: none; text-align: center; width: 25%; padding: 8px;">
                <div style="font-size: 9px; color: #666;">STATUS PTKP</div>
                <div style="font-size: 13px; font-weight: bold; color: {{ $summary['is_below_ptkp'] ? '#198754' : '#ffc107' }};">
                    {{ $summary['is_below_ptkp'] ? '✓ Bebas Pajak' : '⚠ Kena Pajak' }}
                </div>
            </td>
            <td style="border: none; text-align: center; width: 25%; padding: 8px;">
                <div style="font-size: 9px; color: #666;">SISA PTKP</div>
                <div style="font-size: 13px; font-weight: bold;">Rp {{ number_format($summary['remaining_ptkp'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    {{-- Tabel Bulanan --}}
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Jml Trx</th>
                <th>Omset Bruto</th>
                <th>Omset Kumulatif</th>
                <th>Status</th>
                <th>Omset Kena Pajak</th>
                <th>PPh Final 0.5%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($months as $m)
                <tr>
                    <td>{{ $m['month'] }}</td>
                    <td style="text-align: center;">{{ number_format($m['trx_count'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($m['gross_revenue'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($m['cumulative'], 0, ',', '.') }}</td>
                    <td style="text-align: center;">
                        @if ($m['gross_revenue'] == 0)
                            —
                        @elseif ($m['is_below_ptkp'])
                            <span class="badge-free">Bebas</span>
                        @else
                            <span class="badge-taxed">Kena Pajak</span>
                        @endif
                    </td>
                    <td>Rp {{ number_format($m['taxable_revenue'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($m['tax'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td>TOTAL</td>
                <td style="text-align: center;">{{ number_format(collect($months)->sum('trx_count'), 0, ',', '.') }}</td>
                <td>Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
                <td>—</td>
                <td></td>
                <td>Rp {{ number_format($summary['total_taxable_revenue'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="info-section">
        <h3>Catatan:</h3>
        <ul>
            <li>Tarif PPh Final UMKM: <strong>0.5%</strong> dari omset bruto (PP 55/2022).</li>
            <li>Batas PTKP: Omset kumulatif ≤ <strong>Rp 500.000.000</strong>/tahun = bebas pajak.</li>
            <li>Pajak disetor paling lambat tanggal 15 bulan berikutnya.</li>
            <li>Kode Akun Pajak: <strong>411128</strong> | Kode Jenis Setoran: <strong>420</strong></li>
            <li>SPT Tahunan dilaporkan paling lambat 31 Maret tahun berikutnya (Formulir 1770).</li>
        </ul>
    </div>

    <div class="footer">
        Dokumen ini digenerate otomatis oleh sistem POS {{ $store_name ?? '' }} pada {{ now()->format('d/m/Y H:i') }}.
    </div>
</body>
</html>
