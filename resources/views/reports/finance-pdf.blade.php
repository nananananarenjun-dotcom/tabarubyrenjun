<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{
            font-family: sans-serif;
            font-size: 11px; /* Dikecilkan sedikit agar tabel rincian muat */
        }
        table{
            width:100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td{
            border:1px solid #000;
            padding:6px;
            vertical-align: top;
        }
        table th {
            background-color: #f2f2f2;
        }
        h2, h3 {
            text-align:center;
            margin-bottom: 5px;
        }
        .header-info {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <h2>Laporan Keuangan & Penjualan Sabira Ecoprint</h2>
    
    <div class="header-info">
        <p><strong>Tahun :</strong> {{ now()->year }}</p>
        <p><strong>Total Pemasukan :</strong> Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
        <p><strong>Total Pengeluaran :</strong> Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
        <p><strong>Laba Bersih :</strong> Rp {{ number_format($labaBersih, 0, ',', '.') }}</p>
    </div>

    <h3>Rekapitulasi Laba/Rugi Bulanan</h3>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Pemasukan</th>
                <th>Pengeluaran</th>
                <th>Laba</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
            <tr>
                <td>{{ $row['bulan'] }}</td>
                <td>Rp {{ number_format($row['pemasukan'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($row['pengeluaran'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($row['laba'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Rincian Transaksi Penjualan Produk</h3>
    <table>
        <thead>
            <tr>
                <th>Waktu Transaksi</th>
                <th>No. Nota</th>
                <th>Pelanggan</th>
                <th>Detail Produk yang Dibeli</th>
                <th>Total Bayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
            <tr>
                <td>{{ \Carbon\Carbon::parse($trx->created_at)->format('d M Y, H:i') }}</td>
                
                <td>{{ $trx->order_id }}</td>
                
                <td>{{ $trx->user->name ?? 'Anonim' }}</td>
                
                <td>
                    @foreach($trx->orderItems as $item)
                        - {{ $item->product ? $item->product->name : 'Produk Terhapus' }} 
                          (<strong>{{ $item->quantity }} pcs</strong>) <br>
                    @endforeach
                </td>
                
                <td>Rp {{ number_format($trx->total_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>