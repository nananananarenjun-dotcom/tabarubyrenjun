<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use App\Models\TrainingRegistration;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PemasukanChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Grafik Arus Kas';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'monthly';

    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Harian',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        $labels = [];
        $pemasukanData = [];
        $pengeluaranData = [];

        switch ($filter) {

            case 'daily':

                for ($i = 29; $i >= 0; $i--) {

                    $tanggal = now()->subDays($i);

                    $labels[] = $tanggal->format('d M');

                    $produk = Order::whereIn('status', ['paid', 'completed', 'shipped'])
                        ->whereDate('created_at', $tanggal)
                        ->sum('total_price');

                    $pelatihan = TrainingRegistration::whereIn('status', ['paid', 'completed'])
                        ->whereDate('created_at', $tanggal)
                        ->sum('total_price');

                    $pengeluaran = Expense::whereDate('expense_date', $tanggal)
                        ->sum('amount');

                    $pemasukanData[] = $produk + $pelatihan;
                    $pengeluaranData[] = $pengeluaran;
                }

                break;

            case 'yearly':

                for ($i = 4; $i >= 0; $i--) {

                    $tahun = now()->year - $i;

                    $labels[] = $tahun;

                    $produk = Order::whereIn('status', ['paid', 'completed', 'shipped'])
                        ->whereYear('created_at', $tahun)
                        ->sum('total_price');

                    $pelatihan = TrainingRegistration::whereIn('status', ['paid', 'completed'])
                        ->whereYear('created_at', $tahun)
                        ->sum('total_price');

                    $pengeluaran = Expense::whereYear('expense_date', $tahun)
                        ->sum('amount');

                    $pemasukanData[] = $produk + $pelatihan;
                    $pengeluaranData[] = $pengeluaran;
                }

                break;

            default:

                for ($i = 1; $i <= 12; $i++) {

                    $produk = Order::whereIn('status', ['paid', 'completed', 'shipped'])
                        ->whereYear('created_at', now()->year)
                        ->whereMonth('created_at', $i)
                        ->sum('total_price');

                    $pelatihan = TrainingRegistration::whereIn('status', ['paid', 'completed'])
                        ->whereYear('created_at', now()->year)
                        ->whereMonth('created_at', $i)
                        ->sum('total_price');

                    $pengeluaran = Expense::whereYear('expense_date', now()->year)
                        ->whereMonth('expense_date', $i)
                        ->sum('amount');

                    $labels[] = date('M', mktime(0, 0, 0, $i, 1));

                    $pemasukanData[] = $produk + $pelatihan;
                    $pengeluaranData[] = $pengeluaran;
                }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $pemasukanData,
                    'borderColor' => '#16A34A',
                    'backgroundColor' => '#16A34A',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $pengeluaranData,
                    'borderColor' => '#DC2626',
                    'backgroundColor' => '#DC2626',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}