<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class GrafikProdukTerlaris extends ChartWidget
{
    protected static ?string $heading = 'Grafik Produk Paling Banyak Terjual';
    
    protected static ?int $sort = 3; // Posisinya di bawah Arus Kas

    protected function getData(): array
    {
        // Ambil data produk yang terjual dan jumlahkan quantity-nya
        $data = OrderItem::join('products', 'order_items.product_id', '=', 'products.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->whereIn('orders.status', ['paid', 'completed', 'shipped'])
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_terjual'))
            ->groupBy('products.name')
            ->orderByDesc('total_terjual')
            ->limit(5) // Ambil 5 besar
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Terjual (Pcs)',
                    'data' => $data->pluck('total_terjual')->toArray(),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Menampilkan grafik batang (bisa diganti 'pie' atau 'doughnut')
    }
}