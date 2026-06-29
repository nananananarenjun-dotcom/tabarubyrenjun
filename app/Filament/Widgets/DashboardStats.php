<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;
    // Mengatur lebar widget agar penuh di halaman
    protected int | string | array $columnSpan = 'full';

    // Mengatur agar dalam 1 baris menampilkan 3 kotak (nanti sisanya turun ke bawah)
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        // 1. TRANSAKSI HARIAN (Hari Ini)
        $harian = Order::whereDate('created_at', Carbon::today())
            ->whereIn('status', ['paid', 'completed', 'shipped'])
            ->sum('total_price');

        // 2. TRANSAKSI MINGGUAN (Senin - Minggu Ini)
        $mingguan = Order::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->whereIn('status', ['paid', 'completed', 'shipped'])
            ->sum('total_price');

        // 3. TRANSAKSI BULANAN (Bulan Ini)
        $bulanan = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereIn('status', ['paid', 'completed', 'shipped'])
            ->sum('total_price');

        // 4. TRANSAKSI TAHUNAN (Tahun Ini)
        $tahunan = Order::whereYear('created_at', Carbon::now()->year)
            ->whereIn('status', ['paid', 'completed', 'shipped'])
            ->sum('total_price');

        // 5. PROYEKSI & PELUANG
        // Logika: Ambil total penjualan 3 bulan terakhir, cari rata-ratanya, 
        // lalu proyeksikan peluang kenaikan 10% untuk bulan depan.
        $total3Bulan = Order::where('created_at', '>=', Carbon::now()->subMonths(3))
            ->whereIn('status', ['paid', 'completed', 'shipped'])
            ->sum('total_price');
        
        $rataBulanan = $total3Bulan / 3;
        $proyeksi = $rataBulanan + ($rataBulanan * 0.10); // Peluang naik 10%

        // Hitung Produk Belum Terealisasi sebagai PELUANG BISNIS
        $jumlahPeluangProduk = \App\Models\Product::where('status', 'Belum Terealisasi')->count();
        $potensiHargaPeluang = \App\Models\Product::where('status', 'Belum Terealisasi')->sum('price');

        return [
            Stat::make('Transaksi Harian', 'Rp ' . number_format($harian, 0, ',', '.'))
                ->description('Penjualan hari ini')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Transaksi Mingguan', 'Rp ' . number_format($mingguan, 0, ',', '.'))
                ->description('Penjualan minggu ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Transaksi Bulanan', 'Rp ' . number_format($bulanan, 0, ',', '.'))
                ->description('Penjualan bulan ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            Stat::make('Transaksi Tahunan', 'Rp ' . number_format($tahunan, 0, ',', '.'))
                ->description('Penjualan tahun ini')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('primary'),

            Stat::make('Proyeksi Bulan Depan', 'Rp ' . number_format($proyeksi, 0, ',', '.'))
                ->description('Estimasi +10% peluang pasar')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('Peluang Produk Baru', $jumlahPeluangProduk . ' Item Menunggu')
                ->description('Potensi nilai: Rp ' . number_format($potensiHargaPeluang, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('info'),
        ];

    }
}