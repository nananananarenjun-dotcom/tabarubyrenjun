<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Expense;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TrainingRegistration;

class FinanceReportController extends Controller
{
    public function exportPdf()
    {
        $rows = [];

        $totalPemasukan = 0;
        $totalPengeluaran = 0;

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

            $pemasukan = $produk + $pelatihan;

            $rows[] = [
                'bulan' => date('F', mktime(0, 0, 0, $i, 1)),
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'laba' => $pemasukan - $pengeluaran,
            ];

            $totalPemasukan += $pemasukan;
            $totalPengeluaran += $pengeluaran;
        }

        $pdf = Pdf::loadView('reports.finance-pdf', [
            'rows' => $rows,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'labaBersih' => $totalPemasukan - $totalPengeluaran,
        ]);

        return $pdf->download('laporan-keuangan.pdf');
    }
}