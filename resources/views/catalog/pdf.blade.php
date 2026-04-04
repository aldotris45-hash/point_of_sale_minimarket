<!DOCTYPE html>
<html>
<head>
    <title>Katalog Harga - {{ $storeName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #111;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #555;
            font-size: 13px;
        }
        .info {
            text-align: right;
            margin-bottom: 20px;
            font-style: italic;
            color: #555;
        }
        .category-title {
            background-color: #f4f4f4;
            padding: 8px;
            font-weight: bold;
            font-size: 14px;
            border-left: 4px solid #0d6efd;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #fafafa;
            color: #333;
            font-weight: bold;
        }
        .price {
            text-align: right;
            font-weight: bold;
            color: #000;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Katalog Harga Grosir {{ $storeName }}</h1>
        <p>{{ $storeAddress }}</p>
        <p>Telepon/WA: {{ $storePhone }}</p>
    </div>

    <div class="info">
        Pembaruan Terakhir: {{ $date }}
    </div>

    @forelse($groupedProducts as $categoryName => $products)
        <div class="category-title">{{ $categoryName }}</div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">SKU</th>
                    <th style="width: 45%;">Nama Produk</th>
                    <th style="width: 25%; text-align: right;">Harga Terkini</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $index => $product)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p style="text-align: center; margin-top: 50px;">Tidak ada produk yang tersedia saat ini.</p>
    @endforelse

    <div class="footer">
        Dicetak secara otomatis dari sistem POS {{ $storeName }}.<br>
        Harga dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya.
    </div>

</body>
</html>
