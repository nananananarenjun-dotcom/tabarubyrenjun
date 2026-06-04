<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        body{
            font-family: sans-serif;
            font-size: 12px;
        }

        table{
            width:100%;
            border-collapse: collapse;
        }

        table th,
        table td{
            border:1px solid #000;
            padding:6px;
        }

        h2{
            text-align:center;
        }
    </style>
</head>
<body>

<h2>Laporan Keuangan Sabira Ecoprint</h2>

<p>
    Tahun : {{ now()->year }}
</p>

<p>
    Total Pemasukan :
    Rp {{ number_format($totalPemasukan,0,',','.') }}
</p>

<p>
    Total Pengeluaran :
    Rp {{ number_format($totalPengeluaran,0,',','.') }}
</p>

<p>
    Laba Bersih :
    Rp {{ number_format($labaBersih,0,',','.') }}
</p>

<br>

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
            <td>Rp {{ number_format($row['pemasukan'],0,',','.') }}</td>
            <td>Rp {{ number_format($row['pengeluaran'],0,',','.') }}</td>
            <td>Rp {{ number_format($row['laba'],0,',','.') }}</td>
        </tr>
        @endforeach

    </tbody>
</table>

</body>
</html>