<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\TrainingRegistration;
use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Hitung total pemasukan
        $pemasukanProduk = Order::whereIn('status', ['paid', 'completed', 'shipped'])->sum('total_price');
        $pemasukanPelatihan = TrainingRegistration::whereIn('status', ['paid', 'completed'])->sum('total_price');
        $totalPemasukan = $pemasukanProduk + $pemasukanPelatihan;

        // Hitung total pengeluaran
        $totalPengeluaran = Expense::sum('amount');

        // Hitung laba bersih (Keuntungan)
        $labaBersih = $totalPemasukan - $totalPengeluaran;

        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalPemasukan, 0, ',', '.'))
                ->description('Dari Penjualan & Pelatihan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'))
                ->description('Biaya operasional & bahan')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Laba Bersih', 'Rp ' . number_format($labaBersih, 0, ',', '.'))
                ->description($labaBersih >= 0 ? 'Keuntungan' : 'Kerugian')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($labaBersih >= 0 ? 'success' : 'danger'),
        ];
    }
}